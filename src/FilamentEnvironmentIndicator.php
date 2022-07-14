<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Closure;
use Filament\Facades\Filament;
use Filament\Support\Concerns\Configurable;
use Illuminate\Support\HtmlString;

class FilamentEnvironmentIndicator
{
    use Configurable;

    public Closure $color;

    public Closure $visible;

    public Closure $showBadge;

    public Closure $showBorder;

    protected function setUp(): void
    {
        $this->color = Closure::fromCallable([$this, 'color']);
        $this->visible = Closure::fromCallable([$this, 'visible']);
        $this->showBadge = Closure::fromCallable([$this, 'showBadge']);
        $this->showBorder = Closure::fromCallable([$this, 'showBorder']);
    }

    public static function boot(): void
    {
        $self = new static();
        $self->configure();

        if (! ($self->visible)()) {
            return;
        }

        $self->injectBadge();
        $self->injectBorderStyle();
    }

    public function injectBadge(): void
    {
        if (! ($this->showBadge)()) {
            return;
        }

        $color = ($this->color)();

        if ($color === null) {
            return;
        }

        Filament::registerRenderHook('global-search.start', fn () => new HtmlString('
            <div
                class="flex items-center h-10 rounded-lg px-3 text-sm font-medium"
                style="background-color: ' . $color . '; margin-right: 1rem"
            >'
                . ucfirst(app()->environment()) .
            '</div>
        '));
    }

    public function injectBorderStyle(): void
    {
        if (! ($this->showBorder)()) {
            return;
        }

        $color = ($this->color)();

        if ($color === null) {
            return;
        }

        Filament::registerRenderHook('styles.end', fn () => new HtmlString("
            <style>
                .filament-app-layout,
                .filament-sidebar {
                    border-top: 5px solid {$color} !important;
                }
            </style>
        "));
    }

    public function visible(): bool
    {
        if (($user = auth()->user()) === null) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->has('super_admin');
        }

        return true;
    }

    public function showBorder(): bool
    {
        return match (app()->environment()) {
            'production', 'prod' => false,
            default => true,
        };
    }

    public function showBadge(): bool
    {
        return match (app()->environment()) {
            'production', 'prod' => false,
            default => true,
        };
    }

    public function color(): string
    {
        return match (app()->environment()) {
            'production' => '#dc2626',
            'staging' => '#f97316',
            'development' => '#2563eb',
            default => '#c026d3',
        };
    }
}
