<?php
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
        /** @var \SwofShippingVendors\Composer\Autoload\ClassLoader $autoloader */
        $autoloader = include($pluginMeta->getLibsPath('autoload.php'));

        // shipping_method alias class
        $autoloader->addClassMap(array('swof_shipping_method' => $pluginMeta->getPath('shipping_method.php')));

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
