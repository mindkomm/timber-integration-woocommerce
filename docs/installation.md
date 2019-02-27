---
title: "Installation"
weight: 100
menu:
  main:
    parent: "woocommerce"
---

## Installation

The WooCommerce integration comes as a separate package. Before you install it, make sure that you’ve installed the [WooCommerce plugin](https://wordpress.org/plugins/woocommerce/).

You can then install the integration with [Composer](https://getcomposer.org/):

```bash
composer require mindkomm/timber-integration-woocommerce
```

Then make sure that you require the autoload file. If you install the integration as a dependency of your theme, then this would be in your **functions.php**:

```php
// Load Composer dependencies
require_once 'vendor/autoload.php';
```

You can’t install the integration as a WordPress plugin.

## Prepare your theme

### Add theme support

For better support with WooCommerce, you should [declare theme support for WooCommerce](https://docs.woocommerce.com/document/woocommerce-theme-developer-handbook/#section-5).

**functions.php**

```php
add_action( 'after_setup_theme', function() {
    /**
     * Add theme support for WooCommerce.
     *
     * @link https://docs.woocommerce.com/document/woocommerce-theme-developer-handbook/#section-5
     */
    add_theme_support( 'woocommerce' );
} );
```

If you want to use the [gallery features](https://docs.woocommerce.com/document/woocommerce-theme-developer-handbook/#section-8), follow along with adding support for it:

```php
add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );
```

### Initialize integration

**functions.php**

```php
if ( class_exists( 'WooCommerce' ) ) {
    Timber\Integrations\WooCommerce\WooCommerce::init();
}
```

From here on, you should be good to go. The integration hooks into the [context](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/usage.md#woocommerce-context) and adds features specific for WooCommerce. If you’re starting your theme from scratch, you’ll probably want to look at the following optional sections. You might also benefit if you already have Timber theme.

## Optional: add woocommerce.php

A **woocommerce.php** file that is placed into the root of your theme has priority over all other templates that you place in the `woocommerce/` folder of your theme. For example, **woocommerce.php** will take precedence over **woocommerce/archive-product.php**. If you use this file, WooCommerce will always use this file to render shop templates.

Add a file `woocommerce.php` to the root your theme with the following contents:

```php
<?php
/**
 * woocommerce.php
 *
 * Usually, WooCommerce template files are placed in the woocommerce/ folder of the theme. If a
 * woocommerce.php file exists, this will be used for all templates.
 */
Timber\Integrations\WooCommerce\WooCommerce::render_default_template();
```

The function `render_default_template()` makes it possible for you to render the default files used by WooCommerce. If you have more complex functionality that you need to apply, you can also copy the contents of the `render_default_template` function into `woocommerce.php` directly and adapt it there.

## Optional: copy default templates to your theme

In the **defaults** folder of the integration, you’ll find [default Twig templates](https://github.com/MINDKomm/timber-integration-woocommerce/tree/master/defaults) for a couple of WooCommerce templates. They are examples for how you could translate templates from PHP to Twig.

You can copy the templates over to a **woocommerce** folder in the Twig views folder of your theme.

```txt
your-theme/views/woocommerce/single-products/tabs/description.twig
your-theme/views/woocommerce/archive-product.twig
your-theme/views/woocommerce/single-product.twig
your-theme/views/woocommerce/teaser-product.twig
```

If you named your views folder differently, copy the files there. If you want to use a different name than **woocommerce** for the subfolder, you can pass the name as an argument when you initialize the integration:

```php
Timber\Integrations\WooCommerce\WooCommerce::init( array(
    'subfolder' => 'woo',
) );
```

## Optional: Use a helper class

You could either loosely add theme support in your and the initialization call for the integration to your **functions.php**, or you can wrap everything in a proper class:

```php
<?php

class WooCommerceTheme {
    public function init() {
        if ( class_exists( 'WooCommerce' ) ) {
            \Timber\Integrations\WooCommerce\WooCommerce::init();
        }

        // Optional: Disable default WooCommerce image functionality.
        // Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();

        add_action( 'after_setup_theme', [ $this, 'hooks' ] );
        add_action( 'after_setup_theme', [ $this, 'setup' ] );
    }

    /**
     * Customize WooCommerce.
     *
     * Add your hooks to customize WooCommerce here.
     *
     * Everything here is hooked to `after_setup_theme`, because child theme functionality runs
     * before parent theme functionality. By hooking it, we make sure it runs after all hooks in
     * the parent theme were registered.
     *
     * @see plugins/woocommerce/includes/wc-template-hooks.php for a list of available actions.
     */
    public function hooks() {
        // Example: Disable product tabs.
        /*remove_action(
            'woocommerce_after_single_product_summary',
            'woocommerce_output_product_data_tabs'
        );*/
    }

    /**
     * Setup.
     */
    public function setup() {
        /**
         * Add theme support for WooCommerce.
         *
         * @link https://docs.woocommerce.com/document/woocommerce-theme-developer-handbook/#section-5
         */
        add_theme_support( 'woocommerce' );
    }
}
```

Make sure to require and initiate the class from your **functions.php**:

```php
require_once 'inc/WooCommerceTheme.php';

( new WooCommerce_Theme() )->init();
```
