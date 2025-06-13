@php use Filament\Facades\Filament; @endphp
<div
    class="
        environment-indicator
        fi-badge fi-color fi-text-color-600
        dark:fi-text-color-400
    "
    style="
        --color-50: {{ $color[50] }};
        --color-300: {{ $color[300] }};
        --color-400: {{ $color[400] }};
        --color-600: {{ $color[600] }};
    "
>
    {{ $environment }}

    @isset($branch)
        <code>({{ $branch }})</code>
    @endisset
</div>
