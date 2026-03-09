<?php
declare(strict_types=1);

namespace SwofShipping;

/**
 * PSR-4 Autoloader
 * 
 * @package SwofShipping
 */
class ClassLoader
{
    public function setup(PluginMeta $pluginMeta)
    {
        /** @var \TrsVendors\Composer\Autoload\ClassLoader $autoloader */
        $autoloader = include($pluginMeta->getLibsPath('autoload.php'));

        // Legacy alias classes used by Loader and WooCommerce section mapping.
        $classMap = array(
            'SwofShipping\\swof_tree_table_rate' => $pluginMeta->getPath('src/Wcocommerce/ShippingMethod.php'),
        );

        $legacyShippingMethodPath = $pluginMeta->getPath('shipping_method.php');
        if (file_exists($legacyShippingMethodPath)) {
            $classMap['swof_shipping_method'] = $legacyShippingMethodPath;
        }

        $autoloader->addClassMap($classMap);

        // Migrations
        $migrationsPath = $pluginMeta->getMigrationsPath();
        spl_autoload_register(static function($class) use ($migrationsPath) {
            if (preg_match('/SwofShipping\\\\Migration\\\\Migration((_\d+)+)$/', $class, $matches)) {
                require($migrationsPath . '/' . str_replace('_', '.', $matches[1]) . '.php');
            }
        });

        return $autoloader;
    }
}
