<?php

namespace Mind\Timber\Integrations\WooCommerce;

use Timber\Loader;
use Timber\LocationManager;
use Timber\PostCollection;
use Timber\Term;
use Timber\Timber;

/**
 * Class WooCommerce
 *
 * Tries to make it as easy as possible to work with WooCommerce when using Timber.
 *
 * When accessing WooCommerce product posts, they need to be returned in a Timber\PostCollection that can be looped
 * over with a custom iterator that automatically sets the $product global for each product.
 *
 * @package Timber\Integrations\WooCommerce
 */
class WooCommerce {
	/**
	 * @var string Class to use for WooCommerce Product posts
	 */
	public static $product_class;

	/**
	 * @var string Class to use when iterating over arrays of WooCommerce product posts.
	 */
	public static $product_iterator;

	/**
	 * @var string The subfolder to use in the Twig template file folder.
	 */
	public static $subfolder;

	public static $context_cache = array();

	/**
	 * WooCommerce constructor.
	 *
	 * @param array $args Array of arguments for the Integration.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'subfolder'        => 'woocommerce',
			'product_class'    => '\Mind\Timber\Integrations\WooCommerce\Product',
			'product_iterator' => '\Mind\Timber\Integrations\WooCommerce\ProductsIterator',
		);

		$args = wp_parse_args( $args, $defaults );

		self::$subfolder        = trailingslashit( $args['subfolder'] );
		self::$product_class    = $args['product_class'];
		self::$product_iterator = $args['product_iterator'];

		$this->setup_hooks();
	}

	/**
	 * Setup hooks used in this integration.
	 */
	public function setup_hooks() {
		// For conditional functions like `is_woocommerce()` to work, we need to hook into the 'wp' action.
		add_action( 'wp', array( $this, 'setup_classes' ), 20 );

		add_filter( 'wc_get_template', array( $this, 'maybe_render_twig_partial' ), 10, 3 );

		// Add WooCommerce context data to normal context
		add_filter( 'timber/context', array( __CLASS__, 'get_woocommerce_context' ) );
	}

	/**
	 * Setup classes Timber should use when in WooCommerce context.
	 */
	public function setup_classes() {
		/**
		 * TODO: What if posts of other posts types need to be displayed on the same page?
		 */
		if ( ! is_woocommerce() ) {
			return;
		}

		// Set a custom iterator to correctly set the $product global.
		add_filter( 'timber/class/posts_iterator', array( $this, 'set_product_iterator' ) );

		// Use a custom post class to load all posts when in WooCommerce context.
		add_filter( 'Timber\PostClassMap', array( $this, 'set_product_class' ) );
	}

	/**
	 * Set the iterator to use to loop over post collections.
	 *
	 * @return string
	 */
	public function set_product_iterator() {
		return self::$product_iterator;
	}

	/**
	 * Set the post class to use for product posts.
	 *
	 * @return string
	 */
	public function set_product_class() {
		return self::$product_class;
	}

	/**
	 * Look for a Twig template in the theme folder first.
	 *
	 * @param string $located       Full path to the template.
	 * @param string $template_name Relative to the template.
	 * @param array  $args          Template arguments.
	 * @return string Path of the file to render.
	 */
	public function maybe_render_twig_partial( $located, $template_name, $args ) {
		/**
		 * Build template name Timber should look for.
		 *
		 * The path is prepended with the subfolder and the PHP file extension replaced with '.twig'.
		 *
		 * TODO: Is str_replace() too naive here?
		 */
		$template_name = self::$subfolder . str_replace( '.php', '.twig', $template_name );

		// Get loader an check if file exists.
		// TODO: Is this now the proper way to initialize and use a loader? Should a new loader be initialized here or would it be better to initialize it in the constructor?
		$caller = LocationManager::get_calling_script_dir( 1 );
		$loader = new Loader( $caller );
		$file   = $loader->choose_template( $template_name );

		// If a file was found, render that file with the given args, otherwise, return the default location.
		if ( $file ) {
			global $product;

			// We can access the context here without performance loss, because it was already cached.
			$context = Timber::get_context();

			// Add the arguments for the WooCommerce template
			$context['wc'] = self::maybe_convert_to_collection( $args );

			// Add current product to context
			if ( is_a( $product, 'WC_Product' ) ) {
				$context['product'] = $product;
			}

			Timber::render( $file, $context );

			/**
			 * TODO: Will this work in all environments?
			 * TODO: Is there a better way to do it than to pass an empty file to an include() function?
			 */
			return __DIR__ . '/template_empty.php';
		}

		return $located;
	}

