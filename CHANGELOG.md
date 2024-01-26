# Timber Integration for WooCommerce

## 1.0.0

### Breaking changes 💥

- Added support for [Timber 2.0](https://github.com/timber/timber/releases/tag/2.0.0) and removes support for Timber 1.x.
- Bumped minimum required PHP version to 7.4.
- Updated how to set up the integration.
- Removed `Product()` function in Twig. Use `get_post()` instead.
- Removed `wc_action()` Twig function. Use `{% do action() %}` instead of `{% do wc_action() %}`.

### Other changes

- Added support for PHP 8.0 and higher.

### New way to set up integration

**🚫 Before**

```php
if ( class_exists( 'WooCommerce' ) ) {
    \Timber\Integrations\WooCommerce\WooCommerce::init();
}
```

**✅ After**

```php
add_filter( 'timber/integrations', function ( array $integrations ): array {
    $integrations[] = new \Timber\Integrations\WooCommerce\WooCommerceIntegration();

    return $integrations;
} );
```

### Removed arguments for the integration

If you passed options to the `Timber\Integrations\WooCommerce\WooCommerce::init()`, you will have to change how you pass them. The new way to init the integration doesn’t take any arguments anymore.

#### Use a custom class for products

**🚫 Before**

```php
Timber\Integrations\WooCommerce\WooCommerce::init( [
    'product_class' => 'MyProductClass',
] );
```

**✅ After**

```php
add_filter( 'timber/product/classmap', function( $classmap ) {
    $classmap['product'] = 'MyProductClass';

    return $classmap;
}, 20 );
```

#### No more custom product iterator

Post iterators were removed in Timber 2.0. If you’ve used the `product_iterator` argument, you can use the `setup()` and `teardown()` methods on your custom product class instead.

#### Set a subfolder for the Twig templates

**🚫 Before**

```php
Timber\Integrations\WooCommerce\WooCommerce::init( [
    'subfolder' => 'woo',
] );
```

**✅ After**

```php
add_filter( 'timber/woocommerce/views_folder', function( $subfolder ) {
    return 'woo';
} );
```

### Updated Twig functions

**🚫 Before**

```twig
# Getting a product
<img src="{{ Product(id).thumbnail.src|resize(200, 200) }}">

# Calling an action
{% do wc_action('woocommerce_before_shop_loop') %}
```

**✅ After**

```twig
# Getting a product
<img src="{{ get_post(id).thumbnail.src|resize(200, 200) }}">

# Calling an action
{% do action('woocommerce_before_shop_loop') %}
```

## 0.7.1 - 2023-01-13

- Fixed a bug when product global is not present on singular product pages.

## 0.7.0 - 2023-01-13

- Updated PHP requirements to allow PHP 8.0 and higher.

## 0.6.1 - 2023-01-13

- Fixed a couple of issues with `$product` global not being kept in sync.
- Added small improvements in documentation.

## 0.6.0 - 2021-04-13

- Improved when this integration applies Product classes and Product Iterators to single posts and lists of posts. This should make it easier to have collections of WooCommerce products and other WordPress post types on the same page. Internally, this integration now uses a Class Map for the `product` post type. This means that you can also [extend](https://timber.github.io/docs/guides/extending-timber/) this integration’s `Timber\Integrations\WooCommerce\Product` class with your own `Product` class.
- Improved default **archive-product.twig** template and added default templates for **loop/loop-start.twig** and **loop/loop-end.twig**.
- Added a default **checkout/form-checkout.twig** template.
- Added a `$context` parameter to the `render_default_template()` function. When you pass a context to this function, it will be merged with Timber’s default context.
- Added a `post` variable to the context in [Twig template partials](https://github.com/mindkomm/timber-integration-woocommerce/blob/master/docs/usage.md#automatic-twig-partial-selection).
- Fixed a bug when calling `Timber\Post::__construct()` messed up the `$product` global.
- Fixed a compatibility issue with admin-ajax.

## 0.5.3.1 - 2020-07-29

- Fixed merge bug.

## 0.5.3 - 2020-07-29

- Added Twig function `wc_action()` that can be used instead of `action()` when calling hooks from Twig. In Twig, you would use it like this: `{% do wc_action('woocommerce_single_product_summary') %}`. This was added to fix compatibility problems when hooks were used with parameters. (See #14, thanks @pascalknecht)
- Added support for [Automatic Twig partial selection](https://github.com/mindkomm/timber-integration-woocommerce/blob/master/docs/usage.md#automatic-twig-partial-selection) when using `wc_get_template_part()`. You could only use `wc_get_template()` before. Now, you can use both.
- Fixed a bug when the `$post` global was not properly set when looping over products.
- Updated default templates.
- Updated internal repository folder structure.

## 0.5.2 - 2018-09-06

- Fixed bug that prevented the integration from working with different versions of Twig. Thanks @chrislind and @VincentLoy!
- Removed non-working support for installing the integration as a WordPress plugin. The integration can only be installed through Composer. Future versions of Timber will will drop support for installation as a plugin. That’s why drop it here, too.

## 0.5.1 - 2018-04-30

- Improved default templates.
- Improved documentation.
