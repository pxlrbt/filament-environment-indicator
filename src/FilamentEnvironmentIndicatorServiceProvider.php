<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Filament\Facades\Filament;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

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
