<?php

namespace App\Commands\Generators;

use App\Resources\ShopwareCmsCategory;
use App\Resources\ShopwarePlugin;
use App\Services\Plugins\PluginService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeCmsBlock extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:cms-block {shopwareRootPath} {plugin}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate a CMS-Block within a Plugin';

    private ?ShopwarePlugin $selectedPlugin = null;

    private ShopwareCmsCategory $selectedCmsCategory;

    private string $blockName;

    public function handle(): int
    {
        $this->selectPlugin();
        $this->selectCmsCategory();
        $this->setCmsBlockName();
        $this->createCmsBlock($this->blockName, $this->selectedCmsCategory);

        return self::SUCCESS;
    }

    private function createCmsBlock(string $blockName, ShopwareCmsCategory $category): void
    {
        $blockPath = $category->getPath().'/'.Str::kebab(Str::camel($blockName));
        if (! File::exists($blockPath)) {
            File::makeDirectory(
                path: $blockPath,
                recursive: true
            );
        }
        // Create CmsComponent Files

        $this->createCmsComponentFiles($blockName, $blockPath);
        // Create CmsPreview Files
        $this->createCmsPreviewFiles($blockName, $blockPath);
        // Create CmsService JS-File
        $this->createCmsServiceFile($blockName, $blockPath);
        // Create CmsBlock Files For Storefront
        $this->createCmsBlockStorefrontFiles($blockName);
        // Register CMS Module in Plugin
        $this->registerCmsModule($blockName, $category);

        $this->newLine();
        $this->newLine();
        $this->info("✅ CMS Block $blockName created successfully");
    }

    private function createCmsComponentFiles(string $blockName, string $blockPath): void
    {
        $this->info("\nCreating CMS Component Files");
        $bar = $this->output->createProgressBar(3);

        $componentPath = $blockPath.'/component';
        if (! File::exists($componentPath)) {
            File::makeDirectory(
                path: $componentPath,
                recursive: true
            );
        }

        $componentIndexJsPath = $componentPath.'/index.js';
        $componentTwigPath = $componentPath.'/sw-cms-block-'.Str::kebab(Str::camel($blockName)).'.html.twig';
        $componentScssPath = $componentPath.'/sw-cms-block-'.Str::kebab(Str::camel($blockName)).'.scss';

        //index.js
        $componentIndexJsStub = File::get(base_path('stubs/cms-block-stubs/component/index.stub'));
        $componentIndexJsStubVariables = [
            '$$blockName' => Str::kebab(Str::camel($blockName)),
        ];
        $componentIndexJsContent = str_replace(array_keys($componentIndexJsStubVariables), array_values($componentIndexJsStubVariables), $componentIndexJsStub);
        File::put($componentIndexJsPath, $componentIndexJsContent);
        $bar->advance();

        //sw-cms-block-$blockName.html.twig
        $componentTwigStub = File::get(base_path('stubs/cms-block-stubs/component/twig.stub'));
        $componentTwigStubVariables = [
            '$$blockName' => Str::snake($blockName),
        ];
        $componentTwigContent = str_replace(array_keys($componentTwigStubVariables), array_values($componentTwigStubVariables), $componentTwigStub);
        File::put($componentTwigPath, $componentTwigContent);
        $bar->advance();

        //sw-cms-block-$blockName.scss
        $componentScssStub = File::get(base_path('stubs/cms-block-stubs/component/scss.stub'));
        $componentScssStubVariables = [
            '$$blockName' => Str::snake($blockName),
        ];
        $componentScssContent = str_replace(array_keys($componentScssStubVariables), array_values($componentScssStubVariables), $componentScssStub);
        File::put($componentScssPath, $componentScssContent);
        $bar->advance();

        $bar->finish();
    }

    private function createCmsPreviewFiles(string $blockName, string $blockPath): void
    {
        $this->info("\nCreating CMS Preview Files");
        $bar = $this->output->createProgressBar(3);

        $previewPath = $blockPath.'/preview';
        if (! File::exists($previewPath)) {
            File::makeDirectory(
                path: $previewPath,
                recursive: true
            );
        }

        $previewIndexJsPath = $previewPath.'/index.js';
        $previewTwigPath = $previewPath.'/sw-cms-preview-'.Str::kebab(Str::camel($blockName)).'.html.twig';
        $previewScssPath = $previewPath.'/sw-cms-preview-'.Str::kebab(Str::camel($blockName)).'.scss';

        //index.js
        $previewIndexJsStub = File::get(base_path('stubs/cms-block-stubs/preview/index.stub'));
        $previewIndexJsStubVariables = [
            '$$blockName' => Str::kebab(Str::camel($blockName)),
        ];
        $componentIndexJsContent = str_replace(array_keys($previewIndexJsStubVariables), array_values($previewIndexJsStubVariables), $previewIndexJsStub);
        File::put($previewIndexJsPath, $componentIndexJsContent);
        $bar->advance();

        //sw-cms-preview-$blockName.html.twig
        $previewTwigStub = File::get(base_path('stubs/cms-block-stubs/preview/twig.stub'));
        $previewTwigStubVariables = [
            '$$blockName' => Str::title($blockName),
            '$$twigBlockName' => Str::snake($blockName),
        ];
        $previewTwigContent = str_replace(array_keys($previewTwigStubVariables), array_values($previewTwigStubVariables), $previewTwigStub);
        File::put($previewTwigPath, $previewTwigContent);
        $bar->advance();

        //sw-cms-preview-$blockName.scss
        $previewScssStub = File::get(base_path('stubs/cms-block-stubs/preview/scss.stub'));
        $previewScssStubVariables = [
            '$$blockName' => Str::snake($blockName),
        ];
        $previewScssContent = str_replace(array_keys($previewScssStubVariables), array_values($previewScssStubVariables), $previewScssStub);
        File::put($previewScssPath, $previewScssContent);
        $bar->advance();

        $bar->finish();
    }

    private function createCmsServiceFile(string $blockName, string $blockPath): void
    {
        $this->info("\nCreating CMS Service File");
        $bar = $this->output->createProgressBar(1);

        $serviceIndexJsPath = $blockPath.'/index.js';

        $serviceIndexJsStub = File::get(base_path('stubs/cms-block-stubs/index.stub'));
        $serviceIndexJsStubVariables = [
            '$$componentBlockName' => Str::kebab(Str::camel($blockName)),
            '$$label' => Str::kebab(Str::camel($blockName)),
            '$$categoryName' => Str::kebab(Str::camel($this->selectedCmsCategory->getTitle())),
        ];
        $serviceIndexJsContent = str_replace(array_keys($serviceIndexJsStubVariables), array_values($serviceIndexJsStubVariables), $serviceIndexJsStub);
        File::put($serviceIndexJsPath, $serviceIndexJsContent);
        $bar->advance();
        $bar->finish();
    }

    private function createCmsBlockStorefrontFiles(string $blockName): void
    {
        $this->info("\nCreating Storefront File");
        $bar = $this->output->createProgressBar(1);
        $storefrontFolder = $this->selectedPlugin->getPath().'/src/Resources/views/storefront/block';

        if (! File::exists($storefrontFolder)) {
            File::makeDirectory(
                path: $storefrontFolder,
                recursive: true
            );
        }

        $storefrontFilePath = $storefrontFolder.'/cms-block-'.Str::kebab(Str::camel($blockName)).'.html.twig';

        if (! File::exists($storefrontFilePath)) {
            $storefrontStub = File::get(base_path('stubs/cms-block-stubs/storefront/twig.stub'));
            $storefrontStubVariables = [
                '$$twigBlockName' => Str::snake($blockName),
            ];
            $storefrontContent = str_replace(array_keys($storefrontStubVariables), array_values($storefrontStubVariables), $storefrontStub);
            File::put($storefrontFilePath, $storefrontContent);
        }
        $bar->advance();
        $bar->finish();
    }

    private function registerCmsModule(string $blockName, ShopwareCmsCategory $category): void
    {
        $this->info("\nRegistering CMS Module in Plugin");
        $bar = $this->output->createProgressBar(1);
        $mainJsFolder = $this->selectedPlugin->getPath().'/src/Resources/app/administration/src';
        $mainJsPath = $mainJsFolder.'/main.js';
        $sBlockName = Str::kebab(Str::camel($blockName));

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

        $sCategoryName = Str::kebab(Str::camel($category->getTitle()));
        $jsContent = <<<JS
import './module/sw-cms/blocks/$sCategoryName/$sBlockName';

JS;

        File::append($mainJsPath, $jsContent);
        $bar->advance();
        $bar->finish();
    }

    private function setCmsBlockName(): void
    {
        $sBlockName = text(
            label: 'Block Name',
            placeholder: 'awesome_new_block',
            default: '',
            hint: 'Please enter the name of the new block you want to create'
        );
        $this->blockName = Str::snake($sBlockName);
    }

    private function selectCmsCategory(): void
    {
        $sNewCategoryTitle = 'Create a new category';
        $sSelectedCategory = select(
            label: 'Select CMS Category',
            options: [$sNewCategoryTitle, ...$this->selectedPlugin->getCmsCategories()->map(function (ShopwareCmsCategory $category) {
                return $category->getTitle();
            })->toArray()],
            hint: 'Select the CMS Category you want to generate a CMS Block for');

        if ($sSelectedCategory === $sNewCategoryTitle) {
            $title = text(
                label: 'New Category Name',
                placeholder: 'awesome-new-category',
                default: '',
                hint: 'Please enter the name of the new category you want to create'
            );
            $this->selectedCmsCategory = $this->selectedPlugin->createNewCmsCategory($title);

            return;
        }

        $this->selectedCmsCategory = $this->selectedPlugin->getCmsCategories()->first(fn (ShopwareCmsCategory $category) => $category->getTitle() === $sSelectedCategory);
    }

    private function selectPlugin(): void
    {
        $pluginService = new PluginService($this->argument('shopwareRootPath') ?? 'shopware');
        $cExistingPlugins = $pluginService->getPlugins();
        $this->selectedPlugin = $cExistingPlugins->first(fn (ShopwarePlugin $plugin) => $plugin->getTitle() === $this->argument('plugin'));
    }
}
