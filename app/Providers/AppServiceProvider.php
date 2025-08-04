<?php


namespace App\Providers;

use App\Models\Staff;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
  public function boot()
{
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}

}
