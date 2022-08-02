<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Filament\Facades\Filament;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class FilamentEnvironmentIndicatorServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-environment-indicator';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Filament::serving(fn () => FilamentEnvironmentIndicator::boot());
    }
}
