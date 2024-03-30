<?php

namespace App\Resources;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ShopwarePlugin
{
    protected string $title;

    protected string $path;

    public function __construct(string $title, string $path)
    {
        $this->title = $title;
        $this->path = $path;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getCmsCategories(): Collection
    {
        $categoriesPath = $this->getPath().'/src/Resources/app/administration/src/module/sw-cms/blocks';
        $defaultCategories = ['commerce', 'form', 'image', 'sidebar', 'text', 'text-image', 'video'];

        if (File::exists($categoriesPath)) {
            $categories = File::directories($categoriesPath);
            $categories = collect($categories)->map(function ($category) {
                return new ShopwareCmsCategory(basename($category), $category);
            });
            collect($defaultCategories)->each(function ($category) use ($categories) {
                if (! $categories->contains('title', $category)) {
                    $categories->push($this->createNewCmsCategory($category));
                }
            });

            return $categories;
        }

        return collect();
    }

    public function createNewCmsCategory(string $title): ShopwareCmsCategory
    {
        $title = Str::kebab(Str::camel($title));
        $categoriesPath = $this->getPath().'/src/Resources/app/administration/src/module/sw-cms/blocks';
        $newCategoryPath = $categoriesPath.'/'.$title;
        if (! File::exists($newCategoryPath)) {
            File::makeDirectory(
                path: $newCategoryPath,
                recursive: true
            );
        }

        return new ShopwareCmsCategory($title, $newCategoryPath);
    }
}
