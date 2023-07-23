<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Closure;
use Filament\Facades\Filament;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\Configurable;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class FilamentEnvironmentIndicatorPlugin implements Plugin
{
    use EvaluatesClosures;

    public bool | Closure | null $showBadge = null;
    public bool | Closure | null $showBorder = null;

    public string | Closure | null $color = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-environment-indicator';
    }

    public function register(Panel $panel): void
    {

        $color = $this->evaluate($this->color) ?? $this->colorDefault();

        if ($this->evaluate($this->showBadge) ?? $this->showBadgeDefault()) {
            $panel->renderHook('global-search.start', fn() => View::make('filament-environment-indicator::badge', [
                'color' => $color,
                'environment' => ucfirst(app()->environment()),
            ]));
        }

        if ( $this->evaluate($this->showBorder) ?? $this->showBorderDefault()) {
            $panel->renderHook('styles.end', fn() => new HtmlString("
                <style>
                    .fi-topbar,
                    .fi-sidebar {
                        border-top: 5px solid {$color} !important;
                    }
                </style>
            "));
        }
    }

    public function showBadge(bool|Closure $showBadge = true)
    {
        $this->showBadge = $showBadge;

        return $this;
    }

    public function showBorder(bool|Closure $showBorder = true)
    {
        $this->showBorder = $showBorder;

        return $this;
    }

    public function color(string|Closure $color = '#c026d3')
    {
        $this->color = $color;

        return $this;
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function colorDefault(): string
    {
        return match (app()->environment()) {
            'production' => '#dc2626',
            'staging' => '#f97316',
            'development' => '#2563eb',
            default => '#c026d3',
        };
    }

    public function showBorderDefault(): bool
    {
        return match (app()->environment()) {
            'production', 'prod' => false,
            default => true,
        };
    }

    public function showBadgeDefault(): bool
    {
        return match (app()->environment()) {
            'production', 'prod' => false,
            default => true,
        };
    }

}
