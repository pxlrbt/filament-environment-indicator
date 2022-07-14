<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Filament\Facades\Filament;
use Filament\PluginServiceProvider;

class FilamentEnvironmentIndicatorServiceProvider extends PluginServiceProvider
{
    public static string $name = 'Filament Environment Indicator';

    public function packageBooted(): void
    {
        Filament::serving(fn () => FilamentEnvironmentIndicator::boot());
    }
}
