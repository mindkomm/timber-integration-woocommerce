# Timber Integration for WooCommerce

## 0.5.3.1 - 2020-07-29

- Fixed merge bug.

## 0.5.3 - 2020-07-29

- Added Twig function `wc_action()` that can be used instead of `action()` when calling hooks from Twig. In Twig, you would use it like this: `{% do wc_action('woocommerce_single_product_summary') %}`. This was added to fix compatibility problems when hooks were used with parameters. (See #14, thanks @pascalknecht)
- Added support for [Automatic Twig partial selection](https://github.com/mindkomm/timber-integration-woocommerce/blob/master/docs/usage.md#automatic-twig-partial-selection) when using `wc_get_template_part()`. You could only use `wc_get_template()` before. Now, you can use both.
- Fixed a bug when the `$post` global was not properly set when looping over products.
- Updated default templates.
- Updated internal repository folder structure.

## 0.5.2 - 2018-09-06

- Fixed bug that prevented the integration from working with different versions of Twig. Thanks @chrislind and @VincentLoy!
- Removed non-working support for installing the integration as a WordPress plugin. The integration can only be installed through Composer. Future versions of Timber will will drop support for installation as a plugin. That’s why drop it here, too.

## 0.5.1 - 2018-04-30

- Improved default templates.
- Improved documentation.
