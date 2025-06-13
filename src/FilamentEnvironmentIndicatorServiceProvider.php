<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentEnvironmentIndicatorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-environment-indicator';

    public function configurePackage(Package $package): void
    {
        FilamentAsset::register([
            Css::make('styles', __DIR__.'/../resources/css/styles.css'),
        ], static::$name);

        $package
            ->name(static::$name)
            ->hasViews();
    }
}
