<?php

namespace App\Commands;

use App\Resources\ShopwarePlugin;
use App\Services\Plugins\PluginService;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class GeneratorCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate {shopwareRootPath?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate Shopware Plugin Files';

    private ?ShopwarePlugin $selectedPlugin = null;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->selectPlugin();
        $this->selectGenerator();
    }

    private function selectPlugin(): void
    {
        $pluginService = new PluginService($this->argument('shopwareRootPath') ?? 'shopware');
        $cExistingPlugins = $pluginService->getPlugins();

        $sSelectedPlugin = select(
            label: 'Select Plugin',
            options: $cExistingPlugins->map(function (ShopwarePlugin $plugin) {
                return $plugin->getTitle();
            }),
            hint: 'Select the plugin you want to generate files for',
        );

        $this->selectedPlugin = $cExistingPlugins->first(fn (ShopwarePlugin $plugin) => $plugin->getTitle() === $sSelectedPlugin);
    }

    private function selectGenerator(): void
    {
        $sSelectedGenerator = select(
            label: 'What do you want to generate?',
            options: [
                'cms-block' => 'CMS-Block',
                'cms-element' => 'CMS-Element',
            ],
            hint: 'Select the generator you want to use',
        );

        $this->call('make:'.$sSelectedGenerator, [
            'plugin' => $this->selectedPlugin->getTitle(),
            'shopwareRootPath' => $this->argument('shopwareRootPath'),
        ]);
    }
}
