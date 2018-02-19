---
title: "Images"
menu:
  main:
    parent: "woocommerce"
    weight: 300
---

You can display a product image like you would do it in every Timber theme.

```twig
{% if post.thumbnail %}
    <div class="product-image">
        <img src="{{ post.thumbnail.src('medium') }}">
    </div>
{% endif %}
```

If you want to use your own image handling functionality and disable all WooCommerce functionality related to images, you can call `disable_woocommerce_images()`, which removes the hooks used by WooCommerce.

Call this in the `__construct()` function of **WooCommerce_Custom.php**:

```php
Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();
```

