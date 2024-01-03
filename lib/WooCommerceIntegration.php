<?php

namespace Timber\Integrations\WooCommerce;

use Timber\Integration\IntegrationInterface;

class WooCommerceIntegration implements IntegrationInterface {
	public function should_init(): bool
    {
        return class_exists('WooCommerce');
    }

    public function init(): void
    {
        WooCommerce::init();
    }
}
