<?php

namespace App\Providers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;


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
    public function boot(): void
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');

        if (!$cloudinaryUrl) {
            throw new \Exception('CLOUDINARY_URL is not defined in .env');
        }

        // Esta es la clase base del SDK, no la facade de Laravel
        new Cloudinary($cloudinaryUrl); // Esto registra la configuración global
    }
}
