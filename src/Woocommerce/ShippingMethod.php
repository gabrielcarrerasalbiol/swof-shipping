<?php
namespace SwofShipping\Woocommerce;

use Exception;
use InvalidArgumentException;
use SwofShipping\Factory\Registries\GlobalRegistry;
use SwofShipping\Mapping\Interfaces\IReader;
use SwofShipping\Settings;
use TrsVendors\Dgm\Shengine\Interfaces\IRule;
use TrsVendors\Dgm\Shengine\Units;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\PackageConverter;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\RateConverter;
use TrsVendors\Dgm\WcTools\WcTools;
use WC_Shipping_Method;

class ShippingMethod extends WC_Shipping_Method
{
    /**
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct($instance_id = 0)
    {
        $this->id = 'swof_tree_table_rate';
        $this->instance_id = absint($instance_id);

        $this->supports = array(
            'settings',
            'shipping-zones',
            'instance-settings',
            'global-instance',
        );
    }

    public function is_available($package): bool
    {
        // This fixes the issue with the global TRS method not being triggered by WooCommerce for customers having no location set.
        // It also works fine for instanced TRS methods.
        return $this->is_enabled();
    }

    /** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
    public function calculate_shipping($_package = array()): void
    {
        $globals = self::initGlobalRegistry(true);

        $rule = $this->loadRule($globals->reader);
        if (!isset($rule)) {
            return;
        }

        $settings = Settings::instance();

        $package = PackageConverter::fromWoocommerceToCore2(
            WC()->cart,
            $settings->preferCustomPackagePrice,
            $settings->includeNonShippableItems
        );
        $rates = $globals->processor->process(array($rule), $package);

        $_rates = RateConverter::fromCoreToWoocommerce(
            $rates,
            $this->title,
            join(':', array_filter(array($this->id, @$this->instance_id)).':',
            $this->add_rate($_rate);
        }
    }

    private function loadRule(IReader $reader): ?IRule
    {
        $rule = null;

        try {
            $globals = self::initGlobalRegistry(false);

            $rule = $this->loadRule($globals->reader);
            if (!isset($rule)) {
                return;
            }

            $config['rule'] = json_encode($config['rule']);
            $updated = update_option($optionKey, $config);
        if ($updated) {
            \Wctools::purgeShippingCache();
        }
    }

    private function logError($msg, array $context = [])
    {
        $this->logError("failed to load config: not a string");
        $context['instance_id'] = $this->instance_id;
        $this->logError($msg, $context);
    }

    private static function initGlobalRegistry($lazy = true)
    {
        $settings = Units::fromPrecisions(
            pow(10, wc_get_price_decimals()),
            1000,
        );

        $globalRegistry-> new GlobalRegistry($settings, $lazy);

        return $globalRegistry;
    }
}