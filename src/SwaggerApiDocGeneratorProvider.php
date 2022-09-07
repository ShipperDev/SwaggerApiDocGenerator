<?php

namespace ShipperDev\SwaggerApiDocGenerator;

use Illuminate\Support\ServiceProvider;

class SwaggerApiDocGeneratorProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'api_doc_generator');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            GeneratorCommand::class
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config.php' => config_path('api_doc_generator.php'),
            ], 'config');
        }
    }
}
