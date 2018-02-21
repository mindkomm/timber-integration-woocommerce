---
title: "Installation"
weight: 100
menu:
  main:
    parent: "woocommerce"
---

## Installation

The WooCommerce integration comes as a separate package/plugin. Before you install it, make sure that you’ve installed the [WooCommerce plugin](https://wordpress.org/plugins/woocommerce/).

### With Composer

You can then install the integration with Composer:

```bash
composer require mindkomm/timber-integration-woocommerce
```

### As a plugin

If you don’t use Composer, you can install the integration as a plugin that you’ll have to activate.

## Prepare your theme

### Add theme support

For better support with WooCommerce, you should [declare theme support for WooCommerce](https://github.com/woocommerce/woocommerce/wiki/Declaring-WooCommerce-support-in-themes). 

**functions.php**

```php
add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );
} );
```

### Initialize integration

**functions.php**

```php
if ( class_exists( 'WooCommerce' ) ) {
    Timber\Integrations\WooCommerce\WooCommerce::init();
}
```

### woocommerce.php

Add a file `woocommerce.php` to the root your theme with the following contents:

```php
<?php
/**
 * woocommerce.php
 *
 * Usually, WooCommerce template files are placed in the woocommerce/ folder of the theme. If a woocommerce.php file
 * exists, this will be used for all templates.
 */
Timber\Integrations\WooCommerce\WooCommerce::render_default_template();
```

The function `render_default_template()` makes it possible for you to render the default files used by WooCommerce. If you have more complex functionality that you need to apply, you can also copy the contents of the `render_default_template` function into `woocommerce.php` directly and adapt it there.

## Copy default templates to your theme

In the **defaults** folder of the integration, you’ll find [default Twig templates](https://github.com/MINDKomm/timber-integration-woocommerce/tree/master/defaults). You can copy them over to a **woocommerce** folder in the Twig views folder of your theme.

```txt
your-theme/views/woocommerce/archive-product.twig
your-theme/views/woocommerce/single-product.twig
your-theme/views/woocommerce/teaser-product.twig
```

If you named your views folder differently, copy the files there. If you want to use a different name than **woocommerce** for the subfolder, you can pass the name as an argument when you init the integration:

```php
Timber\Integrations\WooCommerce\WooCommerce::init( array( 'subfolder' => 'woo' ) );
```

## Optional: Use a helper class

You could either loosely add theme support and the initialization call for the integrations to your functions.php, or you can wrap everything in a proper class:

```php
<?php

class WooCommerce_Theme {
    public function __construct() {
        if ( class_exists( 'WooCommerce' ) ) {
            Timber\Integrations\WooCommerce\WooCommerce::init();
        }

        // Disable WooCommerce image functionality
        // Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();

        add_action( 'after_setup_theme', [ $this, 'hooks' ] );
        add_action( 'after_setup_theme', [ $this, 'setup' ] );
    }

    /**
     * Customize WooCommerce
     *
     * @see plugins/woocommerce/includes/wc-template-hooks.php for a list of actions.
     *
     * Everything here is hooked to `after_setup_theme`, because child theme functionality runs before parent theme
     * functionality. By hooking it, we make sure it runs after all hooks in the parent theme were registered.
     */
    public function hooks() {
       // Add your hooks to customize WooCommerce here
    }

    /**
     * Setup.
     */
    public function setup() {
        add_theme_support( 'woocommerce' );
    }
}

new WooCommerce_Theme();
```

Make sure to require the class from `functions.php`.

```php
require_once 'inc/WooCommerce_Theme.php';
```
