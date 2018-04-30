# Timber Integration for WooCommerce

This integration tries to make it easier to develop Timber themes for WooCommerce.

Here’s a short list of features it adds and problems it tries to solve:

- [Automatic handling of the `$product` global](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/usage.md#product-global). Whenever you loop through a list of WooCommerce products, the integration will set the `$product` global for you. This will improve compatibility for WooCommerce and its integrations.
- [Enhance Timber’s context](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/usage.md#woocommerce-context).
- [Automatic Twig partial selection](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/usage.md#automatic-twig-partial-selection)
- Provide default templates, tips and examples.

## Documentation

- [Installation](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/installation.md)
- [Usage Guide](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/usage.md)
- [Images](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/images.md)
- [Examples](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/examples.md)
- [Mini Cart](https://github.com/MINDKomm/timber-integration-woocommerce/blob/master/docs/mini-cart.md)

## Roadmap

Here are a couple features that are not yet integrated, but would be good contribution opportunities:

- [ ] Add support for tag templates.
- [ ] Add support for WooCommerce galleries. Maybe just documentation is needed?

## Todo

- Add testing.
- Write a simple function to wrap a WooCommerce template.
- Add filter to filter current menu item classes for shop page.
- Hint about adding WooCommerce support to the theme (<https://woocommerce.wordpress.com/2017/12/09/wc-3-3-will-look-great-on-all-the-themes/>).
- Check compatibility with <https://woocommerce.wordpress.com/2017/12/11/wc-3-3-image-size-improvements/>
- Documentation: Add hint about using `woocommerce_before_template_part` and `woocommerce_after_template_part` to wrap a template.
