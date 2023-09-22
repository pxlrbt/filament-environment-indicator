<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentEnvironmentIndicatorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-environment-indicator';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }
}
