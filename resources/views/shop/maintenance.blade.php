<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>در حال به‌روزرسانی — {{ $store['name'] ?? $storeName }}</title>
    <x-favicon />
    @vite(['resources/css/app.css'])
    <x-theme-variables />
</head>
<body class="flex min-h-screen items-center justify-center bg-shop-background font-sans antialiased" style="font-family: Vazirmatn, sans-serif;">
    <div class="mx-auto max-w-md px-6 text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-shop-primary/10 text-shop-primary">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.099 1.743.188m-1.743-.188a2.548 2.548 0 00-3.586 3.586l6.837 5.63" /></svg>
        </div>
        <h1 class="text-2xl font-black text-shop-text">{{ $store['name'] ?? $storeName }}</h1>
        <p class="mt-4 text-sm leading-7 text-shop-muted">{{ $message }}</p>
    </div>
</body>
</html>
