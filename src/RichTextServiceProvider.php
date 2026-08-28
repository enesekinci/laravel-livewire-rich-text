<?php

namespace EnesEkinci\RichText;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class RichTextServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rich-text');

        Blade::component('rich-text::components.rich-text', 'rich-text');
        Blade::component('rich-text::components.rich-text', 'admin.rich-text');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/rich-text'),
            ], 'rich-text-views');
        }
    }
}
