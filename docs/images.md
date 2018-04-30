---
title: "Images"
weight: 300
menu:
  main:
    parent: "woocommerce"
---

You can work the image functionality that WooCommerce provides you, or you can display a product image like you would do it in every Timber theme, including all the image functionality (like resizing) you already know.

```twig
{% if post.thumbnail %}
    <div class="product-image">
        <img src="{{ post.thumbnail.src('medium') }}">
    </div>
{% endif %}
```

If you want to use your own image handling functionality and disable some WooCommerce frontend functionality related to images, you can call `disable_woocommerce_images()`, which removes the hooks used by WooCommerce for your **product archive and single product pages**. This might give you some performance improvements.

**functions.php**

```php
Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();
```

However, it’s still recommended to set the desired image sizes for your product images in the [WooCommerce settings](https://docs.woocommerce.com/document/woocommerce-customizer/#section-13), because certain WooCommerce extensions or plugins might rely on these sizes.

You don’t always need to create a whole Twig template if you only want to change an image. For example, if you want to change the product images displayed in the cart, you could use a filter:

```php
/**
 * Change cart image.
 */
add_filter( 'woocommerce_product_get_image', function( $url, $product, $size, $attr, $placeholder, $image ) {
    if ( ! is_cart() || 'woocommerce_thumbnail' !== $size ) {
        return $url;
    }
    
    return Timber::compile_string(
        '<img src="{{ Product(id).thumbnail.src|resize(200, 200) }}">',
        array( 'id' => $product->get_id() )
    );
}, 10, 6 );
```
