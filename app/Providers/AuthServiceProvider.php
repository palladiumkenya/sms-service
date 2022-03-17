<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Apps;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $header = $request->header('api_token');
            $autheniticated= Apps::where('api_key', 'LIKE', $header)->count();
        if($autheniticated=='0')
        {
            return null;

        }else
        {
            return Apps::where('api_key', $request->input('api_token'))->first();

        }
            
        });
    }
}
