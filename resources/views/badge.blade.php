@php
    use function Filament\Support\get_color_css_variables;
@endphp
<div
    class="
        environment-indicator
        fi-badge fi-color fi-text-color-600
        dark:fi-text-color-400
    "
    style="{{ get_color_css_variables($color, [50, 300, 400, 600])  }}"
>
    {{ $environment }}

    @isset($branch)
        <code>({{ $branch }})</code>
    @endisset
</div>
