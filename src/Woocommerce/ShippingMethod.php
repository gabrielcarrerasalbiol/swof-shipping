<?php
declare(strict_types=1);

namespace Trs\Woocommerce;

use Trs\Factory\Registries\GlobalRegistry;
use Trs\Log;
use Trs\Mapping\Interfaces\IReader;
use Trs\Settings;
use TrsVendors\Dgm\Shengine\Interfaces\IRule;
use TrsVendors\Dgm\Shengine\Units;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\PackageConverter;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\RateConverter;
use WC_Shipping_Method;

class ShippingMethod extends WC_Shipping_Method
{
    private const DEFAULT_TITLE = 'Shipping';

    public function __construct($instance_id = 0)
    {
        $this->id = 'swof_tree_table_rate';
        $this->instance_id = absint($instance_id);
        $this->method_title = 'Swof Tree Table Rate';
        $this->method_description = 'Advanced tree-based table rate shipping method.';

        $this->supports = array(
            'settings',
            'shipping-zones',
            'instance-settings',
            'global-instance',
        );

        $this->init();
    }

    public function init(): void
    {
        $this->init_form_fields();
        $this->init_instance_form_fields();

        if ($this->instance_id) {
            $this->init_instance_settings();
        } else {
            $this->init_settings();
        }

        $this->enabled = (string) $this->get_option('enabled', 'yes');
        $this->title = (string) $this->get_option('title', self::DEFAULT_TITLE);

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_shipping_' . $this->id . '_' . $this->instance_id, array($this, 'process_admin_options'));
    }

    public function admin_options(): void
    {
        if ($this->showGlobalSettingsStub()) {
            parent::admin_options();
            return;
        }

        $enabled = $this->get_option('enabled', 'yes') === 'yes';
        $label = (string) $this->get_option('label', '');
        $taxStatus = (string) $this->get_option('tax_status', 'taxable');

        echo '<tr style="display:none">';
        echo '<th scope="row">Hidden TRS fields</th>';
        echo '<td>';
        echo '<label><input type="checkbox" name="' . esc_attr($this->get_field_key('enabled')) . '"' . checked($enabled, true, false) . '> enabled</label>';
        echo '<input type="hidden" name="' . esc_attr($this->get_field_key('label')) . '" value="' . esc_attr($label) . '">';
        echo '<input type="hidden" name="' . esc_attr($this->get_field_key('tax_status')) . '" value="' . esc_attr($taxStatus) . '">';
        echo '</td>';
        echo '</tr>';

        echo '<tr><td colspan="2">';
        require dirname(__DIR__, 2) . '/tpl.php';
        echo '</td></tr>';
    }

    public function init_form_fields(): void
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this shipping method',
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => 'Method title',
                'type'        => 'text',
                'description' => 'Title shown to customers during checkout.',
                'default'     => self::DEFAULT_TITLE,
                'desc_tip'    => true,
            ),
        );
    }

    public function init_instance_form_fields(): void
    {
        $this->instance_form_fields = $this->form_fields;
    }

    public function showGlobalSettingsStub(): bool
    {
        return ((int) $this->instance_id) === 0;
    }

    public function is_available($package): bool
    {
        return $this->is_enabled();
    }

    /** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
    public function calculate_shipping($_package = array()): void
    {
        if (!$this->is_enabled()) {
            return;
        }

        $rule = $this->loadRule();
        if (!isset($rule)) {
            return;
        }

        $settings = Settings::instance();

        $package = PackageConverter::fromWoocommerceToCore2(
            $_package,
            WC()->cart,
            $settings->preferCustomPackagePrice,
            $settings->includeNonShippableItems
        );

        $globals = self::initGlobalRegistry(true);
        $rates = $globals->processor->process(array($rule), $package);

        $_rates = RateConverter::fromCoreToWoocommerce(
            $rates,
            $this->title,
            implode(':', array_filter(array($this->id, (string) $this->instance_id))) . ':'
        );

        foreach ($_rates as $_rate) {
            $this->add_rate($_rate);
        }
    }

    private function loadRule(): ?IRule
    {
        $config = get_option($this->getConfigOptionKey(), []);
        if (!is_array($config) || !array_key_exists('rule', $config)) {
            $legacyConfig = get_option('woocommerce_swof_tree_table_rate_shipping_settings', []);
            if (is_array($legacyConfig) && array_key_exists('rule', $legacyConfig)) {
                $config = $legacyConfig;
            }
        }

        if (!is_array($config) || !array_key_exists('rule', $config)) {
            return null;
        }

        if (is_string($config['rule'])) {
            $decoded = json_decode($config['rule'], true);
            if (!is_array($decoded)) {
                Log::error('failed to load config: rule is not valid json', ['instance_id' => $this->instance_id]);
                return null;
            }
            $config['rule'] = $decoded;
        }

        if (!is_array($config['rule'])) {
            return null;
        }

        try {
            $globals = self::initGlobalRegistry(false);
            return $this->readRule($globals->reader, $config);
        } catch (\Throwable $e) {
            Log::error('failed to map shipping rule', [
                'instance_id' => $this->instance_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function readRule(IReader $reader, array $config): ?IRule
    {
        $rule = $reader->read('rule', $config['rule']);
        return ($rule instanceof IRule) ? $rule : null;
    }

    private function getConfigOptionKey(): string
    {
        if ((int) $this->instance_id > 0 && method_exists($this, 'get_instance_option_key')) {
            return $this->get_instance_option_key();
        }

        return $this->get_option_key();
    }

    private static function initGlobalRegistry(bool $lazy): GlobalRegistry
    {
        $settings = Units::fromPrecisions(
            pow(10, wc_get_price_decimals()),
            1000
        );

        return new GlobalRegistry($settings, $lazy);
    }
}
