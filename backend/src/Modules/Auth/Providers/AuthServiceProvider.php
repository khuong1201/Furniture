<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\Auth\Infrastructure\Repositories\EloquentAuthRepository;

class AuthServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Auth';
    protected string $nameLower = 'auth';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();

        // Load migrations
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Load jwt.php config if exists
        $configPath = module_path($this->name, 'config/jwt.php');
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'jwt');
        }

        // Load routes
        $routesPath = module_path($this->name, 'routes/api.php');
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        // Bind AuthService **safely** after all providers loaded
        // $this->app->booted(function () {
        //     $this->app->singleton(AuthService::class, function ($app) {
        //         // $app->make('cache') sẽ luôn tồn tại
        //         return new AuthService($app->make('cache'));
        //     });
        // });
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Bind repository
        $this->app->bind(AuthRepositoryInterface::class, EloquentAuthRepository::class);
        $this->app->bind(
            \Modules\Auth\Domain\Repositories\RefreshTokenRepositoryInterface::class,
            \Modules\Auth\Infrastructure\Repositories\EloquentRefreshTokenRepository::class
        );
        $this->app->singleton(AuthService::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Merge jwt config
        $configPath = base_path('Modules/Auth/config/jwt.php');
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'jwt');
        }
    }

    /**
     * Register commands
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () { ... });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config files
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path', 'config'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $configKey = str_replace([$configPath . DIRECTORY_SEPARATOR, '.php'], ['', ''], $file->getPathname());
                    $configKey = $this->nameLower . '.' . strtolower(str_replace(DIRECTORY_SEPARATOR, '.', $configKey));

                    $this->publishes([$file->getPathname() => config_path(basename($file))], 'config');
                    $this->mergeConfigFrom($file->getPathname(), $configKey);
                }
            }
        }
    }

    /**
     * Register views
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace') . '\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->nameLower)) {
                $paths[] = $path . '/modules/' . $this->nameLower;
            }
        }
        return $paths;
    }
}
