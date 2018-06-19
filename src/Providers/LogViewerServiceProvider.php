<?php namespace Genetsis\LogViewer\Providers;

use Genetsis\LogViewer\LogViewer;
use Illuminate\Support\ServiceProvider;

class LogViewerServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        \AdminMenu::add('logviewer::partials.logviewer_menu');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->handleRoutes();
        $this->handleViews();
        $this->handleCommands();

        $this->app->singleton('Logviewer', function(){
            return new LogViewer();
        });
    }


    private function handleRoutes() {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }

    private function handleViews() {
        $this->loadViewsFrom(__DIR__.'/../../views', 'logviewer');
    }

    private function handleCommands() {
//        if ($this->app->runningInConsole()) {
//            $this->commands([
//                InstallPromotions::class
//            ]);
//        }
    }
}
