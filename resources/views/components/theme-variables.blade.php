@php
    use App\Support\StoreSettings;

    $palette = StoreSettings::themeCssVariables();
@endphp
<style>
    :root {
        @foreach ($palette as $name => $hex)
        --shop-{{ $name }}: {{ $hex }};
        --shop-{{ $name }}-rgb: {{ StoreSettings::hexToRgb($hex) }};
        @endforeach
    }
</style>
<script>
    window.__STORE_THEME_DERIVATION__ = @json(StoreSettings::derivationRules());
</script>
