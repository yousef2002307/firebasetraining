<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('firebase', function () {
            $serviceAccount = ServiceAccount::fromJsonFile(
               storage_path('app/firebase/fir-training-e42c0-firebase-adminsdk-fbsvc-ed772f4540.json')
            );
            
            return (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDatabaseUri('https://' . config('services.firebase.project_id') . '.firebaseio.com')
                ->create();
        });
    }

    public function boot()
    {
        //
    }
}
