<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Closure;
use Filament\Facades\Filament;
use Filament\Support\Concerns\Configurable;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\View;

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

		if (!($self->visible)()) {
			return;
		}

		$self->injectBadge();
		$self->injectBorderStyle();
	}

	public function injectBadge(): void
	{
		if (!($this->showBadge)()) {
			return;
		}

		$color = ($this->color)();

		if ($color === null) {
			return;
		}

		$view = View::make('filament-environment-indicator::badge', [
			"color" => $color,
			"environment" => ucfirst(app()->environment())
		])->render();

		Filament::registerRenderHook('global-search.start', fn() => new HtmlString($view));
	}

	public function injectBorderStyle(): void
	{
		if (!($this->showBorder)()) {
			return;
		}

		$color = ($this->color)();

		if ($color === null) {
			return;
		}

		Filament::registerRenderHook('styles.end', fn() => new HtmlString("
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
			return $user->hasRole('super_admin');
		}

		return true;
	}

	public function showBorder(): bool
	{
		return Arr::get(config('filament-environment-indicator.showBorder'), app()->environment()) ??
			config('filament-environment-indicator.showBorder.default');
	}

	public function showBadge(): bool
	{
		return Arr::get(config('filament-environment-indicator.showBadge'), app()->environment()) ??
			config('filament-environment-indicator.showBadge.default');

	}

	public function color(): string
	{
		return Arr::get(config('filament-environment-indicator.colors'), app()->environment()) ??
			config('filament-environment-indicator.colors.default');
	}
}
