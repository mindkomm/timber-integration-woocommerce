<?php

namespace Timber\Integrations\WooCommerce;

use Timber\Loader;
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
	public static function init() {
		$self = new self();

		/**
		 * Filters the subfolder to use in the Twig template file folder.
		 *
		 * @param string $subfolder Subfolder name.
		 */
		self::$subfolder = apply_filters( 'theme/woocommerce/views_folder', 'woocommerce' );
		self::$subfolder = trailingslashit( self::$subfolder );

		add_filter( 'wc_get_template', array( $self, 'maybe_render_twig_template' ), 10, 3 );

		// Bailout in admin.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		// For conditional functions like `is_woocommerce()` to work, we need to
		// hook into the 'wp' action.
		add_action( 'wp', array( $self, 'setup_classes' ), 20 );

		add_filter( 'wc_get_template_part', array( $self, 'maybe_render_twig_template_part' ), 10, 3 );

		// Add WooCommerce context data to normal context.
		add_filter( 'timber/context', array( $self, 'get_woocommerce_context' ) );
	}

	/**
	 * Setup classes Timber should use when in WooCommerce context.
	 */
	public function setup_classes() {
		// Use a custom post class for all WooCommerce product posts.
		add_filter( 'timber/post/classmap', array( $this, 'set_product_class' ) );
	}

	/**
	 * Set the post class to use for product posts.
	 *
	 * @return array
	 */
	public function set_product_class( $classmap ) {
		$classmap['product'] = Product::class;

		return $classmap;
	}

	/**
	 * Renders a Twig template instead of a PHP template when calling
	 * wc_get_template().
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
		 * The path is prepended with the subfolder and the PHP file extension
		 * replaced with '.twig'.
		 */
		$template_name_twig = self::$subfolder . str_replace( '.php', '.twig', $template_name );

		// Get loader and check if file exists.
		$loader = new Loader();
		$file   = $loader->choose_template( $template_name_twig );

		// If a file was found, render that file with the given args, otherwise,
		// return the default location.
		if ( ! $file ) {
			return $located;
		}

		// Setup missing product global.
		global $product, $post;

		if ( ! $product ) {
			$product = wc_setup_product_data( $post );
		}

		// We can access the context here without performance loss, because it
		// was already cached.
		$context = Timber::context();

		// Add the arguments for the WooCommerce template.
		$context['wc'] = self::convert_objects( $args );

		// Add current product to context.
		if ( $product instanceof \WC_Product ) {
			$context['product'] = $product;
			$context['post_id'] = $product->get_id();
			$context['post']    = Timber::get_post( $product->get_id() );
		}

		// Set up the post again. When we use Timber::context(), then setup()
		// is called on singular post templates, which would cause WooCommerce
		// to set up the queried object as the $product global.
		if ( $context['post'] instanceof \Timber\Post ) {
			$context['post']->setup();
		}

		/**
		 * TODO: Add documentation for this.
		 */
		$context = apply_filters( 'timber/woocommerce/template/context', $context, $template_name, $template_name_twig, $product );

		$trace = wp_debug_backtrace_summary( null, 0, false );

		/**
		 * Check if the filter was called from within wc_get_template().
		 *
		 * We should only render the template if it was called with
		 * wc_get_template().
		 *
		 * The `wc_get_template` filter is used in other places as well. For
		 * example, if WooCommerce tries go get the system status including a
		 * list of outdated templates. In that case, we should return the full
		 * path of the template file.
		 */
		if ( in_array( 'wc_get_template', $trace, true ) ) {
			Timber::render( $file, $context );

			return __DIR__ . '/template_empty.php';
		}

		// Try to get the full path of the Twig template and return it.
		$context = $loader->get_loader()->getSourceContext( $file );

		if ( $context->getPath() !== '' ) {
			return $context->getPath();
		}

		return __DIR__ . '/template_empty.php';
	}

	/**
	 * Renders a Twig template instead of a PHP template when calling
	 * wc_get_template_part().
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
		$loader = new Loader();
		$file   = $loader->choose_template( $template_name_twig );

		// Use WooCommerce’s default template if no Twig file was found.
		if ( ! $file ) {
			return $template;
		}

		global $post, $product;

		if ( ! $product ) {
			$product = wc_setup_product_data( $post );
		}

		// We can access the context here without performance loss, because it
		// was already cached.
		$context = Timber::context();

		// Add current product to context.
		if ( $product instanceof \WC_Product ) {
			$context['product'] = $product;
			$context['post_id'] = $product->get_id();
			$context['post']    = Timber::get_post( $product->get_id() );
		}

		// Set up the post again. When we use Timber::context(), then setup()
		// is called on singular post templates, which would cause WooCommerce
		// to set up the queried object as the $product global.
		if ( $context['post'] instanceof \Timber\Post ) {
			$context['post']->setup();
		}

		Timber::render( $file, $context );

		// Falsy values will prevent WooCommerce from loading a template.
		return false;
	}

	public static function convert_objects( $args ) {
		if ( ! is_array( $args ) || empty( $args ) ) {
			return $args;
	}

		// Convert WordPress objects to Timber objects.
		foreach ( $args as &$arg ) {
			if ( $arg instanceof \WP_Term ) {
				$arg = Timber::get_term( $arg );
			}
		}

		$args = self::maybe_convert_to_collection( $args );

		return $args;
	}

	/**
	 * Convert arrays of WooCommerce product objects to PostCollections of Timber Product posts.
	 *
	 * @param array $args Template arguments
	 *
	 * @return array
	 */
	public static function maybe_convert_to_collection( $args ) {
		$collections = [];

		// Loop through args and add to collections array if it’s an array of WC_Product objects
		foreach ( $args as $key => $arg ) {
			// Bailout if not array or not array of WC_Product objects
			if ( ! is_array( $arg ) || empty( $arg ) || ! isset( $arg[0] )
			     || ! is_object( $arg[0] ) || ! is_a( $arg[0], 'WC_Product' )
			) {
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
			 * They will be converted to Product objects in the Post Collection using class maps.*
			 */
			$posts = array_map( function( $post ) {
				if ( $post instanceof \WC_Product ) {
					return get_post( $post->get_id() );
				}

				return $post;
			}, $posts );

			$args[ $collection ] = Timber::get_posts( $posts );
		}

		return $args;
	}

	/**
	 * Render default Twig templates.
	 *
	 * This function can be called from `woocommerce.php` template file in the root of the theme. It
	 * mimics the logic used by WooCommerce to sort out which template to load and tries to load
	 * the corresponding Twig file. It builds up an array with Twig templates to check for. Timber
	 * will use the first Twig file that exists. In addition to the default WooCommerce template
	 * files, there are some opininated "Goodies" that can make your life easier. E.g., you don’t
	 * have to use *woocommerce/single-product.twig*, but can use *woocommerce/single.twig*.
	 *
	 * If you have your own solution going on or need to do more checks, you don’t have to call this
	 * function.
	 *
	 * @api
	 * @param array $context Initial context.
	 *
	 * @see  WC_Template_Loader::get_template_loader_files()
	 * @todo Add functionality for product tags
	 *
	 */
	public static function render_default_template( $context = [] ) {
		$context = array_merge( Timber::context(), $context );

		$templates = [];

		if ( is_singular( 'product' ) ) {
			$post = $context['post'];

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
					$woocommerce_context['term'] = Timber::get_term( get_queried_object() );
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
