<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    echo Inspiring::quote();
})->purpose('Display an inspiring quote');
