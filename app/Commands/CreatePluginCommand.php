<?php

namespace App\Commands;

use App\Services\Plugins\PluginService;
use LaravelZero\Framework\Commands\Command;

class CreatePluginCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:plugin {shopwareRootPath?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new Shopware plugin';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $pluginService = new PluginService($this->argument('shopwareRootPath'));

        $pluginService->getPlugins()->dd();
    }
}