	/**
	 * Convert arrays of WooCommerce product objects to PostCollections of Timber Product posts.
	 *
	 * @param array $args Template arguments
	 * @return array
	 */
	public static function maybe_convert_to_collection( $args ) {
		$collections = [];

		// Loop through args and add to collections array if it’s an array of WC_Product objects
		foreach ( $args as $key => $arg ) {
			// Bailout if not array or not array of WC_Product objects
			if ( ! is_array( $arg ) || empty( $arg ) || ! isset( $arg[0] )
				|| ! is_object( $arg[0] ) || ! is_a( $arg[0], 'WC_Product' ) ) {
				continue;
			}

			$collections[] = $key;
		}

		// Bailout early if there are no collections
		if ( empty( $collections ) ) {
			return $args;
		}

		// Convert product post collections into PostCollections
		foreach ( $collections as $collection ) {
			$args[ $collection ] = new PostCollection( $args[ $collection ], self::$product_class );
		}

		return $args;
	}

	/**
	 * Render default Twig templates.
	 *
	 * This function can be called from `woocommerce.php` template file in the root of the theme. It mimicks the logic
	 * used by WooCommerce to sort out which template to load and tries to load the corresponding Twig file. It builds
	 * up an array with Twig templates to check for. Timber will use the first Twig file that exists. In addition to
	 * the default WooCommerce template files, there are some opininated "Goodies" that can make your life easier.
	 * E.g., you don’t have to to use woocommerce/single-product.twig, but can use woocommerce/single.twig.
	 *
	 * If you have your own solution going on or need to do more checks, you don’t have to call this function.
	 *
	 * @api
	 */
	public static function render_default_template() {
		$context = Timber::get_context();

		$templates = [];

		if ( is_singular( 'product' ) ) {
			$context['post'] = Timber::get_post();

			// WooCommerce default
			$templates[] = 'single-product.twig';

			// Timber goodie
			$templates[] = 'single.twig';

		} elseif ( is_archive() ) {
			$context['title'] = woocommerce_page_title( false );

			if ( is_product_taxonomy() ) {
				$term = get_queried_object();

				$context['term'] = new Term( $term );

				// WooCommerce defaults
				$templates[] = "taxonomy-{$term->taxonomy}-{$term->slug}.twig";
				$templates[] = "taxonomy-{$term->taxonomy}.twig";

				// Timber goodies
				$templates[] = "taxonomy-{$term->slug}.twig";
				$templates[] = 'taxonomy.twig';
			}

			// WooCommerce default
			$templates[] = 'archive-product.twig';

			// Timber goodie
			$templates[] = 'archive.twig';
		}

		// Prepend subfolder to templates
		$templates = array_map( function( $template ) {
			return self::$subfolder . $template;
		}, $templates );

		Timber::render( $templates, $context );
	}

	public static function get_woocommerce_context( $context = array() ) {
		if ( empty( self::$context_cache ) ) {
			/**
			 * Always add cart to context
			 */
			self::$context_cache = [
				'cart' => WC()->cart,
			];

			self::$context_cache = apply_filters( 'timber/woocommerce/context', self::$context_cache );
		}

		$context = array_merge( $context, self::$context_cache );

		return $context;
	}

	/**
	 * Disable image functions added in WooCommerce.
	 *
	 * This is useful if you want to use Timber’s image functionalities to display images.
	 */
	public static function disable_woocommerce_images() {
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
	}
}
