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

You can’t install the integration as a WordPress plugin.

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

From here on, you should be good to go. The integration hooks into the [context](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/usage.md#woocommerce-context) and adds features specific for WooCommerce. If you’re starting your theme from scratch, you’ll probably want to look at the following optional sections. You might also benefit if you already have Timber theme.

## Optional: add woocommerce.php

A **woocommerce.php** file that is placed into the root of your theme has priority over all other templates that you place in the `woocommerce/` folder of your theme. For example, **woocommerce.php** will take precedence over **woocommerce/archive-product.php**. If you use this file, WooCommerce will always use this file to render shop templates.

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

## Optional: copy default templates to your theme

In the **defaults** folder of the integration, you’ll find [default Twig templates](https://github.com/MINDKomm/timber-integration-woocommerce/tree/master/defaults). You can copy them over to a **woocommerce** folder in the Twig views folder of your theme.

```txt
your-theme/views/woocommerce/archive-product.twig
your-theme/views/woocommerce/single-product.twig
your-theme/views/woocommerce/teaser-product.twig
```

If you named your views folder differently, copy the files there. If you want to use a different name than **woocommerce** for the subfolder, you can pass the name as an argument when you initialize the integration:

```php
Timber\Integrations\WooCommerce\WooCommerce::init( array( 'subfolder' => 'woo' ) );
```

## Optional: Use a helper class

You could either loosely add theme support and the initialization call for the integration to your functions.php, or you can wrap everything in a proper class:

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

Make sure to require the class from your **functions.php**:

```php
require_once 'inc/WooCommerce_Theme.php';
```
