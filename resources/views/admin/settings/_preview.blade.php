<aside class="admin-settings-preview">
    <div class="admin-settings-preview-sticky">
        <p class="admin-settings-preview-label">پیش‌نمایش زنده</p>
        <div class="admin-settings-preview-card">
            <p class="admin-settings-preview-card-title">هدر فروشگاه</p>
            <div class="admin-settings-preview-header">
                <div class="flex items-center gap-2 min-w-0">
                    <template x-if="logoPreview"><img :src="logoPreview" class="h-8 w-8 rounded-lg object-contain" alt=""></template>
                    <span class="truncate text-sm font-black" x-text="storeName || 'نام فروشگاه'"></span>
                </div>
            </div>
        </div>
        <div class="admin-settings-preview-card">
            <p class="admin-settings-preview-card-title">بنر صفحه اصلی</p>
            <div class="admin-settings-preview-hero" :style="{ background: `linear-gradient(135deg, ${themePalette['hero-from']}, ${themePalette['hero-via']}, ${themePalette['hero-to']})` }">
                <p class="text-sm font-bold text-white" x-text="storeName"></p>
                <p class="mt-1 text-xs text-white/80" x-text="tagline || 'شعار فروشگاه'"></p>
            </div>
        </div>
        <div class="admin-settings-preview-card">
            <p class="admin-settings-preview-card-title">صفحه فروشگاه</p>
            <div class="overflow-hidden rounded-xl border" :style="{ borderColor: themePalette.border, background: themePalette.background }">
                <div class="border-b px-3 py-2" :style="{ borderColor: themePalette.border, background: '#fff' }">
                    <p class="text-xs font-bold" :style="{ color: themePalette.text }" x-text="storeName"></p>
                </div>
                <div class="space-y-2 p-3">
                    <p class="text-[11px]" :style="{ color: themePalette.muted }">متن توضیحی نمونه</p>
                    <span class="inline-block rounded-lg px-3 py-1 text-[10px] font-bold text-white" :style="{ background: `linear-gradient(135deg, ${themePalette.primary}, ${themePalette.accent})` }">دکمه</span>
                </div>
            </div>
        </div>
        <div class="admin-settings-preview-card">
            <p class="admin-settings-preview-card-title">فوتر</p>
            <p class="text-xs leading-relaxed text-zinc-600" x-text="footerDescription || 'توضیح فوتر...'"></p>
        </div>
        <div class="admin-settings-preview-card">
            <p class="admin-settings-preview-card-title">تماس</p>
            <div class="space-y-1.5 text-xs text-zinc-600">
                <p x-show="contactPhone" x-text="contactPhone"></p>
                <p x-show="contactEmail" x-text="contactEmail" dir="ltr"></p>
            </div>
        </div>
        <p class="text-center text-[11px] text-zinc-400">پالت همین‌جا زنده به‌روز می‌شود — برای فروشگاه ذخیره کنید</p>
    </div>
</aside>
