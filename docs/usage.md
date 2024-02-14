---
title: "Working with the Integration"
linktitle: "Usage"
slug: "usage"
weight: 200
menu:
  main:
    parent: "woocommerce"
---

## Stay compatible

When working with WooCommerce you’ll want to stay as compatible to WooCommerce as possible. If you stay compatible, it will be easier for you to update WooCommerce and your theme. Here are some (maybe opinionated) recommendations:

- Try to edit as few WooCommerce templates as possible. If you want to change something, work with actions and filters provided by WooCommerce as much as you can.
- Never delete a WooCommerce hook from a frontend template. Unhook functionalities you don’t need through `remove_filter()` and `remove_action()`.

## Template selection

In WooCommerce, you have the following template names that you can overwrite:

- single-product.php
- archive-product.php
- taxonomy-`{taxonomy}`.php
- taxonomy-`{taxonomy}`-`{term_slug}`.php

In this list, `{taxonomy}` will be `product_cat` for product categories or `product_tag` for product tags.

With the integration, you can also use `single.twig` or `archive.twig`. Here’s a list of file names that the integration looks out for (and will be selected in that order):

**Single product posts**

- single-`{post_name}`.twig
- single-product.twig
- single.twig

**Shop categories**

- taxonomy-`{taxonomy}`-`{term_slug}`.twig
- taxonomy-`{term_slug}`.twig
- taxonomy.twig

**Shop archive**

- archive-product.twig
- archive.twig

## Automatic Twig partial selection

