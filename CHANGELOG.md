# Timber Integration for WooCommerce

## [3.0.0](https://github.com/mindkomm/timber-integration-woocommerce/compare/v2.0.0...v3.0.0) (2025-04-28)


### ⚠ BREAKING CHANGES

* Deprecate `theme/woocommerce/views_folder` filter in favor of `timber/woocommerce/views_folder`
* Deprecate timber/integration/woocommerce/product filter in favor of timber/woocommerce/product ([#50](https://github.com/mindkomm/timber-integration-woocommerce/issues/50))
* Remove `woocommerce_shop_loop` hook call in `Product::setup()` to reduce side effects ([#48](https://github.com/mindkomm/timber-integration-woocommerce/issues/48))
* Update minimum PHP version to 7.4
* Add compatibility for Timber 2.x

### Features

* Add `timber/woocommerce/views_folder` filter ([4c0222c](https://github.com/mindkomm/timber-integration-woocommerce/commit/4c0222cc6376f174f61113a8cf233f91eda1ff18))
* Deprecate `theme/woocommerce/views_folder` filter in favor of `timber/woocommerce/views_folder` ([ac58e4f](https://github.com/mindkomm/timber-integration-woocommerce/commit/ac58e4fc991ad79fa2057660c5be285d5af278d2))
* Deprecate timber/integration/woocommerce/product filter in favor of timber/woocommerce/product ([#50](https://github.com/mindkomm/timber-integration-woocommerce/issues/50)) ([359eddb](https://github.com/mindkomm/timber-integration-woocommerce/commit/359eddbeec707b90ad4a7cd6fe4b2edf3ba9e458))
* Remove `woocommerce_shop_loop` hook call in `Product::setup()` to reduce side effects ([#48](https://github.com/mindkomm/timber-integration-woocommerce/issues/48)) ([484e621](https://github.com/mindkomm/timber-integration-woocommerce/commit/484e6211d3b703626bce9daea7bbd00decf39f36))


### Bug Fixes

* Add compatibility for Timber 2.x ([5d7a5ea](https://github.com/mindkomm/timber-integration-woocommerce/commit/5d7a5ea0bc49d439ee7cd9d3979cdfa07494a187))
* **docs:** Update Getting Started guide with recommended way of initializing the integration ([#45](https://github.com/mindkomm/timber-integration-woocommerce/issues/45)) ([4de815c](https://github.com/mindkomm/timber-integration-woocommerce/commit/4de815cdac06f6e648a6330c32eef077965c5c42))
* Fix a bug when context didn’t contain a post for singular product templates ([1a2b3c8](https://github.com/mindkomm/timber-integration-woocommerce/commit/1a2b3c811e84c7f4daa3967dd76817a6bceda968))
* Fix a compatibility bug with Timber 2.2.0 ([3fa43c8](https://github.com/mindkomm/timber-integration-woocommerce/commit/3fa43c893a8ff39edf8c7fa17770700f37c6c1e1))
* Fix a typo ([9bfc96f](https://github.com/mindkomm/timber-integration-woocommerce/commit/9bfc96f0fe3ab8f8ed39a889f9edd5d79847f60b))
* Fix bugs when templates are accessed in the WooCommerce status screen ([7988120](https://github.com/mindkomm/timber-integration-woocommerce/commit/79881205eacdbf0eabfe50c86596be6fcb16f536))
* Fix documentation for 1.x ([1b964fa](https://github.com/mindkomm/timber-integration-woocommerce/commit/1b964fa7f24f90d81fae2b73ea553f40d8514fa8))
* Remove args for the init function ([486766c](https://github.com/mindkomm/timber-integration-woocommerce/commit/486766cff5a12e95014fdd6c8a4f518e51a333f3))
* Update minimum PHP version to 7.4 ([7fe7518](https://github.com/mindkomm/timber-integration-woocommerce/commit/7fe751878521e6d9bf527052a0176f1e693a982b))
* Use proper integration init ([ee42a2b](https://github.com/mindkomm/timber-integration-woocommerce/commit/ee42a2bdd4cf02203b1094f5e54edd8ae3df464b))


### Miscellaneous Chores

* **1.x:** release 1.1.0 ([#47](https://github.com/mindkomm/timber-integration-woocommerce/issues/47)) ([ff6a3a6](https://github.com/mindkomm/timber-integration-woocommerce/commit/ff6a3a6f1ac5d2b8b374fd8b21ff84f583137e0e))
* **2.x:** release 2.0.0 ([#52](https://github.com/mindkomm/timber-integration-woocommerce/issues/52)) ([67a88cd](https://github.com/mindkomm/timber-integration-woocommerce/commit/67a88cd88b1b643fd5e94fb87ace24d037b31795))

## [2.0.0](https://github.com/mindkomm/timber-integration-woocommerce/compare/v1.1.0...v2.0.0) (2025-04-28)


### ⚠ BREAKING CHANGES

* Deprecate `theme/woocommerce/views_folder` filter in favor of `timber/woocommerce/views_folder`
* Deprecate timber/integration/woocommerce/product filter in favor of timber/woocommerce/product ([#50](https://github.com/mindkomm/timber-integration-woocommerce/issues/50))
* Remove `woocommerce_shop_loop` hook call in `Product::setup()` to reduce side effects ([#48](https://github.com/mindkomm/timber-integration-woocommerce/issues/48))

### Features

* Deprecate `theme/woocommerce/views_folder` filter in favor of `timber/woocommerce/views_folder` ([ac58e4f](https://github.com/mindkomm/timber-integration-woocommerce/commit/ac58e4fc991ad79fa2057660c5be285d5af278d2))
* Deprecate timber/integration/woocommerce/product filter in favor of timber/woocommerce/product ([#50](https://github.com/mindkomm/timber-integration-woocommerce/issues/50)) ([359eddb](https://github.com/mindkomm/timber-integration-woocommerce/commit/359eddbeec707b90ad4a7cd6fe4b2edf3ba9e458))
* Remove `woocommerce_shop_loop` hook call in `Product::setup()` to reduce side effects ([#48](https://github.com/mindkomm/timber-integration-woocommerce/issues/48)) ([484e621](https://github.com/mindkomm/timber-integration-woocommerce/commit/484e6211d3b703626bce9daea7bbd00decf39f36))


### Bug Fixes

* Fix bugs when templates are accessed in the WooCommerce status screen ([7988120](https://github.com/mindkomm/timber-integration-woocommerce/commit/79881205eacdbf0eabfe50c86596be6fcb16f536))

## [1.1.0](https://github.com/mindkomm/timber-integration-woocommerce/compare/1.0.1...v1.1.0) (2024-09-10)


### Features

* Add `timber/woocommerce/views_folder` filter ([4c0222c](https://github.com/mindkomm/timber-integration-woocommerce/commit/4c0222cc6376f174f61113a8cf233f91eda1ff18))


### Bug Fixes

* **docs:** Update Getting Started guide with recommended way of initializing the integration ([#45](https://github.com/mindkomm/timber-integration-woocommerce/issues/45)) ([4de815c](https://github.com/mindkomm/timber-integration-woocommerce/commit/4de815cdac06f6e648a6330c32eef077965c5c42))

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

## 0.8.0 - 2024-02-14

- Fixed a bug when Twig templates would be falsely rendered on the /system_status endpoint of the WooCommerce REST API.
- Added the possibility to make the system status work with [outdated WooCommerce templates](https://github.com/mindkomm/timber-integration-woocommerce/blob/main/docs/usage.md#template-versions-and-system-status).
- Changed Twig template loader to not load views from the caller directory.

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
- Added a `post` variable to the context in [Twig template partials](https://github.com/mindkomm/timber-integration-woocommerce/blob/main/docs/usage.md#automatic-twig-partial-selection).
- Fixed a bug when calling `Timber\Post::__construct()` messed up the `$product` global.
- Fixed a compatibility issue with admin-ajax.

## 0.5.3.1 - 2020-07-29

- Fixed merge bug.

## 0.5.3 - 2020-07-29

- Added Twig function `wc_action()` that can be used instead of `action()` when calling hooks from Twig. In Twig, you would use it like this: `{% do wc_action('woocommerce_single_product_summary') %}`. This was added to fix compatibility problems when hooks were used with parameters. (See #14, thanks @pascalknecht)
- Added support for [Automatic Twig partial selection](https://github.com/mindkomm/timber-integration-woocommerce/blob/main/docs/usage.md#automatic-twig-partial-selection) when using `wc_get_template_part()`. You could only use `wc_get_template()` before. Now, you can use both.
- Fixed a bug when the `$post` global was not properly set when looping over products.
- Updated default templates.
- Updated internal repository folder structure.

## 0.5.2 - 2018-09-06

- Fixed bug that prevented the integration from working with different versions of Twig. Thanks @chrislind and @VincentLoy!
- Removed non-working support for installing the integration as a WordPress plugin. The integration can only be installed through Composer. Future versions of Timber will will drop support for installation as a plugin. That’s why drop it here, too.

## 0.5.1 - 2018-04-30

- Improved default templates.
- Improved documentation.
