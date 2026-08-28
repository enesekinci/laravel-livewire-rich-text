<?php

namespace EnesEkinci\RichText;

use EnesEkinci\RichText\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RichTextServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rich-text.php', 'rich-text');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rich-text');

        Blade::component('rich-text::components.rich-text', 'rich-text');
        Blade::component('rich-text::components.rich-text', 'admin.rich-text');

        $this->registerUploadRoute();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/rich-text.php' => config_path('rich-text.php'),
            ], 'rich-text-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/rich-text'),
            ], 'rich-text-views');
        }
    }

    protected function registerUploadRoute(): void
    {
        Route::middleware(config('rich-text.middleware', ['web', 'auth']))
            ->post('__rich-text/upload', UploadController::class)
            ->name('rich-text.upload');
    }
}
