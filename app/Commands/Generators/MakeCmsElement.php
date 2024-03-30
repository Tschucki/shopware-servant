<?php

namespace App\Commands\Generators;

use App\Resources\ShopwarePlugin;
use App\Services\Plugins\PluginService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\text;

class MakeCmsElement extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:cms-element {shopwareRootPath} {plugin}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate a CMS-Element within a Plugin';

    private ?ShopwarePlugin $selectedPlugin = null;

    private string $elementName;

    public function handle(): int
    {
        $this->selectPlugin();
        $this->setCmsElementName();
        $this->createCmsElement($this->elementName);

        return self::SUCCESS;
    }

    private function createCmsElement(string $elementName): void
    {
        $elementPath = $this->selectedPlugin->getPath().'/src/Resources/app/administration/src/module/sw-cms/elements/'.Str::kebab(Str::camel($elementName));
        if (! File::exists($elementPath)) {
            File::makeDirectory(
                path: $elementPath,
                recursive: true
            );
        }

        $this->task('Creating component files', fn () => $this->createCmsComponentFiles($elementName, $elementPath));
        $this->task('Creating preview files', fn () => $this->createCmsPreviewFiles($elementName, $elementPath));
        $this->task('Creating config files', fn () => $this->createCmsConfigFiles($elementName, $elementPath));
        $this->task('Creating service file', fn () => $this->createCmsServiceFile($elementName, $elementPath));
        $this->task('Creating storefront file', fn () => $this->createCmsElementStorefrontFiles($elementName));
        $this->task('Registering module', fn () => $this->registerCmsModule($elementName));

        $this->newLine();
        $this->info("✅ CMS Element $elementName created successfully");
    }

    private function createCmsComponentFiles(string $elementName, string $elementPath): void
    {

        $componentPath = $elementPath.'/component';
        if (! File::exists($componentPath)) {
            File::makeDirectory(
                path: $componentPath,
                recursive: true
            );
        }

        $componentIndexJsPath = $componentPath.'/index.js';
        $componentTwigPath = $componentPath.'/sw-cms-el-'.Str::kebab(Str::camel($elementName)).'.html.twig';
        $componentScssPath = $componentPath.'/sw-cms-el-'.Str::kebab(Str::camel($elementName)).'.scss';

        $componentIndexJsStub = File::get(base_path('stubs/cms-element-stubs/component/index.stub'));
        $componentIndexJsStubVariables = [
            '$$elementName' => Str::kebab(Str::camel($elementName)),
        ];
        $componentIndexJsContent = str_replace(array_keys($componentIndexJsStubVariables), array_values($componentIndexJsStubVariables), $componentIndexJsStub);
        File::put($componentIndexJsPath, $componentIndexJsContent);

        $componentTwigStub = File::get(base_path('stubs/cms-element-stubs/component/twig.stub'));
        $componentTwigStubVariables = [
            '$$elementName' => Str::snake($elementName),
            '$$twigBlockClassName' => Str::kebab(Str::camel($elementName)),
            '$$twigBlockName' => Str::snake($elementName),
        ];
        $componentTwigContent = str_replace(array_keys($componentTwigStubVariables), array_values($componentTwigStubVariables), $componentTwigStub);
        File::put($componentTwigPath, $componentTwigContent);

        $componentScssStub = File::get(base_path('stubs/cms-element-stubs/component/scss.stub'));
        $componentScssStubVariables = [];
        $componentScssContent = str_replace(array_keys($componentScssStubVariables), array_values($componentScssStubVariables), $componentScssStub);
        File::put($componentScssPath, $componentScssContent);

    }

    private function createCmsPreviewFiles(string $elementName, string $elementPath): void
    {

        $previewPath = $elementPath.'/preview';
        if (! File::exists($previewPath)) {
            File::makeDirectory(
                path: $previewPath,
                recursive: true
            );
        }

        $previewIndexJsPath = $previewPath.'/index.js';
        $previewTwigPath = $previewPath.'/sw-cms-el-preview-'.Str::kebab(Str::camel($elementName)).'.html.twig';
        $previewScssPath = $previewPath.'/sw-cms-el-preview-'.Str::kebab(Str::camel($elementName)).'.scss';

        $previewIndexJsStub = File::get(base_path('stubs/cms-element-stubs/preview/index.stub'));
        $previewIndexJsStubVariables = [
            '$$elementName' => Str::kebab(Str::camel($elementName)),
        ];
        $componentIndexJsContent = str_replace(array_keys($previewIndexJsStubVariables), array_values($previewIndexJsStubVariables), $previewIndexJsStub);
        File::put($previewIndexJsPath, $componentIndexJsContent);

        $previewTwigStub = File::get(base_path('stubs/cms-element-stubs/preview/twig.stub'));
        $previewTwigStubVariables = [
            '$$elementName' => Str::snake($elementName),
            '$$twigBlockClassName' => Str::kebab(Str::camel($elementName)),
            '$$twigBlockName' => Str::snake($elementName),
        ];
        $previewTwigContent = str_replace(array_keys($previewTwigStubVariables), array_values($previewTwigStubVariables), $previewTwigStub);
        File::put($previewTwigPath, $previewTwigContent);

        $previewScssStub = File::get(base_path('stubs/cms-element-stubs/preview/scss.stub'));
        $previewScssStubVariables = [];
        $previewScssContent = str_replace(array_keys($previewScssStubVariables), array_values($previewScssStubVariables), $previewScssStub);
        File::put($previewScssPath, $previewScssContent);

    }

    private function createCmsConfigFiles(string $elementName, string $elementPath): void
    {

        $configPath = $elementPath.'/config';
        if (! File::exists($configPath)) {
            File::makeDirectory(
                path: $configPath,
                recursive: true
            );
        }

        $configIndexJsPath = $configPath.'/index.js';
        $configTwigPath = $configPath.'/sw-cms-el-config-'.Str::kebab(Str::camel($elementName)).'.html.twig';

        $configIndexJsStub = File::get(base_path('stubs/cms-element-stubs/config/index.stub'));
        $configIndexJsStubVariables = [
            '$$elementName' => Str::kebab(Str::camel($elementName)),
        ];
        $componentIndexJsContent = str_replace(array_keys($configIndexJsStubVariables), array_values($configIndexJsStubVariables), $configIndexJsStub);
        File::put($configIndexJsPath, $componentIndexJsContent);

        $configTwigStub = File::get(base_path('stubs/cms-element-stubs/config/twig.stub'));
        $configTwigStubVariables = [
            '$$twigBlockName' => Str::snake($elementName),
        ];
        $configTwigContent = str_replace(array_keys($configTwigStubVariables), array_values($configTwigStubVariables), $configTwigStub);
        File::put($configTwigPath, $configTwigContent);

    }

    private function createCmsServiceFile(string $elementName, string $elementPath): void
    {

        $serviceIndexJsPath = $elementPath.'/index.js';

        $serviceIndexJsStub = File::get(base_path('stubs/cms-element-stubs/index.stub'));
        $serviceIndexJsStubVariables = [
            '$$twigComponentBlockName' => Str::kebab(Str::camel($elementName)),
            '$$label' => Str::kebab(Str::camel($elementName)),
        ];
        $serviceIndexJsContent = str_replace(array_keys($serviceIndexJsStubVariables), array_values($serviceIndexJsStubVariables), $serviceIndexJsStub);
        File::put($serviceIndexJsPath, $serviceIndexJsContent);
    }

    private function createCmsElementStorefrontFiles(string $elementName): void
    {
        $storefrontFolder = $this->selectedPlugin->getPath().'/src/Resources/views/storefront/element';

        if (! File::exists($storefrontFolder)) {
            File::makeDirectory(
                path: $storefrontFolder,
                recursive: true
            );
        }

        $storefrontFilePath = $storefrontFolder.'/cms-element-'.Str::kebab(Str::camel($elementName)).'.html.twig';

        if (! File::exists($storefrontFilePath)) {
            $storefrontStub = File::get(base_path('stubs/cms-element-stubs/storefront/twig.stub'));
            $storefrontStubVariables = [
                '$$twigBlockName' => Str::snake($elementName),
                '$$blockName' => Str::kebab(Str::camel($elementName)),
            ];
            $storefrontContent = str_replace(array_keys($storefrontStubVariables), array_values($storefrontStubVariables), $storefrontStub);
            File::put($storefrontFilePath, $storefrontContent);
        }
    }

    private function registerCmsModule(string $elementName): void
    {
        $mainJsFolder = $this->selectedPlugin->getPath().'/src/Resources/app/administration/src';
        $mainJsPath = $mainJsFolder.'/main.js';
        $sElementName = Str::kebab(Str::camel($elementName));

        if (! File::exists($mainJsFolder)) {
            File::makeDirectory(
                path: $mainJsFolder,
                recursive: true
            );
        }

        if (! File::exists($mainJsPath)) {
            File::put(
                path: $mainJsPath,
                contents: ''
            );
        }

        $jsContent = <<<JS
import './module/sw-cms/elements/$sElementName';

JS;

        File::append($mainJsPath, $jsContent);
    }

    private function setCmsElementName(): void
    {
        $sElementName = text(
            label: 'Element Name',
            placeholder: 'awesome_new_element',
            default: '',
            hint: 'Please enter the name of the new element you want to create'
        );
        $this->elementName = Str::snake($sElementName);
    }

    private function selectPlugin(): void
    {
        $pluginService = new PluginService($this->argument('shopwareRootPath'));
        $cExistingPlugins = $pluginService->getPlugins();
        if ($cExistingPlugins->count() === 0) {
            $this->error('No plugins found in the provided path');
            exit(1);
        }
        $this->selectedPlugin = $cExistingPlugins->first(fn (ShopwarePlugin $plugin) => $plugin->getTitle() === $this->argument('plugin'));
    }
}
