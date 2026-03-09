<?php
namespace Trs;

use Trs\PluginMeta;

class IisCompatService
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
