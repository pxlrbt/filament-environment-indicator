<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class EnvironmentIndicatorPlugin implements Plugin
{
    use EvaluatesClosures;

    public bool|Closure|null $visible = null;

    public bool|Closure|null $showBadge = null;

    public bool|Closure|null $showBorder = null;

    public array|Closure|null $color = null;

    public static function make(): static
    {
        $plugin = app(static::class);

        // Defaults
        $plugin->visible(function () {
            if (($user = auth()->user()) === null) {
                return false;
            }

            if (method_exists($user, 'hasRole')) {
                return $user->hasRole('super_admin');
            }

            return true;
        });

        $plugin->color(fn () => match (app()->environment()) {
            'production' => Color::Red,
            'staging' => Color::Orange,
            'development' => Color::Blue,
            default => Color::Pink,
        });

        $plugin->showBadge(fn () => match (app()->environment()) {
            'production' => false,
            default => true,
        });

        $plugin->showBorder(fn () => match (app()->environment()) {
            'production' => false,
            default => true,
        });

        return $plugin;
    }

    public function getId(): string
    {
        return 'environment-indicator';
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook('panels::global-search.before', function () {
            if (! $this->evaluate($this->visible)) {
                return '';
            }

            if (! $this->evaluate($this->showBadge)) {
                return '';
            }

            return View::make('filament-environment-indicator::badge', [
                'color' => $this->getColor(),
                'environment' => ucfirst(app()->environment()),
            ]);
        });

        $panel->renderHook('panels::styles.after', function () {
            if (! $this->evaluate($this->visible)) {
                return '';
            }

            if (! $this->evaluate($this->showBorder)) {
                return '';
            }

            return new HtmlString("
                <style>
                    .fi-topbar,
                    .fi-sidebar {
                        border-top: 5px solid rgb({$this->getColor()['500']}) !important;
                    }

                    .fi-topbar {
                        height: calc(4rem + 5px) !important;
                    }
                </style>
            ");
        });
    }

    public function visible(bool|Closure $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function showBadge(bool|Closure $showBadge = true): static
    {
        $this->showBadge = $showBadge;

        return $this;
    }

    public function showBorder(bool|Closure $showBorder = true): static
    {
        $this->showBorder = $showBorder;

        return $this;
    }

    public function color(array|Closure $color = Color::Pink): static
    {
        $this->color = $color;

        return $this;
    }

    protected function getColor(): array
    {
        return $this->evaluate($this->color);
    }
}
