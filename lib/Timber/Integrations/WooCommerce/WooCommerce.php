<?php

namespace Timber\Integrations\WooCommerce;

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
 * @api
 */
class WooCommerce {
	/**
	 * Class to use for WooCommerce Product posts.
	 *
	 * @var string Class name.
	 */
	public static $product_class;

	/**
	 * Class to use when iterating over arrays of WooCommerce product posts.
	 *
	 * @var string Class name.
	 */
	public static $product_iterator;

	/**
	 * The subfolder to use in the Twig template file folder.
	 *
	 * @var string Subfolder name.
	 */
	public static $subfolder;

	/**
	 * Storage for context cache.
	 *
	 * @var array Context cache.
	 */
	public static $context_cache = array();

	/**
	 * WooCommerce constructor.
	 *
	 * @api
	 * @param array $args Array of arguments for the Integration.
	 */
	public static function init( $args = array() ) {
		// Bailout in backend
		if ( is_admin() ) {
			return;
		}

		$self = new self();

		$defaults = array(
			'subfolder'        => 'woocommerce',
			'product_class'    => '\Timber\Integrations\WooCommerce\Product',
			'product_iterator' => '\Timber\Integrations\WooCommerce\ProductsIterator',
		);

		$args = wp_parse_args( $args, $defaults );

		self::$subfolder        = trailingslashit( $args['subfolder'] );
		self::$product_class    = $args['product_class'];
		self::$product_iterator = $args['product_iterator'];

		// For conditional functions like `is_woocommerce()` to work, we need to hook into the 'wp' action.
		add_action( 'wp', array( $self, 'setup_classes' ), 20 );

		add_filter( 'wc_get_template', array( $self, 'maybe_render_twig_partial' ), 10, 3 );

		// Add WooCommerce context data to normal context.
		add_filter( 'timber/context', array( $self, 'get_woocommerce_context' ) );

		add_action( 'timber/twig/functions', array( $self, 'add_timber_functions' ) );
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
		$template_name_twig = self::$subfolder . str_replace( '.php', '.twig', $template_name );

		// Get loader and check if file exists.
		// TODO: Is this now the proper way to initialize and use a loader? Should a new loader be initialized here or would it be better to initialize it in the constructor?
		$caller = LocationManager::get_calling_script_dir( 1 );
		$loader = new Loader( $caller );
		$file   = $loader->choose_template( $template_name_twig );

		// If a file was found, render that file with the given args, otherwise, return the default location.
		if ( $file ) {
			global $product;

			// We can access the context here without performance loss, because it was already cached.
			$context = Timber::get_context();

			// Add the arguments for the WooCommerce template
			$context['wc'] = self::convert_objects( $args );

			// Add current product to context
			if ( $product instanceof \WC_Product ) {
				$context['product'] = $product;
				$context['post_id'] = $product->get_id();
			}

			/**
			 * TODO: Add documentation for this.
			 */
			$context = apply_filters( 'timber/woocommerce/template/context', $context, $template_name, $template_name_twig, $product );

			Timber::render( $file, $context );

			/**
			 * TODO: Will this work in all environments?
			 * TODO: Is there a better way to do it than to pass an empty file to an include() function?
			 */
			return __DIR__ . '/template_empty.php';
		}

		return $located;
	}

	public static function convert_objects( $args ) {
		// Convert WP object to Timber objects
		foreach ( $args as &$arg ) {
			if ( $arg instanceof \WP_Term ) {
				$arg = new \Timber\Term( $arg );
			}
		}

		$args = self::maybe_convert_to_collection( $args );

		return $args;
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
	 * This function can be called from `woocommerce.php` template file in the root of the theme. It
	 * mimicks the logic used by WooCommerce to sort out which template to load and tries to load
	 * the corresponding Twig file. It builds up an array with Twig templates to check for. Timber
	 * will use the first Twig file that exists. In addition to the default WooCommerce template
	 * files, there are some opininated "Goodies" that can make your life easier. E.g., you don’t
	 * have to to use *woocommerce/single-product.twig*, but can use *woocommerce/single.twig*.
	 *
	 * If you have your own solution going on or need to do more checks, you don’t have to call this
	 * function.
	 *
	 * @api
	 * @todo Add functionality for product tags
	 * @see WC_Template_Loader::get_template_loader_files()
	 */
	public static function render_default_template() {
		$context = Timber::get_context();

		$templates = [];

		if ( is_singular( 'product' ) ) {
			$post = $context['post'];

			// Timber goodies
			$templates[] = "single-{$post->post_name}.twig";

			// WooCommerce default
			$templates[] = 'single-product.twig';

			// Timber goodie
			$templates[] = 'single.twig';

		} elseif ( is_archive() ) {
			$context['title'] = woocommerce_page_title( false );

			if ( is_product_taxonomy() ) {
				$term = $context['term'];

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
			$woocommerce_context = [];

			if ( is_singular( 'product' ) ) {
				$woocommerce_context['post'] = new Product();
			} elseif ( is_archive() ) {
				// Add shop page to context
				if ( is_shop() ) {
					$woocommerce_context['post'] = new \Timber\Post( wc_get_page_id( 'shop' ) );
				}

				if ( is_product_taxonomy() ) {
					$woocommerce_context['term'] = new Term( get_queried_object() );
				}
			}

			// Always add cart to context
			$woocommerce_context['cart'] = WC()->cart;

			self::$context_cache = apply_filters( 'timber/woocommerce/context', $woocommerce_context );
		}

		$context = array_merge( $context, self::$context_cache );

		return $context;
	}

	/**
	 * Make function available in Twig.
	 *
	 * @param \Twig_Environment $twig Twig Environment
	 *
	 * @return mixed
	 */
	public function add_timber_functions( $twig ) {
		$twig->addFunction( new \Timber\Twig_Function( 'Product', function( $pid ) {
			return new self::$product_class( $pid );
		} ) );

		return $twig;
	}

	/**
	 * Disable image functions added in WooCommerce.
	 *
	 * This is useful if you want to use Timber’s image functionalities to display images.
	 *
	 * @api
	 */
	public static function disable_woocommerce_images() {
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
	}
}
