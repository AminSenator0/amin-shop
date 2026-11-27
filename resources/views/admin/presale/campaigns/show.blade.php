
@extends('layouts.admin')

@section('title', $presaleCampaign->title)

@push('styles')
<style>
    .show-container {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #16a34a;
        --danger: #e11d48;
        --amber: #d97706;
        --text: #1e293b;
        --muted: #64748b;
        --border: #e2e8f0;
        padding: clamp(.75rem, 2vw, 1.5rem);
        max-width: 1500px;
        margin: 0 auto;
        color: var(--text);
    }

    .campaign-header {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding: clamp(1.25rem, 3vw, 2.5rem);
        margin-bottom: 1.5rem;
        color: #fff;
        border-radius: 26px;
        background: linear-gradient(125deg, #3730a3 0%, #6d28d9 52%, #be185d 100%);
        box-shadow: 0 18px 45px rgba(79, 70, 229, .2);
    }

    .campaign-header::before,
    .campaign-header::after {
        content: '';
        position: absolute;
        z-index: -1;
        border-radius: 50%;
        pointer-events: none;
    }

    .campaign-header::before {
        width: 360px;
        height: 360px;
        top: -210px;
        right: -80px;
        background: rgba(255,255,255,.11);
    }

    .campaign-header::after {
        width: 240px;
        height: 240px;
        bottom: -170px;
        left: 12%;
        background: rgba(255,255,255,.08);
    }

    .header-content {
        position: relative;
        z-index: 1;
    }

    .header-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .campaign-title {
        margin: .75rem 0 0;
        font-size: clamp(1.35rem, 3vw, 2rem);
        line-height: 1.6;
        font-weight: 850;
        overflow-wrap: anywhere;
    }

    .campaign-subtitle {
        color: rgba(255,255,255,.75);
        font-size: .85rem;
        margin-top: .35rem;
    }

    .header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        flex-shrink: 0;
    }

    .status-badge,
    .extension-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .85rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .status-badge {
        background: rgba(255,255,255,.17);
        border: 1px solid rgba(255,255,255,.25);
        color: #fff;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #86efac;
        box-shadow: 0 0 0 4px rgba(134,239,172,.15);
    }

    .status-badge.closed .status-dot {
        background: #fda4af;
        box-shadow: 0 0 0 4px rgba(253,164,175,.15);
    }

    .extension-badge {
        color: #fff7ed;
        background: rgba(245,158,11,.22);
        border: 1px solid rgba(255,255,255,.2);
    }

    .campaign-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }

    .stat-pill {
        min-width: 0;
        padding: 1rem;
        text-align: center;
        border-radius: 17px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        backdrop-filter: blur(12px);
    }

    .stat-value {
        display: block;
        font-size: clamp(1.1rem, 2.3vw, 1.55rem);
        font-weight: 850;
        overflow-wrap: anywhere;
    }

    .stat-label {
        display: block;
        margin-top: .3rem;
        color: rgba(255,255,255,.75);
        font-size: .75rem;
    }

    .section-card {
        padding: clamp(1rem, 2.5vw, 1.75rem);
        margin-bottom: 1.25rem;
        border: 1px solid #e8edf5;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 5px 22px rgba(15,23,42,.045);
        transition: box-shadow .25s ease, border-color .25s ease;
    }

    .section-card:hover {
        border-color: #dbe3f2;
        box-shadow: 0 10px 30px rgba(15,23,42,.065);
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.35rem;
    }

    .section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 13px;
        color: #4f46e5;
        background: #eef2ff;
    }

    .section-title {
        margin: 0;
        color: #1e293b;
        font-size: 1.05rem;
        font-weight: 800;
    }

    .section-description {
        margin-top: .2rem;
        color: var(--muted);
        font-size: .8rem;
    }

    .glass-input {
        width: 100%;
        min-height: 46px;
        padding: .7rem .9rem;
        border: 1.5px solid #dfe5ef;
        border-radius: 12px;
        outline: none;
        background: #fbfcfe;
        color: #1e293b;
        font-size: .9rem;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .glass-input:hover {
        border-color: #c7d2fe;
    }

    .glass-input:focus {
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(99,102,241,.1);
    }

    .field-label {
        display: block;
        margin-bottom: .45rem;
        color: #475569;
        font-size: .82rem;
        font-weight: 750;
    }

    .field-hint {
        display: block;
        margin-top: .4rem;
        color: #94a3b8;
        font-size: .72rem;
    }

    .btn-glass {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-height: 43px;
        padding: .65rem 1.05rem;
        border: 1px solid transparent;
        border-radius: 12px;
        color: #fff;
        font-size: .83rem;
        font-weight: 750;
        white-space: nowrap;
        cursor: pointer;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }

    .btn-glass:hover {
        color: #fff;
        filter: brightness(1.04);
        transform: translateY(-2px);
        box-shadow: 0 7px 17px rgba(15,23,42,.13);
    }

    .btn-glass:active {
        transform: translateY(0);
    }

    .btn-primary { background: linear-gradient(135deg, #6366f1, #7c3aed); }
    .btn-success { background: linear-gradient(135deg, #22c55e, #15803d); }
    .btn-danger { background: linear-gradient(135deg, #f43f5e, #be123c); }
    .btn-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .btn-slate { background: linear-gradient(135deg, #64748b, #475569); }

    .btn-outline {
        color: #475569;
        background: #fff;
        border-color: #e2e8f0;
    }

    .btn-outline:hover {
        color: #334155;
        background: #f8fafc;
    }

    .add-product-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(220px, .55fr) auto;
        gap: 1rem;
        align-items: end;
    }

    .money-preview {
        min-height: 30px;
        margin-top: .4rem;
        color: #64748b;
        font-size: .73rem;
        line-height: 1.8;
    }

    .money-preview strong {
        color: #4f46e5;
        font-weight: 750;
    }

    .products-list {
        display: flex;
        flex-direction: column;
        gap: .85rem;
    }

    .product-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #e8edf5;
        border-radius: 17px;
        background: linear-gradient(135deg, #fff, #fbfcff);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .product-row:hover {
        border-color: #c7d2fe;
        box-shadow: 0 5px 18px rgba(99,102,241,.07);
    }

    .product-main {
        display: flex;
        align-items: center;
        gap: .9rem;
        flex: 1 1 280px;
        min-width: 0;
    }

    .product-img {
        width: 62px;
        height: 62px;
        flex-shrink: 0;
        object-fit: cover;
        border-radius: 13px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .product-name {
        overflow: hidden;
        color: #1e293b;
        font-size: .92rem;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-prices {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem .8rem;
        margin-top: .45rem;
        font-size: .77rem;
        font-weight: 700;
    }

    .price-presale { color: #4f46e5; }
    .price-final { color: #059669; }
    .price-penalty { color: #e11d48; }

    .pricing-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(130px, 1fr)) auto;
        gap: .65rem;
        align-items: end;
        flex: 1 1 100%;
        padding: .9rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #edf0f5;
    }

    .pricing-form .glass-input {
        min-height: 42px;
        font-size: .83rem;
    }

    .remove-form {
        flex-shrink: 0;
    }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        border: 1px dashed #dbe3ef;
        border-radius: 17px;
        background: #fafbfe;
    }

    .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 68px;
        height: 68px;
        margin: 0 auto 1rem;
        border-radius: 20px;
        color: #818cf8;
        background: #eef2ff;
    }

    .empty-state h3 {
        margin: 0 0 .4rem;
        color: #334155;
        font-size: .95rem;
        font-weight: 800;
    }

    .empty-state p {
        margin: 0;
        color: #94a3b8;
        font-size: .8rem;
    }

    .notice-box {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        padding: .9rem 1rem;
        margin-bottom: 1rem;
        border: 1px solid #fde68a;
        border-radius: 13px;
        color: #92400e;
        background: #fffbeb;
        font-size: .82rem;
        line-height: 1.9;
    }

    .orders-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .orders-info {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-width: 0;
    }

    .orders-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 15px;
        color: #4f46e5;
        background: #eef2ff;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15,23,42,.55);
        backdrop-filter: blur(6px);
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-box {
        width: 100%;
        max-width: 440px;
        padding: 1.5rem;
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 25px 70px rgba(15,23,42,.25);
        animation: modal-in .2s ease-out;
    }

    @keyframes modal-in {
        from { opacity: 0; transform: translateY(12px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-bottom: 1rem;
        border-radius: 15px;
        color: #d97706;
        background: #fef3c7;
    }

    .modal-title {
        margin: 0 0 .4rem;
        color: #1e293b;
        font-size: 1.1rem;
        font-weight: 850;
    }

    .modal-description {
        margin-bottom: 1.25rem;
        color: #64748b;
        font-size: .82rem;
        line-height: 1.8;
    }

    .form-actions {
        display: flex;
        gap: .65rem;
        margin-top: 1.25rem;
    }

    .form-actions > * {
        flex: 1;
    }

    .animate-in {
        animation: fade-up .45s ease both;
    }

    .delay-1 { animation-delay: .07s; }
    .delay-2 { animation-delay: .14s; }
    .delay-3 { animation-delay: .21s; }

    @keyframes fade-up {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 900px) {
        .add-product-form {
            grid-template-columns: 1fr 1fr;
        }

        .add-product-form button {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 640px) {
        .show-container {
            padding: .75rem;
        }

        .campaign-header {
            border-radius: 20px;
        }

        .header-top {
            flex-direction: column;
            gap: 1rem;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions form,
        .header-actions button {
            flex: 1;
        }

        .header-actions .btn-glass {
            width: 100%;
        }

        .campaign-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem;
        }

        .stat-pill {
            padding: .8rem .5rem;
        }

        .stat-value {
            font-size: 1.15rem;
        }

        .section-card {
            border-radius: 17px;
        }

        .add-product-form {
            grid-template-columns: 1fr;
        }

        .add-product-form button {
            grid-column: auto;
            width: 100%;
        }

        .product-row {
            align-items: flex-start;
            gap: .8rem;
        }

        .product-main {
            flex-basis: 100%;
        }

        .pricing-form {
            grid-template-columns: 1fr 1fr;
        }

        .pricing-form button {
            grid-column: 1 / -1;
            width: 100%;
        }

        .remove-form {
            width: 100%;
        }

        .remove-form button {
            width: 100%;
        }

        .orders-card {
            align-items: stretch;
            flex-direction: column;
        }

        .orders-card > a {
            width: 100%;
        }

        .form-actions {
            flex-direction: column-reverse;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .show-container *,
        .show-container *::before,
        .show-container *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')
<div class="show-container">

    {{-- سربرگ کمپین --}}
    <section class="campaign-header animate-in">
        <div class="header-content">
            <div class="header-top">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="status-badge {{ $presaleCampaign->status !== 'active' ? 'closed' : '' }}">
                            <span class="status-dot"></span>
                            {{ $presaleCampaign->status === 'active' ? 'کمپین فعال' : 'کمپین بسته شده' }}
                        </span>

                        @if($presaleCampaign->is_extended)
                            <span class="extension-badge">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                تمدید شده
                            </span>
                        @endif
                    </div>

                    <h1 class="campaign-title">{{ $presaleCampaign->title }}</h1>
                    <p class="campaign-subtitle">پنل مدیریت و جزئیات کمپین پیش‌فروش</p>
                </div>

                <div class="header-actions">
                    @if($presaleCampaign->status === 'active')
                        <form action="{{ route('admin.presale.campaigns.close', $presaleCampaign) }}"
                              method="POST"
                              onsubmit="return confirm('آیا از بستن این کمپین اطمینان دارید؟ پس از بستن، امکان تعیین قیمت نهایی فراهم می‌شود.')">
                            @csrf
                            <button type="submit" class="btn-glass btn-danger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                بستن کمپین
                            </button>
                        </form>

                        <button type="button"
                                onclick="openExtendModal()"
                                class="btn-glass btn-amber">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            تمدید کمپین
                        </button>
                    @endif
                </div>
            </div>

            <div class="campaign-stats">
                <div class="stat-pill">
                    <span class="stat-value">{{ $presaleCampaign->presaleProducts->count() }}</span>
                    <span class="stat-label">محصول ثبت‌شده</span>
                </div>

                <div class="stat-pill">
                    <span class="stat-value">{{ $presaleCampaign->presaleOrders->count() }}</span>
                    <span class="stat-label">سفارش ثبت‌شده</span>
                </div>

                <div class="stat-pill">
                    <span class="stat-value">{{ $presaleCampaign->starts_at->toJalali()->format('Y/m/d') }}</span>
                    <span class="stat-label">تاریخ شروع</span>
                </div>

                <div class="stat-pill">
                    <span class="stat-value">{{ $presaleCampaign->ends_at->toJalali()->format('Y/m/d') }}</span>
                    <span class="stat-label">تاریخ پایان</span>
                </div>
            </div>
        </div>
    </section>

    {{-- افزودن محصول --}}
    @if($presaleCampaign->status === 'active')
        <section class="section-card animate-in delay-1">
            <div class="section-heading">
                <span class="section-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                <div>
                    <h2 class="section-title">افزودن محصول به کمپین</h2>
                    <p class="section-description">محصول موردنظر و مبلغ پیش‌پرداخت آن را انتخاب کنید.</p>
                </div>
            </div>

            <form action="{{ route('admin.presale.campaigns.products.store', $presaleCampaign) }}"
                  method="POST"
                  class="add-product-form">
                @csrf

                <div>
                    <label for="product_id" class="field-label">انتخاب محصول</label>
                    <select id="product_id" name="product_id" class="glass-input" required>
                        <option value="">-- یک محصول انتخاب کنید --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ number_format($product->price) }} تومان)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- فیلدهای سفارشی محصول --}}
                <div id="custom-fields-container" class="space-y-3"></div>

                <div>
                    <label for="presale_price_display" class="field-label">قیمت پیش‌پرداخت (تومان)</label>

                    <input type="text"
                           id="presale_price_display"
                           class="glass-input"
                           inputmode="numeric"
                           autocomplete="off"
                           placeholder="مثلاً ۷۰۰,۰۰۰"
                           data-money-display
                           data-money-target="presale_price"
                           data-money-preview="presale-price-preview"
                           required>

                    <input type="hidden" name="presale_price" id="presale_price">

                    <div class="money-preview" id="presale-price-preview" aria-live="polite">
                        مبلغ به حروف نمایش داده می‌شود.
                    </div>
                    <span class="field-hint">حداقل مبلغ قابل ثبت: ۱۰۰,۰۰۰ تومان</span>
                </div>

                <button type="submit" class="btn-glass btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    افزودن محصول
                </button>

                <script>
                document.getElementById('product_id').addEventListener('change', async function() {
                    const container = document.getElementById('custom-fields-container');
                    container.innerHTML = '';
                    if (!this.value) return;

                    try {
                        const res = await fetch(`/admin/products/${this.value}/custom-fields`);
                        const data = await res.json();
                        
                        data.fields.forEach(field => {
                            let html = '<div>' +
                                '<label class=\"field-label\">' + field.label + '</label>' +
                                '<select name=\"custom_fields[' + field.id + '][option_id]\" class=\"glass-input\" required>' +
                                '<option value=\"\">-- انتخاب کنید --</option>';
                            
                            field.options.forEach(opt => {
                                let stockText = opt.stock !== undefined && opt.stock !== null ? ' (' + opt.stock + ' عدد)' : '';
                                html += '<option value=\"' + opt.id + '\">' + opt.value + stockText + '</option>';
                            });
                            
                            html += '</select></div>';
                            container.innerHTML += html;
                        });
                    } catch (e) {
                        console.error('Error loading custom fields:', e);
                    }
                });
                </script>
            </form>
        </section>
    @endif

    {{-- لیست محصولات --}}
    <section class="section-card animate-in delay-2">
        <div class="section-heading">
            <span class="section-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </span>
            <div>
                <h2 class="section-title">محصولات پیش‌فروش</h2>
                <p class="section-description">
                    {{ $presaleCampaign->presaleProducts->count() }} محصول در این کمپین ثبت شده است.
                </p>
            </div>
        </div>

        @if($presaleCampaign->presaleProducts->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3>هنوز محصولی ثبت نشده است</h3>
                <p>برای شروع، از بخش افزودن محصول استفاده کنید.</p>
            </div>
        @else
            <div class="products-list">
                @foreach($presaleCampaign->presaleProducts as $pp)
                    <article class="product-row">
                        <div class="product-main">
                            <img
                               src="{{ $pp->product?->images->first()?->url() ?? asset('images/placeholder-book.png') }}"
                                alt="{{ $pp->product?->name ?? 'محصول حذف شده' }}"
                                class="product-img"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.png') }}';"
                            >

                            <div class="min-w-0 flex-1">
                              <div class="product-name">
    {{ $pp->product?->name ?? 'محصول حذف شده' }}
    @if($pp->customFieldsLabel())
        <span class="text-slate-400 text-sm">({{ $pp->customFieldsLabel() }})</span>
    @endif
</div>
                                <div class="product-prices">
                                    <span class="price-presale">
                                        پیش‌پرداخت: {{ number_format($pp->presale_price) }} تومان
                                    </span>

                                    @if($pp->final_price !== null)
                                        <span class="price-final">
                                            قیمت نهایی: {{ number_format($pp->final_price) }} تومان
                                        </span>
                                    @endif

                                    @if($pp->penalty_amount > 0)
                                        <span class="price-penalty">
                                            جریمه: {{ number_format($pp->penalty_amount) }} تومان
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ثبت قیمت نهایی پس از بسته شدن کمپین --}}
                        @if($presaleCampaign->status === 'closed')
                            <form action="{{ route('admin.presale.campaigns.products.pricing', [$presaleCampaign, $pp]) }}"
                                  method="POST"
                                  class="pricing-form">
                                @csrf

                                <div>
                                    <label class="field-label" for="final-price-{{ $pp->id }}">قیمت نهایی (تومان)</label>

                                    <input type="text"
                                           id="final-price-{{ $pp->id }}"
                                           class="glass-input"
                                           inputmode="numeric"
                                           autocomplete="off"
                                           placeholder="قیمت نهایی"
                                           value="{{ $pp->final_price !== null ? number_format($pp->final_price) : '' }}"
                                           data-money-display
                                           data-money-target="final_price_{{ $pp->id }}"
                                           data-money-preview="final-preview-{{ $pp->id }}">

                                    <input type="hidden"
                                           name="final_price"
                                           id="final_price_{{ $pp->id }}"
                                           value="{{ $pp->final_price }}">

                                    <div class="money-preview" id="final-preview-{{ $pp->id }}" aria-live="polite"></div>
                                </div>

                                <div>
                                    <label class="field-label" for="penalty-display-{{ $pp->id }}">مبلغ جریمه (تومان)</label>

                                    <input type="text"
                                           id="penalty-display-{{ $pp->id }}"
                                           class="glass-input"
                                           inputmode="numeric"
                                           autocomplete="off"
                                           placeholder="مبلغ جریمه"
                                           value="{{ $pp->penalty_amount !== null ? number_format($pp->penalty_amount) : '' }}"
                                           data-money-display
                                           data-money-target="penalty_amount_{{ $pp->id }}"
                                           data-money-preview="penalty-preview-{{ $pp->id }}">

                                    <input type="hidden"
                                           name="penalty_amount"
                                           id="penalty_amount_{{ $pp->id }}"
                                           value="{{ $pp->penalty_amount }}">

                                    <div class="money-preview" id="penalty-preview-{{ $pp->id }}" aria-live="polite"></div>
                                </div>

                                <button type="submit" class="btn-glass btn-success">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                    ثبت قیمت‌ها
                                </button>
                            </form>
                        @endif

                        {{-- حذف محصول --}}
                        @if($presaleCampaign->status === 'active')
                            <form action="{{ route('admin.presale.campaigns.products.destroy', [$presaleCampaign, $pp]) }}"
                                  method="POST"
                                  class="remove-form"
                                  onsubmit="return confirm('آیا از حذف این محصول از کمپین اطمینان دارید؟')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn-glass btn-danger" title="حذف محصول" aria-label="حذف محصول">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span class="sm:hidden">حذف محصول</span>
                                </button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- تعیین مهلت تسویه --}}
    @if($presaleCampaign->status === 'closed')
        @php
            $awaitingOrders = $presaleCampaign->presaleOrders()
                ->whereIn('status', [
                    \App\Enums\PresaleStatus::AwaitingPayment->value,
                    \App\Enums\PresaleStatus::PartiallyPaid->value
                ])
->whereNull('presale_deadline_at')
                ->count();
        @endphp

        @if($awaitingOrders > 0)
            <section class="section-card animate-in delay-3">
                <div class="section-heading">
                    <span class="section-icon" style="color:#d97706;background:#fffbeb;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="section-title">تعیین مهلت تسویه سفارش‌ها</h2>
                        <p class="section-description">برای سفارش‌های واجد شرایط، مهلت پرداخت نهایی تعیین کنید.</p>
                    </div>
                </div>

                <div class="notice-box">
                    <svg class="w-5 h-5 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>
                        <strong>{{ number_format($awaitingOrders) }} سفارش</strong>
                        در انتظار تعیین مهلت تسویه هستند.
                    </span>
                </div>

                <form action="{{ route('admin.presale.campaigns.deadline', $presaleCampaign) }}"
                      method="POST"
                      class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    @csrf

                    <div class="w-full sm:max-w-xs">
                        <label for="deadline-days" class="field-label">تعداد روز مهلت تسویه</label>
                        <input type="number"
                               id="deadline-days"
                               name="days"
                               class="glass-input"
                               placeholder="مثلاً ۲۰"
                               min="1"
                               max="365"
                               required>
                        <span class="field-hint">عدد باید بین ۱ تا ۳۶۵ روز باشد.</span>
                    </div>

                    <button type="submit"
                            class="btn-glass btn-primary w-full sm:w-auto"
                            onclick="return confirm('مهلت تسویه برای تمام سفارش‌های واجد شرایط تعیین شود؟')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        تعیین مهلت برای همه
                    </button>
                </form>
            </section>
        @endif
    @endif

    {{-- دسترسی به سفارش‌ها --}}
    <section class="section-card animate-in delay-3">
        <div class="orders-card">
            <div class="orders-info">
                <span class="orders-icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>

                <div>
                    <h2 class="section-title">سفارش‌های این کمپین</h2>
                    <p class="section-description">مشاهده، پیگیری و مدیریت سفارش‌های پیش‌فروش</p>
                </div>
            </div>

            <a href="{{ route('admin.orders.index', ['campaign_id' => $presaleCampaign->id, 'order_type' => 'presale']) }}"
                    @if(request('campaign_id'))
            <input type="hidden" name="campaign_id" value="{{ request('campaign_id') }}">
        @endif
               class="btn-glass btn-primary">
                مشاهده سفارش‌ها
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
    </section>
</div>

{{-- پنجره تمدید کمپین --}}
@if($presaleCampaign->status === 'active')
    <div id="extend-modal"
         class="modal-overlay hidden"
         role="dialog"
         aria-modal="true"
         aria-labelledby="extend-modal-title"
         onclick="if(event.target === this) closeExtendModal()">

        <div class="modal-box">
            <div class="modal-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h3 id="extend-modal-title" class="modal-title">تمدید زمان کمپین</h3>
            <p class="modal-description">
                تاریخ و ساعت جدید پایان کمپین را انتخاب کنید. تاریخ جدید باید مطابق قوانین ثبت‌شده در سیستم باشد.
            </p>

            <form action="{{ route('admin.presale.campaigns.extend', $presaleCampaign) }}" method="POST">
                @csrf

                <label for="new-ends-at" class="field-label">تاریخ و ساعت پایان جدید</label>
                <input type="datetime-local"
                       id="new-ends-at"
                       name="new_ends_at"
                       class="glass-input"
                       required>

                <div class="form-actions">
                    <button type="button"
                            onclick="closeExtendModal()"
                            class="btn-glass btn-outline">
                        انصراف
                    </button>

                    <button type="submit" class="btn-glass btn-amber">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        ثبت تمدید
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const modal = document.getElementById('extend-modal');

    window.openExtendModal = function () {
        if (!modal) return;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        const input = modal.querySelector('input[name="new_ends_at"]');
        if (input) input.focus();
    };

    window.closeExtendModal = function () {
        if (!modal) return;

        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeExtendModal();
        }
    });

    // تبدیل ارقام فارسی و عربی به انگلیسی
    function toEnglishDigits(value) {
        const digits = {
            '۰':'0','۱':'1','۲':'2','۳':'3','۴':'4',
            '۵':'5','۶':'6','۷':'7','۸':'8','۹':'9',
            '٠':'0','١':'1','٢':'2','٣':'3','٤':'4',
            '٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'
        };

        return String(value).replace(/[۰-۹٠-٩]/g, char => digits[char] || char);
    }

    function onlyNumbers(value) {
        return toEnglishDigits(value).replace(/[^\d]/g, '');
    }

    function formatNumber(value) {
        if (!value) return '';
        return Number(value).toLocaleString('en-US');
    }

    const ones = [
        '', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه',
        'ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده',
        'شانزده', 'هفده', 'هجده', 'نوزده'
    ];

    const tens = [
        '', '', 'بیست', 'سی', 'چهل', 'پنجاه',
        'شصت', 'هفتاد', 'هشتاد', 'نود'
    ];

    const hundreds = [
        '', 'یکصد', 'دویست', 'سیصد', 'چهارصد',
        'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'
    ];

    const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];

    function threeDigitsToWords(number) {
        const parts = [];
        const hundred = Math.floor(number / 100);
        const remainder = number % 100;

        if (hundred) parts.push(hundreds[hundred]);

        if (remainder < 20) {
            if (remainder) parts.push(ones[remainder]);
        } else {
            const ten = Math.floor(remainder / 10);
            const one = remainder % 10;

            parts.push(tens[ten]);
            if (one) parts.push(ones[one]);
        }

        return parts.join(' و ');
    }

    function numberToPersianWords(value) {
        let number = Number(value);

        if (!Number.isFinite(number) || number < 0) return '';
        if (number === 0) return 'صفر';

        number = Math.floor(number);

        const groups = [];
        let scaleIndex = 0;

        while (number > 0 && scaleIndex < scales.length) {
            const group = number % 1000;

            if (group) {
                let text = threeDigitsToWords(group);

                if (scales[scaleIndex]) {
                    text += ' ' + scales[scaleIndex];
                }

                groups.unshift(text);
            }

            number = Math.floor(number / 1000);
            scaleIndex++;
        }

        if (number > 0) return 'مبلغ بیش از محدوده قابل نمایش';

        return groups.join(' و ');
    }

    function updateMoneyField(input) {
        const targetId = input.dataset.moneyTarget;
        const previewId = input.dataset.moneyPreview;

        const hiddenInput = document.getElementById(targetId);
        const preview = document.getElementById(previewId);

        const rawValue = onlyNumbers(input.value);

        if (hiddenInput) {
            hiddenInput.value = rawValue;
        }

        input.value = formatNumber(rawValue);

        if (preview) {
            if (rawValue === '') {
                preview.textContent = 'مبلغ به حروف نمایش داده می‌شود.';
            } else {
                preview.innerHTML = '';

                const strong = document.createElement('strong');
                strong.textContent = numberToPersianWords(rawValue) + ' تومان';

                preview.appendChild(document.createTextNode('به حروف: '));
                preview.appendChild(strong);
            }
        }
    }

    document.querySelectorAll('[data-money-display]').forEach(function (input) {
        // مقدار اولیه فیلدهای ویرایش قیمت
        updateMoneyField(input);

        input.addEventListener('input', function () {
            updateMoneyField(input);
        });

        input.addEventListener('paste', function () {
            setTimeout(function () {
                updateMoneyField(input);
            }, 0);
        });
    });

    // قبل از ارسال فرم، مقادیر خام دوباره همگام‌سازی می‌شوند.
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-money-display]').forEach(updateMoneyField);
        });
    });
})();
</script>
@endpush
