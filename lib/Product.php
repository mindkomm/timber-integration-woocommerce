<?php

namespace Timber\Integrations\WooCommerce;

use Timber\Term;

/**
 * Class Product
 *
 * @api
 */
class Product extends \Timber\Post {
	/**
	 * @var null|\WC_Product
	 */
	public $product = null;

	/**
	 * Product constructor.
	 *
	 * @example
	 * ```php
	 * // Get a product post by ID
	 * new Timber\Integrations\WooCommerce\Product( 354 );
	 * ```
	 *
	 * You can also use the `Product` class in Twig.
	 *
	 * ```twig
	 * {% set product = Product(354) %}
	 * ```
	 *
	 * @api
	 * @param mixed $post A post object or an object of class WC_Product or a class that inherits from WC_Product.
	 */
	public function __construct( $post = null ) {
		/**
		 * Check if the object is an instance of WC_Product or inherits from WC_Product.
		 *
		 * In that case, get the post ID from the product and then let Timber get the post through the parent
		 * constructor of this class.
		 */
		if ( $post instanceof \WC_Product ) {
			parent::__construct( $post->get_id() );
			$product = $post;
		} else {
			parent::__construct( $post );
			$product = wc_get_product( $this->ID );
		}

		/**
		 * Filters the WooCommerce product
		 */
		$product = apply_filters( 'timber/integration/woocommerce/product', $product, $post );

		$this->product = $product;
	}

	/**
	 * Get the first assigned product category.
	 *
	 * @api
	 * @return bool|\Timber\Term
	 */
	public function category() {
		$categories = $this->product->get_category_ids();

		if ( $categories ) {
			$category = reset( $categories );
			$category = new Term( $category );

			return $category;
		}

		return false;
	}

	/**
	 * Get a WooCommerce product attribute by slug.
	 *
	 * @api
	 *
	 * @param string $slug          The name of the attribute to get.
	 * @param bool   $convert_terms Whether to convert terms to Timber\Term objects.
	 *
	 * @return array|false
	 */
	public function get_product_attribute( $slug, $convert_terms = true ) {
		$attributes = $this->product->get_attributes();

		if ( ! $attributes || empty( $attributes ) ) {
			return false;
		}

		/**
		 * @var \WC_Product_Attribute|false $attribute
		 */
		$attribute = false;

		foreach ( $attributes as $key => $value ) {
			if ( "pa_{$slug}" === $key ) {
				$attribute = $attributes[ $key ];
				break;
			}
		}

		if ( ! $attribute ) {
			return false;
		}

		if ( $attribute->is_taxonomy() ) {
			$terms = wc_get_product_terms(
				$this->product->get_id(),
				$attribute->get_name(),
				array(
					'fields' => 'all',
				)
			);

			// Turn WP_Terms into instances of Timber\Term
			if ( $convert_terms ) {
				$terms = array_map( function( $term ) {
					return new Term( $term );
				}, $terms );
			}

			return $terms;
		}

		return $attribute->get_options();
	}
}
