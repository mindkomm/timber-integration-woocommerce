# Working with the Integration

When working with WooCommerce you’ll want to stay as compatible to updates to WooCommerce as possible. This means that you should:

- Work with actions and filters provided by WooCommerce (or the Storefront theme) as much as you can.
- Replace functions where you need them.
- Never delete a hook from a frontend template. Unhook functionalities you don’t need through `remove_filter` and `remove_action`.

## Automatic Twig partial selection

WooCommerce allows you to [override templates](https://docs.woocommerce.com/document/template-structure/) by adding files to the `woocommerce/` folder of your theme. You’re probably used to have a `woocommerce/single-product.php` in your theme. With the integration, you don’t necessarily have to do that. The integration **first checks if a Twig template exists**. So instead of adding a file `woocommerce/single-product/related.php` to your theme, you can directly add a file `views/woocommerce/single-product/related.twig`, which the integration will render instead of PHP file.

The arguments that WooCommerce would pass to the template will be available under the `wc` variable. Here’s an example:

**views/woocommerce/single-product/related.twig**

```twig
{% for post in wc.related_products %}
    {% include 'woocommerce/teaser-product.twig' %}
{% endfor %}
```

Additionally, if you’re in the context of a product, you’ll always have the product object available in `product`:

```twig
{{ dump(product) }}
```

## Timber\Product

### Get a product post by ID

```php
new Timber\Integrations\WooCommerce\Product( 354 );
```

The same class is also available in Twig:

```twig
{% set product = Product(354) %}
```

## Product global

When working with WooCommerce, you’re maybe used to the `$product` global. The integration handles that global variable for you and makes it available under `post.product` in your Twig templates. Here’s an example for the `get_attribute` object on `$product`.

```twig
{{ post.product.get_attribute('ingredient') }}
```

So what is the difference between `post` and `product`?

- `post` is an instance of `Timber\Post`. In the case of this integration, it’s an instance of `Timber\Product`, which extends `Timber\Post`.
- `product` is an instance of [`WP_Product`](https://docs.woocommerce.com/wc-apidocs/class-WC_Product.html), which is not really a post, but an object that handles all functionalities related to a WooCommerce product. 

## WooCommerce context

The integration adds some context variables to the global Timber context. When you access the context through `Timber::get_context()`, you’ll get access to the following variables:

- `cart` – The [WooCommerce cart object](https://docs.woocommerce.com/wc-apidocs/class-WC_Cart.html).

### Template based context

In addition to the global context, you’ll have other context variables available based on the template file you’re currently working with.

- `post` – Will be an instance of `Timber\Product` when a singular product page is displayed (`is_singular('product')`). If the [shop page](https://docs.woocommerce.com/document/woocommerce-pages/) is displayed, it will be a `Timber\Post` with that page as the contents.
- `term` – Will be set when a shop taxonomy term is displayed (when [`is_product_taxonomy()`](https://docs.woocommerce.com/wc-apidocs/function-is_product_taxonomy.html) applies).
- `title` – Title to display on archive pages. Result of [woocommerce_page_title()](https://docs.woocommerce.com/wc-apidocs/function-woocommerce_page_title.html).

## Filter posts from Twig instead of filtering them in PHP

Normally when working with Timber, you’d want to filter post data before you pass it to a Twig template. With WooCommerce, this is maybe a little more complicated. You could write your own `woocommerce.php` that handles all the data. But remember, we want to interfere with WooCommerce as little as possible.

The other possibility is to filter posts when when they are used.

```twig
{% for post in fn('my_filter_posts', posts) %}
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

## Translate WooCommerce templates to Twig

When translating WooCommerce templates to Twig, you need to be extra careful. There are many hooks and filters that you should include, except for hooks that are only responsible for displaying loop wrappers. For example: `woocommerce_product_loop_start` will load `loop/loop-start.php`, which will only display the following:

```html
<ul class="products">
```

It’s up to you then, if you want to create a `loop/loop-start.twig` file. You could also directly write the opening `<ul>` tag in your Twig archive template.

## Structured Data

WooCommerce uses the hook `woocommerce_shop_loop` to set the structured data for different products. This hook is normally called from `archive.php`. You don’t have to call it manually, because it’s already called in the integration’s `ProductsIterator`.
