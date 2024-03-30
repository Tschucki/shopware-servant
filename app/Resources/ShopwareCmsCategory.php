<?php

namespace App\Resources;

class ShopwareCmsCategory
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
}
