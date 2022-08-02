<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Filament\Facades\Filament;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class FilamentEnvironmentIndicatorServiceProvider extends PluginServiceProvider
{
    public static string $name = 'Filament Environment Indicator';

	public function configurePackage(Package $package): void
	{
		$package
			->name('filament-environment-indicator')
			->hasViews();
	}


	public function packageBooted(): void
    {
        Filament::serving(fn () => FilamentEnvironmentIndicator::boot());
    }
}
