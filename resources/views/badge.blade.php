@php
    use function Filament\Support\get_color_css_variables;
@endphp
<div
    class="
        environment-indicator
        fi-badge fi-color fi-text-color-600
        dark:fi-text-color-400
    "
    style="{{ get_color_css_variables($color, [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950])  }}"
>
    {{ $environment }}

    @isset($branch)
        <code>({{ $branch }})</code>
    @endisset
</div>
