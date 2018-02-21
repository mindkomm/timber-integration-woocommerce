---
title: "Examples"
weight: 400
menu:
  main:
    parent: "woocommerce"
---

## Adapt the add-to-cart button

The default template for the add-to-cart button is called **loop/add-to-cart.php**. To change it, add a new file **views/woocommcerce/add-to-cart.twig**. Now you see that the contents of the template might look like this:

```php
<?php

echo apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
    sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
        esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
        isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
        esc_html( $product->add_to_cart_text() )
    ),
$product, $args );
```

How would you translate that to Twig? The essential functionality that we want to start with is the template of the `sprintf` function:

```twig
<a href="%s" data-quantity="%s" class="%s" %s>%s</a>
```

The first attribute `%s` will be replaced with `esc_url( $product->add_to_cart_url() )`. In Twig, this can be done like this:

```twig
<a href="{{ product.add_to_cart_url()|e('esc_url') }}">
```

See that we can directly use the product variable with `{{ product }}`. Remember, the integration makes the `$product` global available to you as a variable in Twig. Let’s now translate the `data-quantity` attribute:

```twig
<a data-quantity="{{ wc.quantity|default(1) }}">
```

Again, the integration adds all the arguments that you get in the template to `wc`. So `$args['quantity']` can be accessed through `wc.quantity`. Then, instead of writing a long if statement that checks for the existence of that value, we can use Twig’s [default filter](https://twig.symfony.com/doc/2.x/filters/default.html) that let’s us set a default value. The same applies for the class attribute:

```twig
<a class="{{ wc.class|default('button') }}">
```

Next, the attributes:

```twig
<a {{ wc.attributes ? fn('wc_implode_html_attributes', attributes) }}>
```

And finally, the text:

```twig
<a>{{ product.add_to_cart_text|e('esc_html') }}</a> 
```

Let’s look at the full template now:

```twig
<a
    href="{{ product.add_to_cart_url()|e('esc_url') }}"
    class="{{ wc.class|default('button') }}"
    data-quantity="{{ wc.quantity|default(1) }}"
    {{ wc.attributes ? fn('wc_implode_html_attributes', wc.attributes) }}
>{{ product.add_to_cart_text|e('esc_html') }}</a> 
```

You can now start to adapt this template to your needs.

We didn’t apply the `woocommerce_loop_add_to_cart_link` filter yet, which could be used to overwrite a template instead of adding it in `add-to-cart.twig`. Maybe you think that you might not need this filter. But remember that other plugins and WooCommerce extensions might use this filter as well. Every time you remove a filter or an action, you reduce the compatibility of your theme. It’s your choice! Let’s add it in this use case, by wrapping the whole link tag with a Twig [filter tag](https://twig.symfony.com/doc/2.x/tags/filter.html).

```twig
{% filter apply_filters('woocommerce_loop_add_to_cart_link', product, wc.attributes) %}
    <a
        href="{{ product.add_to_cart_url()|e('esc_url') }}"
        class="{{ wc.class|default('button') }}"
        data-quantity="{{ wc.quantity|default(1) }}"
        {{ wc.attributes ? fn('wc_implode_html_attributes', wc.attributes) }}
    >{{ product.add_to_cart_text|e('esc_html') }}</a>
{% endfilter %}
```

