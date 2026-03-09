<?php
namespace SwofShipping;

class ServiceInstaller
{
    public function installIfReady($service)
    {
        if (method_exists($service, 'isReady') && $service->isReady()) {
            if (method_exists($service, 'install')) {
                $service->install();
            }
        }
    }
}
