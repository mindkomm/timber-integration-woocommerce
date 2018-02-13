<?php

namespace Timber\Integrations\WooCommerce;

/**
 * Class ProductsIterator
 */
class ProductsIterator extends \ArrayIterator {
	/**
	 * Set $product global in addition to $post global.
	 *
	 * For some functionality, WooCommerce works with a global named $product. When looping over multiple product posts,
	 * this global is not automatically set. With this custom ArrayIterator, we can prepare the globals that are needed.
	 *
	 * @see \Timber\PostsIterator::current()
	 *
	 * @return \Timber\Post
	 */
	public function current() {
		$post = parent::current();

		// Setup the $product global
		// TODO: when wc_setup_product_data() uses get_post(), is the data cached?
		wc_setup_product_data( $post->ID );

		/**
		 * Generate Structured Data for archives.
		 *
		 * Used to set the structured data for a post archive page, which is then inserted into the footer.
		 * This is only run on archive pages, where a woocommerce_before_shop_loop exists. For singular product
		 * pages, WooCommerce uses the `woocommerce_single_product_summary` hook to generate data.
		 *
		 * @hooked WC_Structured_Data::generate_product_data() - 10
		 */
		if ( ! is_singular( 'product' ) && did_action( 'woocommerce_before_shop_loop' ) > 0 ) {
			do_action( 'woocommerce_shop_loop' );
		}

		return $post;
	}
}
