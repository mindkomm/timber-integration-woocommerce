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
		$self = new self();

		add_filter( 'wc_get_template', array( $self, 'maybe_render_twig_template' ), 10, 3 );

		$defaults = array(
			'subfolder'        => 'woocommerce',
			'product_class'    => '\Timber\Integrations\WooCommerce\Product',
			'product_iterator' => '\Timber\Integrations\WooCommerce\ProductsIterator',
		);

		$args = wp_parse_args( $args, $defaults );

		self::$subfolder        = trailingslashit( $args['subfolder'] );
		self::$product_class    = $args['product_class'];
		self::$product_iterator = $args['product_iterator'];

		// Bailout in admin.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		// For conditional functions like `is_woocommerce()` to work, we need to hook into the 'wp' action.
		add_action( 'wp', array( $self, 'setup_classes' ), 20 );

		add_filter( 'wc_get_template_part', array( $self, 'maybe_render_twig_template_part' ), 10, 3 );

		// Fixes product global for singular product pages.
		add_action( 'woocommerce_before_main_content', array( $self, 'maybe_setup_product_data' ), 1 );

		// Add WooCommerce context data to normal context.
		add_filter( 'timber/context', array( $self, 'get_woocommerce_context' ) );

		add_action( 'timber/twig/functions', array( $self, 'add_timber_functions' ) );
	}

	/**
	 * Setup classes Timber should use when in WooCommerce context.
	 */
	public function setup_classes() {
		// Use a custom post class for all WooCommerce product posts.
		add_filter( 'Timber\PostClassMap', array( $this, 'set_product_class' ) );

		// Set a custom iterator to correctly set the $product global.
		add_filter( 'timber/class/posts_iterator', array( $this, 'set_product_iterator' ), 10, 2 );
	}

	/**
	 * Set the iterator to use to loop over post collections.
	 *
	 * Checks the post type of the first post in the collection and only sets the custom posts
	 * iterator if it’s a product post.
	 *
	 * @return string
	 */
	public function set_product_iterator( $posts_iterator, $returned_posts ) {
		if ( empty( $returned_posts ) ) {
			return $posts_iterator;
		}

		$first = $returned_posts[0];

		if ( 'product' === $first->post_type ) {
			return self::$product_iterator;
		}

		return $posts_iterator;
	}

	/**
	 * Set the post class to use for product posts.
	 *
	 * @todo Timber 2.0: Update this.
	 *
	 * @return array
	 */
	public function set_product_class( $post_class ) {
		// Use a default post class map if it’s not already a post class array.
		if ( ! is_array( $post_class ) ) {
			$post_class = [
				'post' => $post_class,
				'page' => $post_class,
			];
		}

		return array_merge( $post_class, [
			'product' => self::$product_class
		] );
	}

	/**
	 * Renders a Twig template instead of a PHP template when calling wc_get_template().
	 *
	 * Looks for a Twig template in the theme folder first.
	 *
	 * @param string $located       Full path to the template.
	 * @param string $template_name Relative to the template.
	 * @param array  $args          Template arguments.
	 *
	 * @return string Path of the file to render.
	 */
	public function maybe_render_twig_template( $located, $template_name, $args ) {
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
			// Setup missing product global.
			global $product, $post;

			if ( ! $product ) {
				$product = wc_setup_product_data( $post );
			}

			// We can access the context here without performance loss, because it was already cached.
			$context = Timber::get_context();

			// Add the arguments for the WooCommerce template.
			$context['wc'] = self::convert_objects( $args );

			// Add current product to context.
			if ( $product instanceof \WC_Product ) {
				$context['product'] = $product;
				$context['post_id'] = $product->get_id();
				$context['post']    = Timber::get_post( $product->get_id() );
			}

			/**
			 * TODO: Add documentation for this.
			 */
			$context = apply_filters( 'timber/woocommerce/template/context', $context, $template_name, $template_name_twig, $product );

			$trace = wp_debug_backtrace_summary( null, 0, false );

			/**
			 * Check if the filter was called from within wc_get_template().
			 *
			 * We should only render the template if it was called from
			 * wc_get_template().
			 *
			 * The `wc_get_template` filter is used in other
			 * places as well. For example, if WooCommerce tries go get the
			 * system status including a list of outdated templates. In that
			 * case, we should return the full path of the template file.
			 */
			if ( in_array( 'wc_get_template', $trace, true ) ) {
				Timber::render( $file, $context );

				// Kind of a hack, because we can’t tell WooComerce to not load a template.
				return __DIR__ . '/template_empty.php';
			}

			// Try to get the full path of the Twig template and return it.
			$context = $loader->get_loader()->getSourceContext( $file );

			if ( $context->getPath() !== '' ) {
				return $context->getPath();
			}

			return __DIR__ . '/template_empty.php';
		}

		return $located;
	}

	/**
	 * Renders a Twig template instead of a PHP template when calling wc_get_template_part().
	 *
	 * @since 0.6.0
	 *
	 * @param string $template The template to load.
	 * @param mixed  $slug     Template slug.
	 * @param string $name     Optional. Template name. Default ''.
	 *
	 * @return bool|string Default template name if no Twig template was found. False if Twig
	 *                     template was found.
	 */
	public function maybe_render_twig_template_part( $template, $slug, $name = '' ) {
		// Build template name Timber should look for.
		$template_name_twig = self::$subfolder . "{$slug}-{$name}.twig";

		// Get loader and check if file exists.
		// TODO: Is this now the proper way to initialize and use a loader? Should a new loader be initialized here or would it be better to initialize it in the constructor?
		$caller = LocationManager::get_calling_script_dir( 1 );
		$loader = new Loader( $caller );
		$file   = $loader->choose_template( $template_name_twig );

		// Use WooCommerce’s default template if no Twig file was found.
		if ( ! $file ) {
			return $template;
		}

		global $post, $product;

		if ( ! $product ) {
			$product = wc_setup_product_data( $post );
		}

		// We can access the context here without performance loss, because it was already cached.
		$context = Timber::get_context();

		// Add current product to context.
		if ( $product instanceof \WC_Product ) {
			$context['product'] = $product;
			$context['post_id'] = $product->get_id();
			$context['post']    = Timber::get_post( $product->get_id() );
		}

		Timber::render( $file, $context );

		// Falsy values will prevent WooCommerce from loading a template.
		return false;
	}

	/**
	 * Fixes product global for singular product pages.
	 *
	 * On singular product pages, the product global is set up when the template loads. But it is
	 * reset when Timber calls the 'the_post' action repeatedly on objects that are not
	 * WooCommerce posts.
	 *
	 * By hooking into the 'woocommerce_before_main_content' action, we can set up the product
	 * global again.
	 *
	 * @since 0.6.2
	 * @link https://github.com/timber/timber/issues/1639
	 * @see Remove this for Timber 2.0?
	 */
	public function maybe_setup_product_data() {
		// Setup missing product global.
		global $product, $post;

		if ( ! $product ) {
			$product = wc_setup_product_data( $post );
		}
	}

	public static function convert_objects( $args ) {
		// Convert WP objects to Timber objects.
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
			$posts = $args[ $collection ];

			/**
			 * A post collection currently needs a WP_Post object to work with.
			 *
			 * They will be converted to Product objects in the Post Collection using class maps.
			 *
			 * @todo Improve this in Timber 2.0.
			 */
			$posts = array_map( function( $post ) {
				if ( $post instanceof \WC_Product ) {
					return get_post( $post->get_id() );
				}

				return $post;
			}, $posts );

			$args[ $collection ] = new PostCollection( $posts );
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
	 * have to use *woocommerce/single-product.twig*, but can use *woocommerce/single.twig*.
	 *
	 * If you have your own solution going on or need to do more checks, you don’t have to call this
	 * function.
	 *
	 * @api
	 * @see  WC_Template_Loader::get_template_loader_files()
	 * @todo Add functionality for product tags
	 *
	 * @param array $context Initial context.
	 */
	public static function render_default_template( $context = [] ) {
		$context = array_merge( Timber::get_context(), $context );

		$templates = [];

		if ( is_singular( 'product' ) ) {
			$post = $context['post'];

			/**
			 * Sets up $product global and other global variables needed.
			 *
			 * We only need to do this for singular templates, because the product iterator does
			 * this for us in loops.
			 */
			$post->setup();

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
				$woocommerce_context['post'] = Timber::get_post();
			} elseif ( is_archive() ) {
				// Add shop page to context
				if ( is_shop() ) {
					$woocommerce_context['post'] = Timber::get_post( wc_get_page_id( 'shop' ) );
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
	 * @param \Twig_Environment $twig Twig Environment.
	 *
	 * @return mixed
	 */
	public function add_timber_functions( $twig ) {
		$twig->addFunction( new \Timber\Twig_Function( 'Product', function( $pid ) {
			return new self::$product_class( $pid );
		} ) );

		/**
		 * Use 'wc_action' as an optimization to 'action', which behaves a bit weird.
		 *
		 * @link https://github.com/timber/timber/pull/1773
		 */
		$twig->addFunction( new \Timber\Twig_Function( 'wc_action', function() {
			$args   = func_get_args();
			$action = $args[0];
			array_shift( $args );
			do_action_ref_array( $action, $args );
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
