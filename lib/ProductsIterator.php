<?php

namespace Timber\Integrations\WooCommerce;

/**
 * Class ProductsIterator
 *
 * @todo After Timber 2.0 is released, this class can probably be removed.
 */
class ProductsIterator extends \Timber\PostsIterator {
	/**
	 * Set $product global in addition to $post global.
	 *
	 * For some functionality, WooCommerce works with a global named $product. When looping over
	 * multiple product posts, this global is not automatically set. With this custom ArrayIterator,
	 * we can prepare the globals that are needed.
	 *
	 * @see \Timber\PostsIterator::current()
	 *
	 * @return \Timber\Post
	 */
	public function current() {
		// Fire action when the loop has just started.
		if ( 0 === $this->key() ) {
			do_action_ref_array( 'loop_start', array( &$GLOBALS['wp_query'] ) );
		}

		$post = parent::current();

		if ( is_callable( [ $post, 'setup' ] ) ) {
			// Setup the $product global.
			// TODO: when wc_setup_product_data() uses get_post(), is the data cached?
			$post->setup();
		}

		/**
		 * Generate Structured Data for archives.
		 *
		 * Used to set the structured data for a post archive page, which is then inserted into the
		 * footer. This is only run on archive pages, where a woocommerce_before_shop_loop exists.
		 * For singular product pages, WooCommerce uses the `woocommerce_single_product_summary`
		 * hook to generate data.
		 *
		 * @hooked WC_Structured_Data::generate_product_data() - 10
		 */
		if ( ! is_singular( 'product' ) && did_action( 'woocommerce_before_shop_loop' ) > 0 ) {
			do_action( 'woocommerce_shop_loop' );
		}

		return $post;
	}

	/**
	 * Cleans up state before advancing to the next post.
	 *
	 * Calls the `teardown()` function of the current post. In the last run of a loop through posts,
	 * it will call the 'loop_end' hook to improve compatibility with WordPress.
	 *
	 * @since 2.0.0
	 */
	public function next() {
		/**
		 * The `loop_end` action is not the only thing we do to improve compatibility. There’s
		 * more going on in the Timber\Post::teardown() function. The compabitibility improvements
		 * live there, because they also need to work for singular templates, where there’s no loop.
		 */
		$post = $this->current();

		if ( is_callable( [ $post, 'teardown'] ) ) {
			$post->teardown();
		}

		// Fire action when the loop has ended.
		if ( $this->key() === $this->count() - 1 ) {
			do_action_ref_array( 'loop_end', array( &$GLOBALS['wp_query'] ) );
			wp_reset_postdata();
		}

		parent::next();
	}
}
