<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('firebase.messaging', function ($app) {
            $credentialsPath = storage_path('app/firebase/tekiplanet-app-firebase-adminsdk-u7g3y-7bab4b724b.json');
            
            return (new Factory)
                ->withServiceAccount($credentialsPath)
                ->createMessaging();
        });
    }

    public function provides()
    {
        return ['firebase.messaging'];
    }
} 