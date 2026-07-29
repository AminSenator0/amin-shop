@php
    use App\Support\StoreSettings;
@endphp
<link rel="icon" href="{{ StoreSettings::faviconUrl() }}" type="{{ StoreSettings::faviconMimeType() }}">
<link rel="apple-touch-icon" href="{{ StoreSettings::faviconUrl() }}">
