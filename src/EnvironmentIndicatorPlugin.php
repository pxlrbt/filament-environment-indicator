<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Throwable;

class EnvironmentIndicatorPlugin implements Plugin
{
    use EvaluatesClosures;

    public bool|Closure|null $visible = null;

    public bool|Closure|null $showBadge = null;

    public bool|Closure|null $showBorder = null;

    public array|Closure|null $color = null;

    public ?string $badgePosition = null;

    public bool|Closure|null $showGitBranch = null;

    public bool|Closure|null $showDebugModeWarning = null;

    public int|Closure|null $borderWidth = 5;

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
        $panel->renderHook($this->getBadgePosition(), function () {
            $html = '';

            if (! $this->evaluate($this->visible)) {
                return $html;
            }

            if ($this->evaluate($this->showDebugModeWarning) && app()->hasDebugModeEnabled()) {
                $html .= view('filament-environment-indicator::debug-mode-warning', [
                    'color' => $this->getColor(),
                    'environment' => ucfirst(app()->environment()),
                    'branch' => $this->getGitBranch(),
                ])->render();
            }

            if ($this->evaluate($this->showBadge)) {
                $html .= view('filament-environment-indicator::badge', [
                    'color' => $this->getColor(),
                    'environment' => ucfirst(app()->environment()),
                    'branch' => $this->getGitBranch(),
                ])->render();
            }

            return $html;
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
                    .fi-topbar {
                        border-top: {$this->evaluate($this->borderWidth)}px solid {$this->getColor()['500']} !important;
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

    public function showGitBranch(bool|Closure $showGitBranch = true): static
    {
        $this->showGitBranch = $showGitBranch;

        return $this;
    }

    public function showDebugModeWarning(bool|Closure $showWarning = true): static
    {
        $this->showDebugModeWarning = $showWarning;

        return $this;
    }

    public function showDebugModeWarningInProduction(): static
    {
        $this->showDebugModeWarning(fn () => app()->isProduction());

        return $this;
    }

    public function color(array|Closure $color = Color::Pink): static
    {
        $this->color = $color;

        return $this;
    }

    public function badgePosition(string $position): static
    {
        $this->badgePosition = $position;

        return $this;
    }

    protected function getBadgePosition(): string
    {
        return $this->badgePosition ?: PanelsRenderHook::GLOBAL_SEARCH_BEFORE;
    }

    protected function getColor(): array
    {
        return $this->evaluate($this->color);
    }

    protected function getGitBranch(): ?string
    {
        if (! $this->evaluate($this->showGitBranch)) {
            return null;
        }

        try {
            return trim(exec('git branch --show-current'));
        } catch (Throwable $th) {
            return null;
        }
    }

    public function borderWidth(int|Closure $borderWidth = 5): static
    {
        $this->borderWidth = $borderWidth;

        return $this;
    }
}
