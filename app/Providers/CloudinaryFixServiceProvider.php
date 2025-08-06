<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cloudinary\Cloudinary;

class CloudinaryFixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Cloudinary::class, function () {
            $url = env('CLOUDINARY_URL');
            if (!$url) {
                throw new \Exception('CLOUDINARY_URL not defined in .env');
            }

            return new Cloudinary($url);
        });
    }
}
