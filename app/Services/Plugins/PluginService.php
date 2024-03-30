<?php

namespace App\Services\Plugins;

use App\Resources\ShopwarePlugin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class PluginService
{
    private string $sPluginsPath;

    private string|false $sShopwareRootPath;

    private string $sStaticPluginsPath;

    private array $aDirScanExcludes;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(?string $sShopwareRootPath = null, array $aDirScanExcludes = [])
    {
        $this->sShopwareRootPath = $sShopwareRootPath ?? getcwd();
        if ($this->sShopwareRootPath === false) {
            throw new InvalidArgumentException('Could not determine Shopware root path');
        }
        $this->sPluginsPath = $this->sShopwareRootPath.'/custom/plugins';
        $this->sStaticPluginsPath = $this->sShopwareRootPath.'/custom/static-plugins';
        $this->aDirScanExcludes = $aDirScanExcludes;
    }

    public function getPlugins(): Collection
    {
        $plugins = new Collection();

        if (File::exists($this->sPluginsPath)) {
            $aPluginDirs = File::directories($this->sPluginsPath);
            foreach ($aPluginDirs as $pluginDir) {
                if (! in_array(basename($pluginDir), $this->aDirScanExcludes, true)) {
                    $plugins->push(new ShopwarePlugin(basename($pluginDir), $pluginDir));
                }
            }
        }

        if (File::exists($this->sStaticPluginsPath)) {
            $aStaticPluginDirs = File::directories($this->sStaticPluginsPath);
            foreach ($aStaticPluginDirs as $staticPluginDir) {
                if (! in_array(basename($staticPluginDir), $this->aDirScanExcludes, true)) {
                    $plugins->push(new ShopwarePlugin(basename($staticPluginDir), $staticPluginDir));
                }
            }
        }

        return $plugins;
    }
}
