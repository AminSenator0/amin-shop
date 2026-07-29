<?php

use App\Providers\AppServiceProvider;
use Barryvdh\DomPDF\ServiceProvider as DomPdfServiceProvider;
use Mccarlosen\LaravelMpdf\LaravelMpdfServiceProvider;

return [
    AppServiceProvider::class,
    DomPdfServiceProvider::class,
    LaravelMpdfServiceProvider::class,
];
