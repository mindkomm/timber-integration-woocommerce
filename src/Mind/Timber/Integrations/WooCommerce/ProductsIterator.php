<?php

namespace Mind\Timber\Integrations\WooCommerce;

/**
 * Class ProductsIterator
 *
 * @package Timber\Integrations\WooCommerce
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
		global $post, $product;

		$post = parent::current();
		$product = wc_get_product( $post->ID );

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
