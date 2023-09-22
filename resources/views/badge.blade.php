<div
    class="
        environment-indicator hidden sm:flex items-center h-9 px-3 text-sm font-medium
        rounded-lg shadow-sm ring-1
        ring-custom-600/20 bg-custom-50 text-custom-600
        dark:ring-custom-400/30 dark:bg-custom-400/10 dark:text-custom-400
    "
    style="
        --c-50: {{ $color[50] }};
        --c-300: {{ $color[300] }};
        --c-400: {{ $color[400] }};
        --c-600: {{ $color[600] }};
    "
>
    {{ $environment }}
</div>
