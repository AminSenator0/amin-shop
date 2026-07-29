@php
    $storeName = (string) config('app.name', 'فروشگاه');
    $theme = [
        'primary' => '#16827D',
        'primary_rgb' => '22 130 125',
        'accent' => '#2EC4B6',
        'accent_rgb' => '46 196 182',
        'background' => '#F4FBFB',
        'surface' => '#FFFFFF',
        'text' => '#134E4A',
        'muted' => '#5F7A78',
        'border' => '#D5E8E6',
        'hero_to' => '#2EC4B6',
    ];
    $faviconHref = null;

    try {
        $storeName = (string) (\App\Support\StoreSettings::get('store_name', $storeName) ?: $storeName);
        $vars = \App\Support\StoreSettings::themeCssVariables();
        $theme['primary'] = $vars['primary'] ?? $theme['primary'];
        $theme['accent'] = $vars['accent'] ?? $theme['accent'];
        $theme['background'] = $vars['background'] ?? $theme['background'];
        $theme['surface'] = $vars['surface'] ?? $theme['surface'];
        $theme['text'] = $vars['text'] ?? $theme['text'];
        $theme['muted'] = $vars['muted'] ?? $theme['muted'];
        $theme['border'] = $vars['border'] ?? $theme['border'];
        $theme['hero_to'] = $vars['hero-to'] ?? $vars['accent'] ?? $theme['hero_to'];
        $theme['primary_rgb'] = \App\Support\StoreSettings::hexToRgb($theme['primary']);
        $theme['accent_rgb'] = \App\Support\StoreSettings::hexToRgb($theme['accent']);
        $faviconHref = \App\Support\StoreSettings::faviconUrl();
    } catch (\Throwable) {
        // صفحات خطا باید حتی وقتی دیتابیس/تنظیمات در دسترس نیست هم رندر شوند
    }

    $code = trim($__env->yieldContent('code'));
    $title = trim($__env->yieldContent('title'));
    $message = trim($__env->yieldContent('message'));
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — {{ $storeName }}</title>
    @if($faviconHref)
        <link rel="icon" href="{{ $faviconHref }}">
    @endif
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --shop-primary: {{ $theme['primary'] }};
            --shop-primary-rgb: {{ $theme['primary_rgb'] }};
            --shop-accent: {{ $theme['accent'] }};
            --shop-accent-rgb: {{ $theme['accent_rgb'] }};
            --shop-background: {{ $theme['background'] }};
            --shop-surface: {{ $theme['surface'] }};
            --shop-text: {{ $theme['text'] }};
            --shop-muted: {{ $theme['muted'] }};
            --shop-border: {{ $theme['border'] }};
            --shop-hero-to: {{ $theme['hero_to'] }};
        }
    </style>
</head>
<body class="font-sans antialiased text-shop-text" style="font-family: Vazirmatn, sans-serif;">
    <main class="relative flex min-h-screen items-center justify-center px-4 py-16">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-24 end-[-4rem] h-72 w-72 rounded-full bg-shop-primary/10 blur-3xl"></div>
            <div class="absolute -bottom-28 start-[-3rem] h-72 w-72 rounded-full bg-shop-accent/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto w-full max-w-md text-center">
            <p class="text-6xl font-black tracking-tight text-shop-primary/20 sm:text-7xl" dir="ltr">{{ $code }}</p>

            <div class="mx-auto mt-2 mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-shop-primary/10 text-shop-primary">
                @yield('icon')
            </div>

            <p class="text-xs font-bold text-shop-primary/80">{{ $storeName }}</p>
            <h1 class="mt-2 text-2xl font-black text-shop-text">{{ $title }}</h1>
            <p class="mt-3 text-sm leading-7 text-shop-muted">{{ $message }}</p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                @hasSection('actions')
                    @yield('actions')
                @else
                    <a href="{{ url('/') }}" class="btn-primary">بازگشت به فروشگاه</a>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
