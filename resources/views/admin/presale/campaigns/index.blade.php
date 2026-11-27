
@extends('layouts.admin')

@section('title', 'مدیریت کمپین‌های پیش‌فروش')

@push('styles')
<style>
    .presale-dashboard {
        --pd-primary: #635bdb;
        --pd-primary-dark: #5046c5;
        --pd-text: #172033;
        --pd-muted: #8490a5;
        --pd-border: #e8ebf2;
        direction: rtl;
        padding: 28px 16px 48px;
        color: var(--pd-text);
    }

    .presale-dashboard * {
        box-sizing: border-box;
    }

    .pd-wrapper {
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Header */

    .pd-header {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        padding: 30px;
        margin-bottom: 25px;
        background: linear-gradient(120deg, #37358f 0%, #6257d6 55%, #8875ed 100%);
        color: white;
        box-shadow: 0 12px 35px rgba(79,70,229,.18);
    }

    .pd-header::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,.07);
        top: -190px;
        left: 8%;
        pointer-events: none;
    }

    .pd-header::after {
        content: '';
        position: absolute;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,.12);
        bottom: -120px;
        left: 25%;
        pointer-events: none;
    }

    .pd-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .pd-heading {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .pd-heading-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        flex-shrink: 0;
    }

    .pd-heading-icon svg {
        width: 29px;
        height: 29px;
    }

    .pd-heading h1 {
        font-size: 25px;
        font-weight: 900;
        margin: 0 0 8px;
        color: white;
    }

    .pd-heading p {
        margin: 0;
        color: rgba(255,255,255,.76);
        font-size: 12px;
        line-height: 1.8;
    }

    .pd-create-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 13px 20px;
        border-radius: 13px;
        background: white;
        color: #5148c5;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 5px 18px rgba(30,27,75,.12);
        transition: .2s;
    }

    .pd-create-btn:hover {
        color: #37358f;
        background: #f5f4ff;
        transform: translateY(-2px);
    }

    .pd-create-btn svg {
        width: 18px;
        height: 18px;
    }

    /* Overview */

    .pd-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 24px;
    }

    .pd-overview-card {
        background: white;
        border: 1px solid var(--pd-border);
        border-radius: 17px;
        padding: 19px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 5px 20px rgba(31,41,55,.025);
        min-width: 0;
    }

    .pd-overview-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pd-overview-icon svg {
        width: 22px;
        height: 22px;
    }

    .pd-icon-total {
        color: #6258d3;
        background: #f0efff;
    }

    .pd-icon-active {
        color: #16845a;
        background: #e7f8ef;
    }

    .pd-icon-closed {
        color: #c45b69;
        background: #fff0f1;
    }

    .pd-overview-label {
        color: var(--pd-muted);
        font-size: 11px;
        margin-bottom: 5px;
    }

    .pd-overview-value {
        color: #27334a;
        font-size: 21px;
        font-weight: 900;
    }

    .pd-overview-value small {
        font-size: 10px;
        color: #929bad;
        font-weight: 500;
    }

    /* Toolbar */

    .pd-toolbar {
        background: white;
        border: 1px solid var(--pd-border);
        border-radius: 17px;
        padding: 17px;
        margin-bottom: 21px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }

    .pd-toolbar-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 13px;
        font-weight: 850;
        color: #344057;
        white-space: nowrap;
    }

    .pd-toolbar-heading svg {
        width: 19px;
        height: 19px;
        color: var(--pd-primary);
    }

    .pd-toolbar-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        justify-content: flex-end;
    }

    .pd-search {
        position: relative;
        width: min(300px, 100%);
    }

    .pd-search svg {
        position: absolute;
        right: 13px;
        top: 50%;
        transform: translateY(-50%);
        width: 17px;
        height: 17px;
        color: #98a1b2;
        pointer-events: none;
    }

    .pd-search input {
        width: 100%;
        height: 43px;
        border: 1px solid #e8ebf2;
        border-radius: 11px;
        background: #fafbfe;
        padding: 0 40px 0 12px;
        font-family: inherit;
        font-size: 12px;
        outline: none;
        transition: .2s;
        color: #344057;
    }

    .pd-search input:focus {
        background: white;
        border-color: var(--pd-primary);
        box-shadow: 0 0 0 3px rgba(99,91,219,.09);
    }

    .pd-filter {
        height: 43px;
        min-width: 145px;
        padding: 0 12px;
        border-radius: 11px;
        border: 1px solid #e8ebf2;
        background: #fafbfe;
        color: #526078;
        font-family: inherit;
        font-size: 12px;
        outline: none;
        cursor: pointer;
    }

    .pd-filter:focus {
        border-color: var(--pd-primary);
    }

    .pd-results {
        font-size: 11px;
        color: #929bad;
        white-space: nowrap;
    }

    /* Campaign grid */

    .pd-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .pd-campaign-card {
        min-width: 0;
        position: relative;
        display: flex;
        flex-direction: column;
        background: white;
        border: 1px solid var(--pd-border);
        border-radius: 19px;
        padding: 21px;
        box-shadow: 0 5px 22px rgba(31,41,55,.035);
        transition: transform .25s, box-shadow .25s, border-color .25s;
        animation: pd-enter .45s ease both;
    }

    @keyframes pd-enter {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pd-campaign-card:hover {
        transform: translateY(-4px);
        border-color: #d9d5ff;
        box-shadow: 0 14px 35px rgba(68,62,155,.09);
    }

    .pd-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 17px;
        flex-wrap: wrap;
    }

    .pd-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 9px;
        font-size: 10px;
        font-weight: 850;
    }

    .pd-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .pd-status-active {
        color: #13845a;
        background: #e9f8ef;
    }

    .pd-status-closed {
        color: #cb4e60;
        background: #fff0f1;
    }

    .pd-status-extended {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        background: #fff7e9;
        border: 1px solid #ffebc5;
        color: #b7791f;
        font-size: 10px;
        border-radius: 8px;
        font-weight: 800;
    }

    .pd-status-extended svg {
        width: 13px;
        height: 13px;
    }

    .pd-card-title {
        color: #253149;
        font-size: 16px;
        line-height: 1.9;
        font-weight: 900;
        margin: 0 0 13px;
        overflow-wrap: anywhere;
    }

    .pd-card-date {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #8791a4;
        font-size: 11px;
        line-height: 1.9;
        margin-bottom: 19px;
    }

    .pd-card-date svg {
        width: 17px;
        height: 17px;
        color: #8c84e5;
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Stats */

    .pd-card-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
    }

    .pd-stat {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border-radius: 12px;
        background: #f8f9fc;
        border: 1px solid #f0f1f6;
        min-width: 0;
    }

    .pd-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pd-stat-icon svg {
        width: 18px;
        height: 18px;
    }

    .pd-stat-products {
        background: #eeedff;
        color: #6258d3;
    }

    .pd-stat-orders {
        background: #fff0e5;
        color: #c56a25;
    }

    .pd-stat-label {
        font-size: 10px;
        color: #929bad;
        margin-bottom: 4px;
    }

    .pd-stat-value {
        font-size: 17px;
        font-weight: 900;
        color: #344057;
        line-height: 1.2;
    }

    /* Progress */

    .pd-progress-section {
        margin-top: auto;
        margin-bottom: 18px;
        padding-top: 2px;
    }

    .pd-progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 9px;
        font-size: 10px;
        color: #929bad;
    }

    .pd-progress-percent {
        font-weight: 900;
        color: #6258d3;
        font-size: 11px;
    }

    .pd-progress-track {
        height: 7px;
        border-radius: 20px;
        background: #eff0f5;
        overflow: hidden;
    }

    .pd-progress-fill {
        height: 100%;
        border-radius: 20px;
        background: linear-gradient(90deg,#635bdb,#a18af5);
        transition: width .7s ease;
    }

    .pd-progress-fill.is-complete {
        background: linear-gradient(90deg,#e36b79,#f49b9f);
    }

    .pd-progress-fill.is-future {
        background: linear-gradient(90deg,#a5b4fc,#c4b5fd);
    }

    .pd-progress-caption {
        font-size: 10px;
        color: #929bad;
        margin-top: 8px;
    }

    /* Actions */

    .pd-card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding-top: 16px;
        border-top: 1px solid #edf0f5;
    }

    .pd-action {
        min-height: 37px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 12px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 10px;
        font-weight: 850;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        transition: .2s;
        white-space: nowrap;
    }

    .pd-action svg {
        width: 14px;
        height: 14px;
    }

    .pd-action:hover {
        transform: translateY(-2px);
    }

    .pd-action-manage {
        background: #efefff;
        color: #5c52c9;
    }

    .pd-action-manage:hover {
        color: #4338a8;
        background: #e4e2ff;
    }

    .pd-action-edit {
        background: #f2f4f8;
        color: #59677d;
    }

    .pd-action-edit:hover {
        background: #e8ebf1;
        color: #344057;
    }

    .pd-action-close {
        background: #fff0f1;
        color: #d34e60;
    }

    .pd-action-close:hover {
        background: #ffe1e4;
        color: #ad3045;
    }

    /* Empty states */

    .pd-empty {
        grid-column: 1 / -1;
        background: white;
        border: 1px dashed #dfe2ed;
        border-radius: 20px;
        padding: 65px 20px;
        text-align: center;
    }

    .pd-empty-icon {
        width: 76px;
        height: 76px;
        border-radius: 23px;
        margin: 0 auto 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0efff;
        color: #7770db;
    }

    .pd-empty-icon svg {
        width: 36px;
        height: 36px;
    }

    .pd-empty h3 {
        font-size: 17px;
        color: #344057;
        font-weight: 900;
        margin: 0 0 9px;
    }

    .pd-empty p {
        color: #929bad;
        font-size: 12px;
        line-height: 1.9;
        margin: 0 0 20px;
    }

    .pd-empty-filter {
        display: none;
    }

    .pd-pagination {
        margin-top: 27px;
    }

    .pd-pagination nav {
        display: flex;
        justify-content: center;
    }

    .pd-hidden {
        display: none !important;
    }

    @media(max-width:1100px) {
        .pd-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }
    }

    @media(max-width:700px) {
        .presale-dashboard {
            padding: 18px 10px 30px;
        }

        .pd-header {
            padding: 22px 18px;
            border-radius: 17px;
        }

        .pd-heading {
            gap: 11px;
        }

        .pd-heading-icon {
            width: 45px;
            height: 45px;
            border-radius: 13px;
        }

        .pd-heading-icon svg {
            width: 23px;
            height: 23px;
        }

        .pd-heading h1 {
            font-size: 18px;
        }

        .pd-heading p {
            font-size: 10px;
        }

        .pd-create-btn {
            padding: 10px 13px;
            font-size: 11px;
        }

        .pd-overview {
            gap: 9px;
        }

        .pd-overview-card {
            padding: 12px 9px;
            gap: 8px;
            border-radius: 13px;
        }

        .pd-overview-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
        }

        .pd-overview-icon svg {
            width: 17px;
            height: 17px;
        }

        .pd-overview-label {
            font-size: 9px;
        }

        .pd-overview-value {
            font-size: 16px;
        }

        .pd-overview-value small {
            display: none;
        }

        .pd-toolbar {
            padding: 14px;
            gap: 12px;
        }

        .pd-toolbar-controls {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px;
        }

        .pd-search {
            width: 100%;
            grid-column: 1 / -1;
        }

        .pd-filter {
            width: 100%;
            min-width: 0;
        }

        .pd-results {
            text-align: left;
            align-self: center;
        }

        .pd-grid {
            grid-template-columns: 1fr;
            gap: 13px;
        }

        .pd-campaign-card {
            padding: 17px;
            border-radius: 16px;
        }

        .pd-card-title {
            font-size: 15px;
        }

        .pd-pagination {
            overflow-x: auto;
        }
    }

    @media(max-width:380px) {
        .pd-overview-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .pd-overview-value {
            font-size: 15px;
        }

        .pd-card-actions {
            gap: 6px;
        }

        .pd-action {
            padding: 0 9px;
        }
    }

    @media(prefers-reduced-motion:reduce) {
        .presale-dashboard *,
        .presale-dashboard *::before,
        .presale-dashboard *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')

<div class="presale-dashboard">
    <div class="pd-wrapper">

        {{-- Header --}}

        <header class="pd-header">
            <div class="pd-header-content">

                <div class="pd-heading">
                    <div class="pd-heading-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>

                    <div>
                        <h1>کمپین‌های پیش‌فروش</h1>
                        <p>مدیریت کمپین‌ها، محصولات، قیمت‌گذاری و سفارش‌ها</p>
                    </div>
                </div>

                <a href="{{ route('admin.presale.campaigns.create') }}"
                   class="pd-create-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    ایجاد کمپین جدید
                </a>

            </div>
        </header>

        {{-- Overview --}}

        @php
            $pageCampaigns = $campaigns->getCollection();

            $activeOnPage = $pageCampaigns->where('status', 'active')->count();
            $closedOnPage = $pageCampaigns->where('status', '!=', 'active')->count();
        @endphp

        <div class="pd-overview">

            <div class="pd-overview-card">
                <div class="pd-overview-icon pd-icon-total">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <div class="pd-overview-label">کل کمپین‌ها</div>
                    <div class="pd-overview-value">
                        {{ number_format($campaigns->total()) }}
                        <small>کمپین</small>
                    </div>
                </div>
            </div>

            <div class="pd-overview-card">
                <div class="pd-overview-icon pd-icon-active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <div class="pd-overview-label">فعال در این صفحه</div>
                    <div class="pd-overview-value">
                        {{ number_format($activeOnPage) }}
                        <small>کمپین</small>
                    </div>
                </div>
            </div>

            <div class="pd-overview-card">
                <div class="pd-overview-icon pd-icon-closed">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <div class="pd-overview-label">بسته‌شده در این صفحه</div>
                    <div class="pd-overview-value">
                        {{ number_format($closedOnPage) }}
                        <small>کمپین</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- Search and Filters --}}

        <div class="pd-toolbar">

            <div class="pd-toolbar-heading">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/>
                </svg>
                جستجو و فیلتر کمپین‌ها
            </div>

            <div class="pd-toolbar-controls">

                <div class="pd-search">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>

                    <input
                        type="search"
                        id="campaignSearch"
                        placeholder="جستجوی نام کمپین..."
                        autocomplete="off"
                    >
                </div>

                <select id="campaignFilter" class="pd-filter" aria-label="فیلتر وضعیت کمپین">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="active">کمپین‌های فعال</option>
                    <option value="closed">کمپین‌های بسته‌شده</option>
                    <option value="extended">کمپین‌های تمدیدشده</option>
                </select>

                <span class="pd-results" id="campaignResults">
                    نمایش {{ number_format($campaigns->count()) }} کمپین
                </span>

            </div>

        </div>

        {{-- Campaign Cards --}}

        <div class="pd-grid" id="campaignGrid">

            @forelse($campaigns as $index => $campaign)

                @php
                    $totalSeconds = $campaign->ends_at->timestamp - $campaign->starts_at->timestamp;
                    $elapsedSeconds = now()->timestamp - $campaign->starts_at->timestamp;

                    $percent = $totalSeconds > 0
                        ? min(100, max(0, ($elapsedSeconds / $totalSeconds) * 100))
                        : 0;

                    $isFuture = now()->lt($campaign->starts_at);
                    $isExpired = now()->gte($campaign->ends_at);

                    $progressLabel = $isFuture
                        ? 'هنوز شروع نشده'
                        : ($isExpired ? 'بازه زمانی به پایان رسیده' : 'در حال سپری شدن');

                    $searchTitle = mb_strtolower($campaign->title);
                @endphp

                <article
                    class="pd-campaign-card"
                    data-title="{{ $searchTitle }}"
                    data-status="{{ $campaign->status === 'active' ? 'active' : 'closed' }}"
                    data-extended="{{ $campaign->is_extended ? 'yes' : 'no' }}"
                    style="animation-delay: {{ ($index % 6) * 0.06 }}s"
                >

                    {{-- Card header --}}

                    <div class="pd-card-top">

                        <span class="pd-status {{ $campaign->status === 'active' ? 'pd-status-active' : 'pd-status-closed' }}">
                            <span class="pd-status-dot"></span>
                            {{ $campaign->status === 'active' ? 'فعال' : 'بسته شده' }}
                        </span>

                        @if($campaign->is_extended)
                            <span class="pd-status-extended">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                تمدید شده
                            </span>
                        @endif

                    </div>

                    {{-- Title --}}

                    <h3 class="pd-card-title">
                        {{ $campaign->title }}
                    </h3>

                    {{-- Date range --}}

                    <div class="pd-card-date">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2v12a2 2 0 002 2z"/>
                        </svg>

                        <span>
                            شروع:
                            {{ $campaign->starts_at->toJalali()->format('Y/m/d') }}
                            <br>
                            پایان:
                            {{ $campaign->ends_at->toJalali()->format('Y/m/d') }}
                        </span>
                    </div>

                    {{-- Statistics --}}

                    <div class="pd-card-stats">

                        <div class="pd-stat">
                            <div class="pd-stat-icon pd-stat-products">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="pd-stat-label">محصولات کمپین</div>
                                <div class="pd-stat-value">
                                    {{ number_format($campaign->presale_products_count) }}
                                </div>
                            </div>
                        </div>

                        <div class="pd-stat">
                            <div class="pd-stat-icon pd-stat-orders">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-4 7h4m-4 4h4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="pd-stat-label">سفارش‌های ثبت‌شده</div>
                                <div class="pd-stat-value">
                                    {{ number_format($campaign->presale_orders_count) }}
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Time progress --}}

                    <div class="pd-progress-section">

                        <div class="pd-progress-header">
                            <span>پیشرفت بازه زمانی</span>
                            <span class="pd-progress-percent">
                                {{ number_format(round($percent)) }}٪
                            </span>
                        </div>

                        <div
                            class="pd-progress-track"
                            role="progressbar"
                            aria-label="پیشرفت زمانی کمپین"
                            aria-valuenow="{{ round($percent) }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        >
                            <div
                                class="pd-progress-fill {{ $isExpired ? 'is-complete' : ($isFuture ? 'is-future' : '') }}"
                                style="width: {{ $percent }}%"
                            ></div>
                        </div>

                        <div class="pd-progress-caption">
                            {{ $progressLabel }}
                        </div>

                    </div>

                    {{-- Actions --}}

                    <div class="pd-card-actions">

                        <a href="{{ route('admin.presale.campaigns.show', $campaign) }}"
                           class="pd-action pd-action-manage">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z"/>
                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                            </svg>

                            مدیریت

                        </a>

                        @if($campaign->status === 'active')

                            <form
                                action="{{ route('admin.presale.campaigns.close', $campaign) }}"
                                method="POST"
                                class="inline"
                                onsubmit="return confirm('آیا از بستن کمپین «{{ addslashes($campaign->title) }}» اطمینان دارید؟ پس از بستن، امکان قیمت‌گذاری نهایی فعال می‌شود.');"
                            >
                                @csrf

                                <button type="submit" class="pd-action pd-action-close">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    بستن کمپین
                                </button>
                            </form>

                        @endif

                        <a href="{{ route('admin.presale.campaigns.edit', $campaign) }}"
                           class="pd-action pd-action-edit">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v13h16V5h-5m-7 8l7-7 3 3-7 7H8v-3z"/>
                            </svg>

                            ویرایش

                        </a>

                    </div>

                </article>

            @empty

                <div class="pd-empty" id="campaignEmpty">
                    <div class="pd-empty-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="1.7"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>

                    <h3>هنوز کمپینی ایجاد نشده است</h3>
                    <p>برای شروع، اولین کمپین پیش‌فروش خود را ایجاد کنید.</p>

                    <a href="{{ route('admin.presale.campaigns.create') }}"
                       class="pd-create-btn"
                       style="background:#635bdb;color:white;">

                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                        ساخت اولین کمپین

                    </a>
                </div>

            @endforelse

            {{-- No client-side search results --}}

            <div class="pd-empty pd-empty-filter" id="filterEmpty">
                <div class="pd-empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>
                </div>

                <h3>کمپینی پیدا نشد!</h3>
                <p>با عبارت یا فیلتر دیگری جستجو کنید.</p>

                <button type="button" id="resetFilters" class="pd-action pd-action-manage">
                    پاک کردن فیلترها
                </button>
            </div>

        </div>

        {{-- Pagination --}}

        @if($campaigns->hasPages())
            <div class="pd-pagination">
                {{ $campaigns->links() }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const search = document.getElementById('campaignSearch');
    const filter = document.getElementById('campaignFilter');
    const grid = document.getElementById('campaignGrid');
    const results = document.getElementById('campaignResults');
    const filterEmpty = document.getElementById('filterEmpty');
    const resetBtn = document.getElementById('resetFilters');

    if (!search || !filter || !grid) return;

    const cards = Array.from(
        grid.querySelectorAll('.pd-campaign-card')
    );

    const normalize = value => {
        return String(value || '')
            .toLowerCase()
            .replace(/ي/g, 'ی')
            .replace(/ك/g, 'ک')
            .replace(/\s+/g, ' ')
            .trim();
    };

    function applyFilters() {
        const query = normalize(search.value);
        const selected = filter.value;

        let visible = 0;

        cards.forEach(card => {
            const title = normalize(card.dataset.title);
            const status = card.dataset.status;
            const extended = card.dataset.extended === 'yes';

            const matchesSearch = !query || title.includes(query);

            const matchesStatus =
                selected === 'all' ||
                (selected === 'active' && status === 'active') ||
                (selected === 'closed' && status === 'closed') ||
                (selected === 'extended' && extended);

            const show = matchesSearch && matchesStatus;

            card.classList.toggle('pd-hidden', !show);

            if (show) visible++;
        });

        if (results) {
            results.textContent =
                'نمایش ' + visible.toLocaleString('fa-IR') +
                ' از ' + cards.length.toLocaleString('fa-IR') +
                ' کمپین این صفحه';
        }

        if (filterEmpty) {
            filterEmpty.style.display =
                cards.length > 0 && visible === 0 ? 'block' : 'none';
        }
    }

    search.addEventListener('input', applyFilters);
    filter.addEventListener('change', applyFilters);

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            search.value = '';
            filter.value = 'all';
            applyFilters();
            search.focus();
        });
    }

    applyFilters();

})();
</script>
@endpush
