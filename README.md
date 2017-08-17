# Timber Integration for WooCommerce

## Timber and WooCommerce

When working with WooCommerce you’ll want to stay as compatible to updates to WooCommerce as possible. This means that you should:

- Work with actions and filters provided by WooCommerce (or the Storefront theme) as much as you can.
- Replace functions where you need them.

## Installation

Activate the plugin.

Add a file `inc/woocommerce.php` to your theme with the following contents:

```php
<?php

class WooCommerce_Custom {
    public function __construct() {
        if ( class_exists( 'WooCommerce' ) ) {
            new \Mind\Timber\Integrations\WooCommerce\WooCommerce();
        }

        add_action( 'after_setup_theme', [ $this, 'hooks' ] );
    }

    /**
     * Customize WooCommerce
     *
     * @see plugins/woocommerce/includes/wc-template-hooks.php for a list of actions.
     *
     * Everything here is hook to after_setup_theme, because child theme functionality runs before parent theme
     * functionality. By hooking it, we make sure it runs after all hooks in the parent theme were registered.
     */
    public function hooks() {
       
    }
}

new WooCommerce_Custom();
```

Add a file `woocommerce.php` to your theme with the following contents:

```
<?php
/**
 * woocommerce.php
 *
 * Usually, WooCommerce template files are placed in the woocommerce/ folder of the theme. If a woocommerce.php file
 * exists, this will be used for all templates.
 */
\Mind\Timber\Integrations\WooCommerce\WooCommerce::render_default_template();

```

## Arrays of posts

When you loop over posts, you need to make sure that the posts are a collection of Timber WooCommerce Products.

WooCommerce makes use of the `$product` global, which needs to be set specifically for each post. But no worries, you don’t have to do that manually, Timber takes care of this.

Use `PostQuery()` instead of `Timber::get_posts()`.
