# Mini Cart

If you want to display a "mini cart" that displays the cart contents count and total price, you can [use the `woocommerce_add_to_cart_fragments`](https://docs.woocommerce.com/document/show-cart-contents-total/) hook. Here’s a class you can save in `inc/woocommerce/WooCommerce_Cart.php`:

```php
<?php

use Timber\Timber;

class Theme_Cart {
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

new Theme_Cart();
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
