<?php

use App\Providers\AppServiceProvider;
use Inertia\Laravel\ServiceProvider as InertiaServiceProvider;
use Tightenco\Ziggy\ZiggyServiceProvider;

return [
    AppServiceProvider::class,
    InertiaServiceProvider::class,
    ZiggyServiceProvider::class,
];
