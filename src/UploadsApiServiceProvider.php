<?php

namespace Saritasa\LaravelUploads;

use Dingo\Api\Routing\Router;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Register URLs, handed by this package
 * and declare artifacts, that can be published to application (config, DB migrations, swagger definitions)
 */
class UploadsApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @param Router $apiRouter Dingo/Api router
     * @return void
     */
    public function boot(Router $apiRouter)
    {
        if ($this->app->runningInConsole()) {
            $this->declarePublishedFiles();
        }

        $this->setupDIContainer();
        $this->registerRoutes($apiRouter);
    }

    protected function setupDIContainer(): void
    {
        if ($this->app->has(AwsS3Adapter::class)) {
            return;
        }
        $this->app->bind(AwsS3Adapter::class, function () {
            return Storage::cloud()->getDriver()->getAdapter();
        });
    }

    /**
     * Register API routes and handlers for them

     * @param Router $apiRouter Dingo/Api router
     * @return void
     */
    protected function registerRoutes(Router $apiRouter)
    {
        /* @var Router $router */
        $apiRouter->version(config('api.version'), [
            'middleware' => 'api',
            'namespace' => 'Saritasa\LaravelUploads\Http\Controllers'
        ], function (Router $apiRouter) {
            $apiRouter->post('uploads/tmp', [
                'uses'  => 'UploadsApiController@getTmpUploadUrl',
                'as'    => 'uploads.tmp'
            ]);
        });
    }

    /**
     * Declare which files can be published and customized in main application:
     * config, swagger declarations, DB migrations
     */
    protected function declarePublishedFiles()
    {
        $this->declarePublishedConfig();
        $this->declarePublishedArtifacts();
        $this->declarePublishedMigrations();
    }

    private function declarePublishedConfig()
    {
        $this->publishes([
            __DIR__.'/../config/uploads.php' => config_path('uploads.php')
        ], 'config');
    }

    private function declarePublishedArtifacts()
    {
        $this->publishes([
            __DIR__ . '/../docs/API' => base_path('docs/API')
        ], 'swagger');
    }

    private function declarePublishedMigrations()
    {
//        $this->publishes([
//            __DIR__.'/../database' => database_path()
//        ], 'migrations');
    }
}
