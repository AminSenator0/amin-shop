
@extends('layouts.admin')

@section('title', 'ساخت کمپین پیش‌فروش')

@push('styles')
<style>
    .campaign-page {
        --campaign-primary: #635bdb;
        --campaign-primary-dark: #5046c5;
        --campaign-text: #172033;
        --campaign-muted: #8490a5;
        --campaign-border: #e8ebf2;
        --campaign-surface: #ffffff;
        direction: rtl;
        padding: 28px 16px 48px;
        color: var(--campaign-text);
    }

    .campaign-page * {
        box-sizing: border-box;
    }

    .campaign-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .campaign-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 26px;
    }

    .campaign-heading {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .campaign-icon {
        width: 58px;
        height: 58px;
        border-radius: 19px;
        background: linear-gradient(135deg, #635bdb, #9385f5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 24px rgba(99,91,219,.25);
        flex-shrink: 0;
    }

    .campaign-icon svg {
        width: 27px;
        height: 27px;
    }

    .campaign-heading h1 {
        font-size: 23px;
        font-weight: 850;
        margin: 0 0 7px;
        color: #182238;
    }

    .campaign-heading p {
        color: var(--campaign-muted);
        font-size: 13px;
        margin: 0;
        line-height: 1.8;
    }

    .campaign-step {
        background: #f0efff;
        color: #6258d3;
        border: 1px solid #e1deff;
        padding: 9px 15px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .campaign-card {
        background: var(--campaign-surface);
        border: 1px solid var(--campaign-border);
        border-radius: 22px;
        box-shadow: 0 12px 45px rgba(31,41,55,.055);
        overflow: hidden;
    }

    .campaign-card-header {
        padding: 25px 30px;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .campaign-card-header h2 {
        font-size: 16px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .campaign-card-header p {
        color: var(--campaign-muted);
        font-size: 12px;
        margin: 0;
    }

    .section-number {
        width: 39px;
        height: 39px;
        border-radius: 12px;
        background: #f0efff;
        color: var(--campaign-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        flex-shrink: 0;
    }

    .campaign-body {
        padding: 30px;
    }

    .campaign-info {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        background: linear-gradient(135deg,#f2f4ff,#f8f7ff);
        border: 1px solid #e5e4ff;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .campaign-info svg {
        width: 21px;
        height: 21px;
        color: #6258d3;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .campaign-info strong {
        display: block;
        color: #4540a4;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .campaign-info p {
        color: #626b89;
        font-size: 12px;
        line-height: 2;
        margin: 0;
    }

    .campaign-field {
        margin-bottom: 25px;
        min-width: 0;
    }

    .campaign-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 800;
        color: #27334a;
    }

    .campaign-required {
        color: #ef5268;
        margin-right: 3px;
    }

    .campaign-hint {
        color: #98a1b2;
        font-size: 11px;
        font-weight: 500;
    }

    .campaign-input-wrap {
        position: relative;
    }

    .campaign-input {
        width: 100%;
        height: 53px;
        padding: 0 16px;
        border: 1.5px solid #e5e9f1;
        border-radius: 13px;
        outline: none;
        background: #fafbfe;
        color: #202b40;
        font-size: 13px;
        font-family: inherit;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .campaign-input::placeholder {
        color: #aeb6c5;
    }

    .campaign-input:hover {
        border-color: #c9c6f5;
    }

    .campaign-input:focus {
        background: white;
        border-color: var(--campaign-primary);
        box-shadow: 0 0 0 4px rgba(99,91,219,.10);
    }

    .campaign-input.is-invalid {
        border-color: #f16b7e;
        background: #fffafb;
    }

    .campaign-input[type="datetime-local"] {
        direction: ltr;
        text-align: right;
        padding: 0 14px;
        cursor: pointer;
    }

    .campaign-counter {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
        font-size: 11px;
        color: #98a1b2;
    }

    .campaign-counter strong {
        color: var(--campaign-primary);
        font-weight: 800;
    }

    .campaign-date-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .campaign-date-preview {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 9px;
        font-size: 11px;
        color: #6c64cf;
        min-height: 20px;
    }

    .campaign-date-preview svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .campaign-summary {
        margin-top: 10px;
        background: #f8f9fc;
        border: 1px solid #edf0f5;
        border-radius: 15px;
        padding: 17px;
    }

    .campaign-summary-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 850;
        margin-bottom: 15px;
        color: #39445b;
    }

    .campaign-summary-title svg {
        width: 17px;
        height: 17px;
        color: var(--campaign-primary);
    }

    .campaign-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .summary-item {
        min-width: 0;
    }

    .summary-label {
        font-size: 10px;
        color: #98a1b2;
        margin-bottom: 6px;
    }

    .summary-value {
        font-size: 12px;
        font-weight: 800;
        color: #344057;
        overflow-wrap: anywhere;
    }

    .campaign-error {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #e34c62;
        font-size: 11px;
        margin-top: 8px;
    }

    .campaign-error svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .campaign-form-footer {
        padding: 22px 30px;
        border-top: 1px solid #edf0f5;
        background: #fcfcfe;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .campaign-footer-note {
        font-size: 11px;
        color: #929bad;
        line-height: 1.8;
    }

    .campaign-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .campaign-btn {
        height: 47px;
        padding: 0 21px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
    }

    .campaign-btn svg {
        width: 17px;
        height: 17px;
    }

    .campaign-btn-primary {
        color: white;
        background: linear-gradient(135deg,#635bdb,#8275ee);
        box-shadow: 0 5px 16px rgba(99,91,219,.22);
    }

    .campaign-btn-primary:hover {
        color: white;
        background: linear-gradient(135deg,#5046c5,#6d60d9);
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(99,91,219,.30);
    }

    .campaign-btn-secondary {
        color: #657087;
        background: white;
        border-color: #e5e9f1;
    }

    .campaign-btn-secondary:hover {
        color: #343f55;
        background: #f3f5f9;
        border-color: #d5dae5;
    }

    .campaign-btn:disabled {
        opacity: .65;
        cursor: not-allowed;
        transform: none;
    }

    @media(max-width: 640px) {
        .campaign-page {
            padding: 18px 10px 30px;
        }

        .campaign-header {
            align-items: flex-start;
        }

        .campaign-heading {
            gap: 11px;
        }

        .campaign-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
        }

        .campaign-heading h1 {
            font-size: 17px;
        }

        .campaign-heading p {
            font-size: 11px;
        }

        .campaign-step {
            font-size: 10px;
            padding: 7px 9px;
        }

        .campaign-card {
            border-radius: 17px;
        }

        .campaign-card-header {
            padding: 20px;
        }

        .campaign-body {
            padding: 20px 16px;
        }

        .campaign-date-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .campaign-form-footer {
            padding: 18px 16px;
            flex-direction: column;
            align-items: stretch;
        }

        .campaign-footer-note {
            text-align: center;
        }

        .campaign-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .campaign-btn {
            width: 100%;
            padding: 0 10px;
        }
    }

    @media(prefers-reduced-motion: reduce) {
        .campaign-page *,
        .campaign-page *::before,
        .campaign-page *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')

<div class="campaign-page">
    <div class="campaign-wrapper">

        <div class="campaign-header">
            <div class="campaign-heading">
                <div class="campaign-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 4v16m8-8H4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5 5h14v14H5z" opacity=".35"/>
                    </svg>
                </div>

                <div>
                    <h1>ساخت کمپین پیش‌فروش</h1>
                    <p>یک کمپین جدید ایجاد کنید و بازه زمانی آن را مشخص کنید.</p>
                </div>
            </div>

            <div class="campaign-step">
                مرحله ۱ از ۲
            </div>
        </div>

        <div class="campaign-card">

            <div class="campaign-card-header">
                <div>
                    <h2>اطلاعات اصلی کمپین</h2>
                    <p>اطلاعات مورد نیاز برای ایجاد کمپین را وارد کنید.</p>
                </div>

                <div class="section-number">۰۱</div>
            </div>

            <form action="{{ route('admin.presale.campaigns.store') }}"
                  method="POST"
                  id="campaignForm">

                @csrf

                <div class="campaign-body">

                    <div class="campaign-info">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>

                        <div>
                            <strong>قبل از شروع به کار</strong>
                            <p>
                                پس از ایجاد کمپین، می‌توانید محصولات مورد نظر را انتخاب کنید
                                و قیمت پیش‌پرداخت هر کتاب را مشخص کنید.
                                قیمت نهایی و جریمه نیز پس از پایان کمپین تعیین خواهند شد.
                            </p>
                        </div>
                    </div>

                    {{-- Campaign title --}}

                    <div class="campaign-field">

                        <label class="campaign-label" for="title">
                            <span>عنوان کمپین <span class="campaign-required">*</span></span>
                            <span class="campaign-hint">نامی واضح و قابل تشخیص انتخاب کنید</span>
                        </label>

                        <div class="campaign-input-wrap">
                            <input
                                type="text"
                                id="title"
                                name="title"
                                maxlength="150"
                                class="campaign-input @error('title') is-invalid @enderror"
                                placeholder="مثلاً: پیش‌فروش جلدهای جدید هری پاتر"
                                value="{{ old('title') }}"
                                required
                                autocomplete="off"
                            >
                        </div>

                        <div class="campaign-counter">
                            <span>عنوان کمپین</span>
                            <span><strong id="titleCount">۰</strong> / ۱۵۰ کاراکتر</span>
                        </div>

                        @error('title')
                            <div class="campaign-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Dates --}}

                    <div class="campaign-date-grid">

                        <div class="campaign-field">

                            <label class="campaign-label" for="starts_at">
                                <span>زمان شروع کمپین <span class="campaign-required">*</span></span>
                            </label>

                            <input
                                type="datetime-local"
                                id="starts_at"
                                name="starts_at"
                                class="campaign-input @error('starts_at') is-invalid @enderror"
                                value="{{ old('starts_at') }}"
                                required
                            >

                            <div class="campaign-date-preview">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="startPersian">تاریخ شمسی پس از انتخاب نمایش داده می‌شود.</span>
                            </div>

                            @error('starts_at')
                                <div class="campaign-error">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="campaign-field">

                            <label class="campaign-label" for="ends_at">
                                <span>زمان پایان کمپین <span class="campaign-required">*</span></span>
                            </label>

                            <input
                                type="datetime-local"
                                id="ends_at"
                                name="ends_at"
                                class="campaign-input @error('ends_at') is-invalid @enderror"
                                value="{{ old('ends_at') }}"
                                required
                            >

                            <div class="campaign-date-preview">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="endPersian">تاریخ شمسی پس از انتخاب نمایش داده می‌شود.</span>
                            </div>

                            @error('ends_at')
                                <div class="campaign-error">{{ $message }}</div>
                            @enderror

                        </div>

                    </div>

                    {{-- Live summary --}}

                    <div class="campaign-summary">

                        <div class="campaign-summary-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            پیش‌نمایش کمپین
                        </div>

                        <div class="campaign-summary-grid">

                            <div class="summary-item">
                                <div class="summary-label">عنوان کمپین</div>
                                <div class="summary-value" id="summaryTitle">بدون عنوان</div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-label">وضعیت اطلاعات</div>
                                <div class="summary-value" id="summaryStatus">در انتظار تکمیل</div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-label">شروع کمپین</div>
                                <div class="summary-value" id="summaryStart">تعیین نشده</div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-label">پایان کمپین</div>
                                <div class="summary-value" id="summaryEnd">تعیین نشده</div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="campaign-form-footer">

                    <div class="campaign-footer-note">
                        <span class="campaign-required">*</span>
                        تکمیل فیلدهای ستاره‌دار الزامی است.
                    </div>

                    <div class="campaign-actions">

                        <a href="{{ route('admin.presale.campaigns.index') }}"
                           class="campaign-btn campaign-btn-secondary">
                            انصراف
                        </a>

                        <button type="submit"
                                class="campaign-btn campaign-btn-primary"
                                id="submitBtn">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>

                            ایجاد کمپین

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('campaignForm');

    if (!form) return;

    const title = document.getElementById('title');
    const startsAt = document.getElementById('starts_at');
    const endsAt = document.getElementById('ends_at');

    const titleCount = document.getElementById('titleCount');
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryStart = document.getElementById('summaryStart');
    const summaryEnd = document.getElementById('summaryEnd');
    const summaryStatus = document.getElementById('summaryStatus');

    const startPersian = document.getElementById('startPersian');
    const endPersian = document.getElementById('endPersian');

    const submitBtn = document.getElementById('submitBtn');

    const persianFormatter = new Intl.DateTimeFormat(
        'fa-IR-u-ca-persian',
        {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }
    );

    const timeFormatter = new Intl.DateTimeFormat(
        'fa-IR',
        {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }
    );

    function parseLocalDate(value) {
        if (!value) return null;

        const parts = value.split('T');

        if (parts.length !== 2) return null;

        const dateParts = parts[0].split('-').map(Number);
        const timeParts = parts[1].split(':').map(Number);

        if (dateParts.length !== 3 || timeParts.length < 2) {
            return null;
        }

        const date = new Date(
            dateParts[0],
            dateParts[1] - 1,
            dateParts[2],
            timeParts[0],
            timeParts[1]
        );

        return Number.isNaN(date.getTime()) ? null : date;
    }

    function formatPersianDate(value) {
        const date = parseLocalDate(value);

        if (!date) return 'تعیین نشده';

        return persianFormatter.format(date) +
            ' - ساعت ' + timeFormatter.format(date);
    }

    function updateTitle() {
        const value = title.value.trim();

        titleCount.textContent = title.value.length.toLocaleString('fa-IR');

        summaryTitle.textContent = value || 'بدون عنوان';
    }

    function updateDates() {
        const start = parseLocalDate(startsAt.value);
        const end = parseLocalDate(endsAt.value);

        startPersian.textContent = start
            ? formatPersianDate(startsAt.value)
            : 'تاریخ شمسی پس از انتخاب نمایش داده می‌شود.';

        endPersian.textContent = end
            ? formatPersianDate(endsAt.value)
            : 'تاریخ شمسی پس از انتخاب نمایش داده می‌شود.';

        summaryStart.textContent = formatPersianDate(startsAt.value);
        summaryEnd.textContent = formatPersianDate(endsAt.value);

        if (start) {
            const localMin = new Date(start.getTime() - start.getTimezoneOffset() * 60000)
                .toISOString()
                .slice(0, 16);

            endsAt.min = localMin;
        } else {
            endsAt.removeAttribute('min');
        }

        updateStatus();
    }

    function updateStatus() {
        const start = parseLocalDate(startsAt.value);
        const end = parseLocalDate(endsAt.value);

        if (!title.value.trim() || !start || !end) {
            summaryStatus.textContent = 'در انتظار تکمیل';
            return;
        }

        if (end <= start) {
            summaryStatus.textContent = 'بازه زمانی نامعتبر';
            return;
        }

        summaryStatus.textContent = 'اطلاعات آماده ثبت';
    }

    title.addEventListener('input', updateTitle);

    startsAt.addEventListener('change', updateDates);
    startsAt.addEventListener('input', updateDates);

    endsAt.addEventListener('change', updateDates);
    endsAt.addEventListener('input', updateDates);

    form.addEventListener('submit', function (event) {
        const start = parseLocalDate(startsAt.value);
        const end = parseLocalDate(endsAt.value);

        if (!start || !end || end <= start) {
            event.preventDefault();

            summaryStatus.textContent = 'بازه زمانی نامعتبر';

            if (!start) {
                startsAt.focus();
            } else {
                endsAt.focus();
            }

            return;
        }

        if (!title.value.trim()) {
            event.preventDefault();
            title.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.style.opacity = '.7';
    });

    updateTitle();
    updateDates();

})();
</script>
@endpush
