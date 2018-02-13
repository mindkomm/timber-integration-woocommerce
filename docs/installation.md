# Installation

You can install the integration with Composer:

```bash
composer require mindkomm/timber-integration-woocommerce
```

Activate the plugin.

Add a class to your theme with the following contents:

```php
<?php

class WooCommerce_Theme {
    public function __construct() {
        $self = new self();

        if ( class_exists( 'WooCommerce' ) ) {
            Timber\Integrations\WooCommerce\WooCommerce::init();
        }

        // Disable WooCommerce image functionality
        // Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();

        add_action( 'after_setup_theme', [ $self, 'hooks' ] );
        add_action( 'after_setup_theme', [ $self, 'setup' ] );
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
require_once 'inc/woocommerce/WooCommerce_Theme.php';
```

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
