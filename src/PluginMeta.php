<?php
namespace SwofShipping;

/**
 * Plugin metadata helper class
 * 
 * @package SwofShipping
 */
class PluginMeta
{
    public function __construct(string $entryFile)
    {
        $this->entryFile = self::normalizePath($entryFile);
        $this->dir = dirname($this->entryFile);
    }

    public function getEntryFile(): string
    {
        return $this->entryFile;
    }

    public function getPath($relativePath = null): string
    {
        return $this->makePath(null, $relativePath);
    }

    public function getLibsPath($relativePath = null): string
    {
        return $this->makePath('vendor', $relativePath);
    }

    public function getMigrationsPath($relativePath = null): string
    {
        return $this->makePath('migrations', $relativePath);
    }
    
    public function getAssetsPath($relativePath = null): string
    {
        return $this->makePath('assets', $relativePath);
    }

    public function getAssetUrl($asset = null): string
    {
        return plugins_url("/assets/{$asset}", $this->getEntryFile());
    }

    public function getApiUpdatesEndpoint(): string
    {
        return $this->getApiEndpoint('updates');
    }

    public function getApiStatsEndpoint(): string
    {
        return $this->getApiEndpoint('stats');
    }

    public function getLicense(): ?string
    {
        if ($this->license === false) {
            if (file_exists($file = $this->getPath('license.key'))) {
                $this->license = file_get_contents($file) ?: null;
            } else {
                $this->license = null;
            }
        }

        return $this->license;
    }

    public function getVersion(): ?string
    {
        if ($this->version === false) {
            $pluginFileAttributes = get_file_data($this->entryFile, array('Version' => 'Version'));
            $this->version = $pluginFileAttributes['Version'] ?: null;
        }

        return $this->version;
    }

    public function getPluginBasename(): string
    {
        return plugin_basename($this->getEntryFile());
    }

    private $entryFile;
    private $dir;
    private $license = false;
    private $version = false;
    private $apiEndpoint = false;

    private function makePath($location = null, $path = null): string
    {
        if (!isset($location) && !isset($path)) {
            return $this->dir;
        }

        $parts = array();

        $parts[] = $this->dir;

        if (isset($location)) {
            $parts[] = $location;
        }

        if (isset($path)) {
            $parts[] = $path;
        }

        return join('/', $parts);
    }

    private function getApiEndpoint($service): string
    {
        if ($this->apiEndpoint === false) {

            if (file_exists($config = $this->getPath('.config.php'))) {

                $config = require($config);

                if (isset($config['apiEndpoint'])) {
                    $this->apiEndpoint = $config['apiEndpoint'];
                }
            }

            if ($this->apiEndpoint === false) {
                // No remote API for SwofCommerce Shipping
                $this->apiEndpoint = '';
            }
        }

        return "{$this->apiEndpoint}/{$service}";
    }

    private static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        /** @var string $path */
        $path = preg_replace('|/+|', '/', $path);

        if ($path[1] === ':') {
            $path = ucfirst($path);
        }

        return $path;
    }
}
