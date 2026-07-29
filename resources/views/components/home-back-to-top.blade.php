<button
    type="button"
    class="home-back-to-top"
    aria-label="بازگشت به بالا"
    x-data="{ visible: false }"
    x-init="window.addEventListener('scroll', () => { visible = window.scrollY > 640 }, { passive: true })"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
>
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
</button>
