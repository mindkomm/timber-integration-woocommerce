---
title: "Images"
weight: 300
menu:
  main:
    parent: "woocommerce"
---

You can display a product image like you would do it in every Timber theme, including all the image functionality you already know.

```twig
{% if post.thumbnail %}
    <div class="product-image">
        <img src="{{ post.thumbnail.src('medium') }}">
    </div>
{% endif %}
```

If you want to use your own image handling functionality and disable all WooCommerce frontend functionality related to images, you can call `disable_woocommerce_images()`, which removes the hooks used by WooCommerce. This might give you some performance improvements.

**functions.php**

```php
Timber\Integrations\WooCommerce\WooCommerce::disable_woocommerce_images();
```

It’s still recommended to set the desired image sizes for your product images in **WooCommerce** > **Settings** > **Products** > **Display**, because certain WooCommerce extensions or plugins might request these sizes.

