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

Then make sure that you require the autoload file. If you install the integration as a dependency of your theme, then this would be in your **functions.php**:

```php
// Load Composer dependencies
require_once 'vendor/autoload.php';
```

You can’t install the integration as a WordPress plugin.
