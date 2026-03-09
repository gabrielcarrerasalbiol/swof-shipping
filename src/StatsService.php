<?php
namespace SwofShipping;

use SwofShipping\PluginMeta;

class StatsService
{
    private $pluginMeta;

    public function __construct(PluginMeta $pluginMeta)
    {
        $this->pluginMeta = $pluginMeta;
    }

    public function isReady()
    {
        return false;
    }

    public function install()
    {
        // Stub implementation
    }
}
