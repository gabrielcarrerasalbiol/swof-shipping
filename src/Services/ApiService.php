<?php
declare(strict_types=1);

namespace Trs\Services;

use Trs\Log;
use Trs\Woocommerce\Model\Shipping\ShippingMethodPersistentId;
use Trs\Woocommerce\ShippingMethodLoader;

class ApiService
{
    public const AJAX_ACTION_CONFIG_UPDATE = 'trs_config_update';
    public const AJAX_ACTION_SHIPPING_METHOD = 'trs_shipping_method';

    public function isReady(): bool
    {
        return function_exists('add_action') && function_exists('is_admin') && is_admin();
    }

    public function install(): void
    {
        add_action('wp_ajax_' . self::AJAX_ACTION_CONFIG_UPDATE, [$this, 'handleConfigUpdate']);
        add_action('wp_ajax_' . self::AJAX_ACTION_SHIPPING_METHOD, [$this, 'handleShippingMethod']);
    }

    public static function url(string $action, array $query = []): string
    {
        $query['action'] = $action;
        return admin_url('admin-ajax.php?' . http_build_query($query));
    }

    public function handleConfigUpdate(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Unauthorized', 403);
        }

        $payload = $this->readJsonPayload();
        $this->verifyNonce($payload);

        $instanceId = isset($_GET['instance_id']) ? absint((string) wp_unslash($_GET['instance_id'])) : 0;
        $config = is_array($payload['config'] ?? null) ? $payload['config'] : [];

        $optionKey = $this->getConfigOptionKey($instanceId);
        $existing = get_option($optionKey, []);
        if (!is_array($existing)) {
            $existing = [];
        }

        $updatedConfig = array_replace($existing, $this->sanitizeConfig($config));
        update_option($optionKey, $updatedConfig);

        wp_die(wp_create_nonce('update-options'));
    }

    public function handleShippingMethod(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Unauthorized', 403);
        }

        $this->verifyNonce();

        try {
            $idRaw = isset($_GET['id']) ? sanitize_text_field((string) wp_unslash($_GET['id'])) : '';
            if ($idRaw === '') {
                wp_send_json_error(['message' => 'Missing shipping method id'], 400);
            }

            $id = ShippingMethodPersistentId::unserialize($idRaw);
            $method = (new ShippingMethodLoader())->load($id);

            if (isset($_REQUEST['enable'])) {
                $enable = (int) wp_unslash((string) $_REQUEST['enable']) ? 'yes' : 'no';
                $optionKey = method_exists($method, 'get_instance_option_key') && (int) $method->get_instance_id() > 0
                    ? $method->get_instance_option_key()
                    : $method->get_option_key();

                $settings = get_option($optionKey, []);
                if (!is_array($settings)) {
                    $settings = [];
                }

                $settings['enabled'] = $enable;
                update_option($optionKey, $settings);
            }

            wp_send_json_success(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Shipping method API error', ['message' => $e->getMessage()]);
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }

    private function sanitizeConfig(array $config): array
    {
        $result = [];

        if (array_key_exists('enabled', $config)) {
            $result['enabled'] = ((bool) $config['enabled']) ? 'yes' : 'no';
        }

        if (array_key_exists('label', $config)) {
            $result['label'] = sanitize_text_field((string) $config['label']);
        }

        if (array_key_exists('tax_status', $config)) {
            $result['tax_status'] = sanitize_text_field((string) $config['tax_status']);
        }

        if (array_key_exists('rule', $config)) {
            $rule = $config['rule'];
            if (is_array($rule)) {
                $rule = wp_json_encode($rule);
            }
            $result['rule'] = (string) $rule;
        }

        return $result;
    }

    private function getConfigOptionKey(int $instanceId): string
    {
        if ($instanceId > 0) {
            return sprintf('woocommerce_swof_tree_table_rate_%d_settings', $instanceId);
        }

        return 'woocommerce_swof_tree_table_rate_shipping_settings';
    }

    private function readJsonPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function verifyNonce(array $payload = []): void
    {
        $nonce = '';

        if (isset($_REQUEST['_wpnonce'])) {
            $nonce = sanitize_text_field((string) wp_unslash($_REQUEST['_wpnonce']));
        } elseif (isset($payload['_wpnonce'])) {
            $nonce = sanitize_text_field((string) $payload['_wpnonce']);
        }

        if ($nonce === '' || !wp_verify_nonce($nonce, 'update-options')) {
            wp_die('Invalid nonce', 403);
        }
    }
}
