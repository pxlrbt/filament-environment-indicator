<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class EnvironmentIndicatorPlugin implements Plugin
{
    use EvaluatesClosures;

    public bool|Closure|null $visible = null;

    public bool|Closure|null $showBadge = null;

    public bool|Closure|null $showBorder = null;

    public array|Closure|null $color = null;

    public ?string $badgePosition = null;

    public bool|Closure|null $showGitBranch = null;

    public ?Closure $gitInfo = null;

    public bool|Closure|null $showDebugModeWarning = null;

    public int|Closure|null $borderWidth = 5;

    public string|Closure|null $environment = null;

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

        $plugin->color(fn () => match ($plugin->getEnvironment()) {
            'production' => Color::Red,
            'staging' => Color::Orange,
            'development' => Color::Blue,
            default => Color::Pink,
        });

        $plugin->showBadge(fn () => match ($plugin->getEnvironment()) {
            'production' => false,
            default => true,
        });

        $plugin->showBorder(fn () => match ($plugin->getEnvironment()) {
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
        $panel->renderHook($this->getBadgePosition($panel), function () {
            $html = '';

            if (! $this->evaluate($this->visible)) {
                return $html;
            }

            if ($this->evaluate($this->showDebugModeWarning) && app()->hasDebugModeEnabled()) {
                $html .= view('filament-environment-indicator::debug-mode-warning', [
                    'color' => $this->getColor(),
                    'environment' => ucfirst($this->getEnvironment()),
                    'branch' => $this->resolveGitInfo(),
                ])->render();
            }

            if ($this->evaluate($this->showBadge)) {
                $html .= view('filament-environment-indicator::badge', [
                    'color' => $this->getColor(),
                    'environment' => ucfirst($this->getEnvironment()),
                    'branch' => $this->resolveGitInfo(),
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

            $borderWidth = $this->evaluate($this->borderWidth);
            $borderColor = $this->getColor()['500'];

            return new HtmlString(<<<HTML
                <style>
                    .fi-topbar {
                        border-top: {$borderWidth}px solid {$borderColor} !important;
                    }

                    body:not(.fi-body-has-topbar) {
                        border-top: {$borderWidth}px solid {$borderColor} !important;
                    }
                </style>
            HTML);
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

    /**
     * Display custom git information in the badge. The closure receives a
     * GitInfo instance (also injectable by type) exposing branch(), tag()
     * and hash(), and returns the string to render.
     *
     * Example: ->showGitInfo(fn (GitInfo $git) => $git->tag().' - '.$git->hash())
     */
    public function showGitInfo(Closure $callback): static
    {
        $this->gitInfo = $callback;

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

    protected function getBadgePosition(Panel $panel): string
    {
        if ($this->badgePosition) {
            return $this->badgePosition;
        }

        if (! $panel->hasTopbar()) {
            return PanelsRenderHook::SIDEBAR_LOGO_AFTER;
        }

        return PanelsRenderHook::GLOBAL_SEARCH_BEFORE;
    }

    protected function getColor(): array
    {
        return $this->evaluate($this->color);
    }

    /**
     * Resolve the git string shown in the badge.
     *
     * A ->showGitInfo() closure takes precedence; otherwise ->showGitBranch()
     * falls back to the branch name (implemented on top of the same GitInfo).
     * Returns null when neither is enabled, so the view renders nothing.
     */
    protected function resolveGitInfo(): ?string
    {
        $git = new GitInfo;

        if ($this->gitInfo !== null) {
            $result = $this->evaluate($this->gitInfo, ['git' => $git], [GitInfo::class => $git]);

            return $result === null || $result === '' ? null : (string) $result;
        }

        if ($this->evaluate($this->showGitBranch)) {
            return $git->branch();
        }

        return null;
    }

    public function borderWidth(int|Closure $borderWidth = 5): static
    {
        $this->borderWidth = $borderWidth;

        return $this;
    }

    public function environment(string|Closure $environment): static
    {
        $this->environment = $environment;

        return $this;
    }

    public function getEnvironment(): string
    {
        return $this->evaluate($this->environment) ?? app()->environment();
    }
}
