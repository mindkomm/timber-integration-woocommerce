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
		 * woocommerce_shop_loop hook.
		 *
		 * Used to set the structured for a post, which is then inserted into the footer.
		 *
		 * @hooked WC_Structured_Data::generate_product_data() - 10
		 */
		do_action( 'woocommerce_shop_loop' );

		return $post;
	}
}
