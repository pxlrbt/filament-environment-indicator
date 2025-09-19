@php
    use Filament\Support\Colors\Color;
    use Filament\Support\Icons\Heroicon;
    use function Filament\Support\get_color_css_variables;
    use function Filament\Support\generate_icon_html;
@endphp
<div
    class="
        debug-mode-indicator
        fi-badge fi-color fi-text-color-600
        dark:fi-text-color-400
    "
    style="{{ get_color_css_variables(Color::Red, [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950])  }}"
>
    {{ generate_icon_html(Heroicon::OutlinedExclamationTriangle) }}
    Debug Mode
</div>
