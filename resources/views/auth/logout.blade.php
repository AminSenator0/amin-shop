<x-guest-layout>
    <div class="mb-7 text-center">
        <h2 class="text-xl font-black text-shop-text">خروج از حساب</h2>
        <p class="mt-2 text-sm text-shop-muted">در حال خروج از حساب کاربری...</p>
    </div>

    <form id="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
        <noscript>
            <button type="submit" class="btn-primary w-full !py-3">خروج از حساب</button>
        </noscript>
    </form>

    <script>
        document.getElementById('logout-form')?.submit();
    </script>
</x-guest-layout>
