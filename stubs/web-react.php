<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'appName'        => config('app.name', 'Laravel'),
        'laravelVersion' => app()->version(),
        'phpVersion'     => PHP_VERSION,
    ]);
});
