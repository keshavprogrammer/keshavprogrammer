<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
        $this->mapAdminpanelRoutes();
        $this->mapAgencypanelRoutes();
        $this->mapAgentpanelRoutes();
        $this->mapSubAgentpanelRoutes();
        $this->mapPartnerPanelpanelRoutes();
        $this->mapPartnerUserPanelpanelRoutes();
        
        

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "Adminpanel" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminpanelRoutes()
    {
        Route::prefix('adminpanel')
            ->middleware('web')
            ->namespace($this->namespace . '\adminpanel')
            ->as('adminpanel.')
            ->group(base_path('routes/adminpanel.php'));
    }
    
    /**
     * Define the "Agencypanel" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAgencypanelRoutes()
    {
        Route::prefix('agencypanel')
            ->middleware('web')
            ->namespace($this->namespace . '\agencypanel')
            ->as('agencypanel.')
            ->group(base_path('routes/agencypanel.php'));
    }
    protected function mapAgentpanelRoutes()
    {
        Route::prefix('agentpanel')
            ->middleware('web')
            ->namespace($this->namespace . '\agentpanel')
            ->as('agentpanel.')
            ->group(base_path('routes/agentpanel.php'));
    }
    
    protected function mapSubAgentpanelRoutes()
    {
        Route::prefix('subagentpanel')
            ->middleware('web')
            ->namespace($this->namespace . '\subagentpanel')
            ->as('subagentpanel.')
            ->group(base_path('routes/subagentpanel.php'));
    }
    protected function mapPartnerPanelpanelRoutes()
    {
        Route::prefix('partnerpanel')
            ->middleware('web')
            ->namespace($this->namespace . '\partnerpanel')
            ->as('partnerpanel.')
            ->group(base_path('routes/partnerpanel.php'));
    }
    
    protected function mapPartnerUserPanelpanelRoutes()
    {
        Route::prefix('partneruserpanel')
            ->middleware('web')
            ->namespace($this->namespace . '\partneruserpanel')
            ->as('partneruserpanel.')
            ->group(base_path('routes/partneruserpanel.php'));
    }
    
    
}
