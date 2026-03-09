<?php
/**
 * SwofCommerce Shipping Bootstrap
 * 
 * @package SwofCommerce\Shipping
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once(__DIR__ . '/src/PluginMeta.php');
require_once(__DIR__ . '/src/ClassLoader.php');
require_once(__DIR__ . '/src/Loader.php');

$pluginMeta = new \SwofShipping\PluginMeta(SWOFSHIPPING_ENTRY_FILE);

$classLoader = new \SwofShipping\ClassLoader();
$classLoader->setup($pluginMeta);

$loader = new \SwofShipping\Loader($pluginMeta);
$loader->bootstrap();