WooCommerce allows you to [override templates](https://docs.woocommerce.com/document/template-structure/) by adding files to the **woocommerce** folder of your theme. For example, you’re probably used to have a **woocommerce/single-product.php** file in your theme. With the integration, you don’t necessarily need that PHP file, but you can directly add a Twig file instead. The integration **first checks if a Twig template exists**. Here’s an example:

Instead of adding a file **woocommerce/single-product/related.php** to your theme, you can directly add a file **views/woocommerce/single-product/related.twig**, which the integration will render instead of the PHP file.

There’s an exception for this: You can’t add a **single-product-reviews.twig** file that overrides the **single-product-reviews.php** file, because reviews are loaded differently than normal WooCommerce templates. Unfortunately, we can’t hook into it. The solution is to add **single-product-reviews.php** to your theme and update it there.

### Template context

In your Twig templates that you add in the **woocommerce** folder, you’ll have the following variables available when you’re in the context of a product (if the `$product` global is set):

- `product` - The currently displayed product.
- `post_id` - The ID of the currently displayed post in the loop.
- `post` - The Timber Post instance of the product post.

This means that you can directly do something like the following:

```twig
{{ product.get_name() }}
```

Or if you add custom meta data to your product posts:

```
{{ post.meta('ingredients') }}
```

#### The `wc` variable

Whenever `wc_get_template()` is called, the arguments that WooCommerce would pass to the template as global variables will be available under the `wc` variable in Twig.

For example, consider the following loop section in the PHP template for the related products:

```php
<?php foreach ( $related_products as $related_product ) : ?>
    <?php
        $post_object = get_post( $related_product->get_id() );

        setup_postdata( $GLOBALS['post'] =& $post_object );
        
        wc_get_template_part( 'content', 'product' );
    ?>
<?php endforeach; ?>
```

There’s a `$related_products` variable that is passed to the template. In your Twig template file **views/woocommerce/single-product/related.twig**, you can then access that variable through `wc.related_products`:

```twig
{% for post in wc.related_products %}
    {{ fn('wc_get_template_part', 'content', 'product' ) }}
{% endfor %}
```

### Template versions and system status

WooCommerce has a system status page that will show you which version of the templates you’re using. You can access it through **WooCommerce → Status**. Look for the templates section. Read more about this in the [WooCommerce documentation about outdated templates](https://developer.woo.com/docs/how-to-fix-outdated-woocommerce-templates/).

WooCommerce sets a `@version` tag in the DocBlock of the template that it compares with the version of your overwritten template. If the version of the template is outdated, WooCommerce will show you a warning in the system status.

**archive-product.php**

```php
<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * …
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */
```

You can use the same for your Twig templates. You can add a `@version` tag to a DocBlock in your Twig file. The WooCommerce system status will pick it up.

**archive-product.twig**

```twig
{#
 # @version 3.4.0
 #}
```

Make sure you don’t put it on one line, but add it on a separate line with after the opening comment tag (`{#`). Otherwise WooCommerce won’t pick it up.

**🚫 This will not work.**

```twig
{# @version 3.4.0 #}
```

Be aware that WooCommerce caches the system status in a transient named `wc_system_status_theme_info`. If you don’t see any templates in the system status, clearing the transient might help.

## Product global

When working with WooCommerce, you’re maybe used to the `$product` global. The integration handles that global variable for you. You don’t have to manually set up the product global in your loops. The product object is available under `post.product` in Twig. Here’s an example for the `get_attribute` object on `$product`.

```twig
{{ post.product.get_attribute('ingredient') }}
```

What is the difference between `post` and `product` in the example above?

- `post` is an instance of `Timber\Post`. In the case of this integration, it’s an instance of `Timber\Product`, which extends `Timber\Post`.
- `product` is an instance of [`WP_Product`](https://docs.woocommerce.com/wc-apidocs/class-WC_Product.html), which is not really a post, but an object that handles all functionalities related to a WooCommerce product. 

## WooCommerce context

The integration adds some context variables to the global Timber context. When you access the context through `Timber::get_context()`, you’ll get access to certain variables.

### Global context

Global context variables are always available.

- `cart` – The [WooCommerce cart object](https://docs.woocommerce.com/wc-apidocs/class-WC_Cart.html).

### Template based context

In addition to the global context, you’ll have other context variables available based on the template file you’re currently working with.

- `post` – Will be an instance of `Timber\Product` when a singular product page is displayed (`is_singular('product')`). If the [shop page](https://docs.woocommerce.com/document/woocommerce-pages/) is displayed, it will be a `Timber\Post` with that page as the contents.
- `term` – Will be set when a shop taxonomy term is displayed (when [`is_product_taxonomy()`](https://docs.woocommerce.com/wc-apidocs/function-is_product_taxonomy.html) applies).

    **views/woocommerce/archive-product.twig**

    ```twig
    {% if term.description %}
        <div class="description">{{ term.description }}
    {% endif %}
    ```
- `title` – Title to display on archive pages. Result of [woocommerce_page_title()](https://docs.woocommerce.com/wc-apidocs/function-woocommerce_page_title.html).

    **views/woocommerce/archive-product.twig**

    ```twig
    <h1 class="heading-1">{{ title }}</h1>
    ```

## Hooks

In Timber, you can call hooks like this:

```twig
{# Beware, could lead to errors. #}
{% do action('hook_name', param1, param2) %}
```

But because of compatibility problems with how Timber works with parameters passed to these hooks, we added our own function. Use `wc_action()` instead of `action()`.

```twig
{% do wc_action('woocommerce_single_product_summary') %}
```

## Structured Data

WooCommerce uses the hook `woocommerce_shop_loop` to set the structured data for different products. This hook is normally called in **templates/archive-product.php**. You don’t have to call it manually though, because it’s already called when you start looping through posts.

**views/woocommerce/archive-product.twig**

```twig
{% for post in posts %}
    {# Structured data will be set up here #} 
    <div class="list-items">
        {% include 'woocommerce/teaser-product.twig' %}
    </div>
{% endfor %}
```

## Translate WooCommerce templates to Twig

When you translate WooCommerce templates to Twig, you need to be extra careful. There are many hooks and filters that you should include, but there are exceptions. For example, there are hooks that are only responsible for displaying loop wrappers.

**templates/archive-product.php**

```php
<?php woocommerce_product_loop_start(); ?>
    <?php woocommerce_product_subcategories(); ?>

    <?php while ( have_posts() ) : the_post(); ?>
        <?php
            /**
             * woocommerce_shop_loop hook.
             *
             * @hooked WC_Structured_Data::generate_product_data() - 10
             */
            do_action( 'woocommerce_shop_loop' );
        ?>
        <?php wc_get_template_part( 'content', 'product' ); ?>
    <?php endwhile; // end of the loop. ?>
<?php woocommerce_product_loop_end(); ?>
```
 
The `woocommerce_product_loop_start` function will load `loop/loop-start.php`, which will only display the following:

```html
<ul class="products">
```

The `woocommerce_product_loop_end` function will load `loop/loop-end.php` respectively and display a closing tag:

```html
</ul>
```

It’s up to you then, whether you want to create a `loop/loop-start.twig` and `loop/loop-end.twig` file. You could also directly write the opening and closing `<ul>` tags in your Twig archive template.

**views/woocommerce/archive-product.twig**

```twig
<div class="list-products">
    {% do fn('woocommerce_product_subcategories') %}

    {##
     # Depending on your WooCommerce display settings,
     # the `woocommerce_product_subcategories` function
     # might reset the $wp_query global. By calling `have_posts()`,
     # we check if there are posts we can display.
     #}
    {% for post in posts if fn('have_posts') %}
        <ul class="products">
            {% include 'woocommerce/teaser-product.twig' %}
        </div>
    {% endfor %}
</div>
```

## Working with Post Collections

If you have a collection of product posts that you want to convert to Timber posts, you can use `Timber::get_posts()`. Timber will automatically return `Timber\Integration\WooCommerce\Product` posts.

Then, you will have to wrap your posts in a `Timber\PostCollection`. That will make sure that the correct `$product` global will be set up when you loop over your posts. (If you don’t do this, you will run into errors where the permalink or the price of all products in a loop will be the same.)

Here’s an example where we get the featured products and convert them to a collection you can use.

```php
use Timber\PostCollection;

$posts = Timber::get_posts( wc_get_featured_product_ids() );
$posts = new PostCollection( $posts );
```

You could use this to display the posts on a page.

**page.php**

```php
<?php

use Timber\PostCollection;

$context = Timber::get_context();

$context['featured_products'] = new PostCollection(
    Timber::get_posts( wc_get_featured_product_ids() )
);

Timber::render( 'page.twig', $context );
```

**page.twig**

```twig
{{ include('woocommerce/products.twig', {
    posts: featured_products
}) }}
```

**woocommerce/products.twig**

```twig
{{ fn('woocommerce_product_loop_start') }}

{% for post in posts %}
    {{ fn('wc_get_template_part', 'content', 'product' ) }}
{% endfor %}

{{ fn('woocommerce_product_loop_end') }}
```

## Filter posts from Twig

Normally when working with Timber, you’d want to filter post data before you pass it to a Twig template. With WooCommerce, this is maybe a little more complicated. You could write your own **woocommerce.php** that handles all the data. But remember, we want to interfere with WooCommerce as little as possible.

If you don’t want to touch **woocommerce.php**, the other possibility is to filter posts when when they are used.

```twig
{% for post in fn('my_posts_filter', posts) %}
    {# Display post #}
{% endfor %}
```

You would then define a function in PHP that takes in the posts and filters them. However, be aware that you then always need to return a `PostCollection` instead of an array so that the single posts will be set up properly:

```php
function my_posts_filter( $posts ) {
    $filtered_posts = array_filter( $posts, function( $post ) {
        return $post->product->is_virtual();
    } );

    return new Timber\PostCollection( $filtered_posts );
}
```
