<?php
namespace Trs;

class UpdateService
{
    private $pluginMeta;

    public function __construct($pluginMeta)
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
