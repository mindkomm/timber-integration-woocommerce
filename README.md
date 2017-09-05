# Timber Integration for WooCommerce

## Timber and WooCommerce

When working with WooCommerce you’ll want to stay as compatible to updates to WooCommerce as possible. This means that you should:

- Work with actions and filters provided by WooCommerce (or the Storefront theme) as much as you can.
- Replace functions where you need them.

## Installation

Activate the plugin.

Add a file `inc/woocommerce/WooCommerce_Custom.php` to your theme with the following contents:

```php
<?php

class WooCommerce_Custom {
    public function __construct() {
        if ( class_exists( 'WooCommerce' ) ) {
            new \Mind\Timber\Integrations\WooCommerce\WooCommerce();
        }

        // Disable WooCommerce image functionality
        // \Mind\Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();

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

Make sure to require that file from `functions.php`.

```php
require_once( 'inc/woocommerce/WooCommerce_Custom.php' );
```

Add a file `woocommerce.php` to the root your theme with the following contents:

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

The function `render_default_template()` makes it possible for you to render the default files used by WooCommerce. When working with WooCommerce, you’re probably used to have a `woocommerce/single-product.php` in your theme. You don’t need that anymore. You can now create a file `views/woocommerce/single-product.twig` in your Twig views folder directly.

## Automatic Twig partial selection

WooCommerce allows you to [override templates](https://docs.woocommerce.com/document/template-structure/) by adding files to the `woocommerce/` folder of your theme. The integration **first checks if a Twig template exists**. So instead of adding a file `woocommerce/single-product/related.php` to your theme, you can directly add a file `views/woocommerce/single-product/related.twig`, **without adding a PHP file**. The arguments that WooCommerce would pass to the template will be available under the `wc` variable. Here’s an example:

**views/woocommerce/single-product/related.twig**

```twig
{% for post in wc.related_products %}
    {% include 'woocommerce/teaser-product.twig' %}
{% endfor %}
```

## Product global

When working with WooCommerce, you’re maybe used to the `$product` global. The integration handles that global variable for you and makes it available under `post.product` in your Twig templates. Here’s an example for the `get_attribute` object on `$product`.

```twig
{{ post.product.get_attribute('ingredient') }}
```

## WooCommerce context

The integration adds some context variables to the global Timber context. This means, that when you access the context through `Timber::get_context()`, you’ll get access to the following variables:

- `cart` – The WooCommerce cart object.

## Cart

If you want to display a "mini cart" that displays the cart contents count and total price, you can [use the `woocommerce_add_to_cart_fragments`](https://docs.woocommerce.com/document/show-cart-contents-total/) hook. Here’s a class you can save in `inc/woocommerce/WooCommerce_Cart.php`:

```php
<?php

use Timber\Timber;

class My_WooCommerce_Cart {
    public function __construct() {
        add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_link_fragment' ] );
    }

    /**
     * Cart Fragments.
     *
     * Ensure cart contents update when products are added to the cart via AJAX.
     *
     * @param  array $fragments Fragments to refresh via AJAX.
     * @return array            Fragments to refresh via AJAX.
     */
    public function cart_link_fragment( $fragments ) {
        global $woocommerce;

        $fragments['a.navCart-contents'] = Timber::compile( 'woocommerce/cart/fragment-link.twig', [
            'cart' => WC()->cart,
        ] );

        return $fragments;
    }
}

new My_WooCommerce_Cart();
```

Don’t forget to require it in your **functions.php** file.

**views/woocommerce/cart/fragment-link.twig**

```twig
<a class="navCart-contents" href="{{ fn('wc_get_cart_url') }}" title="{{ __('View cart', 'my-theme-textdomain') }}">
    <span class="amount">{{ cart.get_cart_subtotal }}</span> <span class="count">{{ cart.get_cart_contents_count }}</span>
</a>
```

See that the link with the class `navCart-contents` is added to the `$fragments` global in the `cart_link_fragment` method? That will be a JavaScript selector that updates the contents in the link through AJAX. WooCommerce needs to know the HTML to update the cart with. We can make use of `Timber::compile` to use the same Twig template that we also use display the cart fragment in our theme:

**Twig**

```Twig
{% include 'woocommerce/cart/fragment-link.twig' %}
```

## Images

If you want to use your own image handling functionality and disable all WooCommerce functionality related to images, you can call `disable_woocommerce_images()`, which removes the hooks used by WooCommerce.

Call this in the `__construct()` function of **WooCommerce_Custom.php**:

```php
\Mind\Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();
```

You can display a product image like you would do it in every Timber theme.

```twig
{% if post.thumbnail %}
    <div class="product-image">
        <img src="{{ post.thumbnail.src('medium') }}">
    </div>
{% endif %}
```
