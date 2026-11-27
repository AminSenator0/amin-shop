
@extends('layouts.admin')

@section('title', 'ویرایش کمپین پیش‌فروش')

@push('styles')
<style>
    .edit-campaign-page {
        --primary: #635bdb;
        --primary-dark: #5046c5;
        --text: #172033;
        --muted: #8490a5;
        --border: #e8ebf2;
        direction: rtl;
        padding: 28px 16px 48px;
        color: var(--text);
    }

    .edit-campaign-page * {
        box-sizing: border-box;
    }

    .edit-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .edit-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 25px;
    }

    .edit-heading {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .edit-back {
        width: 45px;
        height: 45px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #69758b;
        background: white;
        border: 1px solid var(--border);
        transition: .2s;
        text-decoration: none;
        flex-shrink: 0;
    }

    .edit-back:hover {
        color: var(--primary);
        border-color: #c8c3ff;
        background: #f7f6ff;
        transform: translateX(3px);
    }

    .edit-back svg {
        width: 20px;
        height: 20px;
    }

    .edit-heading h1 {
        font-size: 22px;
        font-weight: 850;
        margin: 0 0 6px;
        color: #182238;
    }

    .edit-heading p {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
    }

    .edit-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 13px;
        border-radius: 12px;
        background: #fff7e9;
        border: 1px solid #ffebc5;
        color: #b7791f;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .edit-badge svg {
        width: 15px;
        height: 15px;
    }

    .edit-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 22px;
        box-shadow: 0 12px 45px rgba(31,41,55,.055);
        overflow: hidden;
    }

    .edit-card-header {
        padding: 25px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #edf0f5;
        gap: 15px;
    }

    .edit-card-header h2 {
        font-size: 16px;
        font-weight: 850;
        margin: 0 0 6px;
        color: #27334a;
    }

    .edit-card-header p {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
    }

    .edit-section-icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        background: #f0efff;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .edit-section-icon svg {
        width: 21px;
        height: 21px;
    }

    .edit-body {
        padding: 30px;
    }

    .edit-info {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 16px;
        border-radius: 14px;
        background: #f3f2ff;
        border: 1px solid #e4e2ff;
        margin-bottom: 28px;
    }

    .edit-info svg {
        width: 21px;
        height: 21px;
        color: var(--primary);
        flex-shrink: 0;
    }

    .edit-info strong {
        display: block;
        font-size: 12px;
        color: #5148ad;
        margin-bottom: 5px;
    }

    .edit-info p {
        font-size: 11px;
        line-height: 1.9;
        color: #73769a;
        margin: 0;
    }

    .edit-field {
        margin-bottom: 25px;
        min-width: 0;
    }

    .edit-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 800;
        color: #27334a;
    }

    .edit-required {
        color: #ef5268;
        margin-right: 3px;
    }

    .edit-hint {
        font-size: 10px;
        color: #9ba5b6;
        font-weight: 500;
    }

    .edit-input {
        width: 100%;
        height: 53px;
        padding: 0 16px;
        border: 1.5px solid #e5e9f1;
        border-radius: 13px;
        outline: none;
        background: #fafbfe;
        color: #202b40;
        font-family: inherit;
        font-size: 13px;
        transition: .2s;
    }

    .edit-input:hover {
        border-color: #c9c6f5;
    }

    .edit-input:focus {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99,91,219,.10);
    }

    .edit-input.is-invalid {
        border-color: #ef5268;
        background: #fffafb;
    }

    .edit-input[type="datetime-local"] {
        direction: ltr;
        text-align: right;
        cursor: pointer;
    }

    .edit-counter {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        color: #98a1b2;
        font-size: 11px;
    }

    .edit-counter strong {
        color: var(--primary);
    }

    .edit-date-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .edit-date-preview {
        min-height: 22px;
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 9px;
        font-size: 11px;
        color: #6c64cf;
    }

    .edit-date-preview svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .edit-error {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #e34c62;
        font-size: 11px;
        margin-top: 8px;
    }

    .edit-summary {
        margin-top: 5px;
        padding: 18px;
        border-radius: 15px;
        background: #f8f9fc;
        border: 1px solid #edf0f5;
    }

    .edit-summary-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 850;
        color: #39445b;
        margin-bottom: 16px;
    }

    .edit-summary-title svg {
        width: 18px;
        height: 18px;
        color: var(--primary);
    }

    .edit-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .edit-summary-label {
        font-size: 10px;
        color: #98a1b2;
        margin-bottom: 7px;
    }

    .edit-summary-value {
        font-size: 12px;
        font-weight: 800;
        color: #344057;
        overflow-wrap: anywhere;
    }

    .edit-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 22px 30px;
        background: #fcfcfe;
        border-top: 1px solid #edf0f5;
    }

    .edit-footer-note {
        font-size: 11px;
        color: #929bad;
        line-height: 1.8;
    }

    .edit-actions {
        display: flex;
        gap: 10px;
    }

    .edit-btn {
        height: 47px;
        padding: 0 22px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .edit-btn svg {
        width: 17px;
        height: 17px;
    }

    .edit-btn-primary {
        color: white;
        background: linear-gradient(135deg,#635bdb,#8275ee);
        box-shadow: 0 5px 16px rgba(99,91,219,.22);
    }

    .edit-btn-primary:hover {
        color: white;
        background: linear-gradient(135deg,#5046c5,#6d60d9);
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(99,91,219,.30);
    }

    .edit-btn-secondary {
        color: #657087;
        background: white;
        border-color: #e5e9f1;
    }

    .edit-btn-secondary:hover {
        background: #f3f5f9;
        color: #343f55;
    }

    .edit-btn:disabled {
        opacity: .65;
        cursor: not-allowed;
        transform: none;
    }

    @media(max-width:640px) {
        .edit-campaign-page {
            padding: 18px 10px 30px;
        }

        .edit-heading h1 {
            font-size: 17px;
        }

        .edit-heading p {
            font-size: 10px;
        }

        .edit-badge {
            padding: 7px 9px;
            font-size: 10px;
        }

        .edit-card {
            border-radius: 17px;
        }

        .edit-card-header {
            padding: 20px;
        }

        .edit-body {
            padding: 20px 16px;
        }

        .edit-date-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .edit-footer {
            padding: 18px 16px;
            flex-direction: column;
            align-items: stretch;
        }

        .edit-footer-note {
            text-align: center;
        }

        .edit-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .edit-btn {
            padding: 0 10px;
            width: 100%;
        }
    }

    @media(prefers-reduced-motion:reduce) {
        .edit-campaign-page *,
        .edit-campaign-page *::before,
        .edit-campaign-page *::after {
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')

<div class="edit-campaign-page">
    <div class="edit-wrapper">

        <div class="edit-header">

            <div class="edit-heading">

                <a href="{{ route('admin.presale.campaigns.index') }}"
                   class="edit-back"
                   aria-label="بازگشت به لیست کمپین‌ها">

                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>

                </a>

                <div>
                    <h1>ویرایش کمپین</h1>
                    <p>اطلاعات کمپین پیش‌فروش خود را مدیریت کنید.</p>
                </div>

            </div>

            <div class="edit-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 16H9v-2.828l8.586-8.586z"/>
                </svg>
                حالت ویرایش
            </div>

        </div>

        <div class="edit-card">

            <div class="edit-card-header">

                <div>
                    <h2>اطلاعات کمپین</h2>
                    <p>تغییرات مورد نظر خود را در فیلدهای زیر اعمال کنید.</p>
                </div>

                <div class="edit-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v13h16V5h-5M9 3h6v4H9z"/>
                    </svg>
                </div>

            </div>

            <form
                action="{{ route('admin.presale.campaigns.update', ['presaleCampaign' => $presaleCampaign]) }}"
                method="POST"
                id="editCampaignForm"
            >

                @csrf
                @method('PUT')

                <div class="edit-body">

                    <div class="edit-info">

                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>

                        <div>
                            <strong>ویرایش اطلاعات کمپین</strong>
                            <p>
                                پس از ذخیره، اطلاعات جدید جایگزین اطلاعات قبلی خواهند شد.
                                لطفاً پیش از ثبت تغییرات، تاریخ شروع و پایان را بررسی کنید.
                            </p>
                        </div>

                    </div>

                    {{-- Title --}}

                    <div class="edit-field">

                        <label class="edit-label" for="title">
                            <span>عنوان کمپین <span class="edit-required">*</span></span>
                            <span class="edit-hint">حداکثر ۱۵۰ کاراکتر</span>
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            maxlength="150"
                            value="{{ old('title', $presaleCampaign->title) }}"
                            class="edit-input @error('title') is-invalid @enderror"
                            placeholder="عنوان کمپین را وارد کنید..."
                            autocomplete="off"
                            required
                        >

                        <div class="edit-counter">
                            <span>تعداد کاراکترها</span>
                            <span><strong id="titleCount">۰</strong> / ۱۵۰</span>
                        </div>

                        @error('title')
                            <div class="edit-error">{{ $message }}</div>
                        @enderror

                    </div>

                    {{-- Dates --}}

                    <div class="edit-date-grid">

                        <div class="edit-field">

                            <label class="edit-label" for="starts_at">
                                <span>تاریخ شروع <span class="edit-required">*</span></span>
                            </label>

                            <input
                                type="datetime-local"
                                id="starts_at"
                                name="starts_at"
                                value="{{ old('starts_at', $presaleCampaign->starts_at->format('Y-m-d\TH:i')) }}"
                                class="edit-input @error('starts_at') is-invalid @enderror"
                                required
                            >

                            <div class="edit-date-preview">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="startPersian"></span>
                            </div>

                            @error('starts_at')
                                <div class="edit-error">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="edit-field">

                            <label class="edit-label" for="ends_at">
                                <span>تاریخ پایان <span class="edit-required">*</span></span>
                            </label>

                            <input
                                type="datetime-local"
                                id="ends_at"
                                name="ends_at"
                                value="{{ old('ends_at', $presaleCampaign->ends_at->format('Y-m-d\TH:i')) }}"
                                class="edit-input @error('ends_at') is-invalid @enderror"
                                required
                            >

                            <div class="edit-date-preview">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="endPersian"></span>
                            </div>

                            @error('ends_at')
                                <div class="edit-error">{{ $message }}</div>
                            @enderror

                        </div>

                    </div>

                    {{-- Live summary --}}

                    <div class="edit-summary">

                        <div class="edit-summary-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            پیش‌نمایش اطلاعات جدید
                        </div>

                        <div class="edit-summary-grid">

                            <div>
                                <div class="edit-summary-label">عنوان کمپین</div>
                                <div class="edit-summary-value" id="summaryTitle"></div>
                            </div>

                            <div>
                                <div class="edit-summary-label">وضعیت اطلاعات</div>
                                <div class="edit-summary-value" id="summaryStatus">در حال بررسی</div>
                            </div>

                            <div>
                                <div class="edit-summary-label">شروع کمپین</div>
                                <div class="edit-summary-value" id="summaryStart"></div>
                            </div>

                            <div>
                                <div class="edit-summary-label">پایان کمپین</div>
                                <div class="edit-summary-value" id="summaryEnd"></div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="edit-footer">

                    <div class="edit-footer-note">
                        <span class="edit-required">*</span>
                        فیلدهای ستاره‌دار الزامی هستند.
                    </div>

                    <div class="edit-actions">

                        <a href="{{ route('admin.presale.campaigns.index') }}"
                           class="edit-btn edit-btn-secondary">
                            انصراف
                        </a>

                        <button type="submit"
                                id="submitBtn"
                                class="edit-btn edit-btn-primary">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>

                            ذخیره تغییرات

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

    const form = document.getElementById('editCampaignForm');

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

    const dateFormatter = new Intl.DateTimeFormat(
        'fa-IR-u-ca-persian',
        {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }
    );

    const timeFormatter = new Intl.DateTimeFormat('fa-IR', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });

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

        if (
            date.getFullYear() !== dateParts[0] ||
            date.getMonth() !== dateParts[1] - 1 ||
            date.getDate() !== dateParts[2]
        ) {
            return null;
        }

        return Number.isNaN(date.getTime()) ? null : date;
    }

    function formatDate(value) {
        const date = parseLocalDate(value);

        if (!date) return 'تعیین نشده';

        return dateFormatter.format(date) +
            '، ساعت ' + timeFormatter.format(date);
    }

    function updateTitle() {
        titleCount.textContent = title.value.length.toLocaleString('fa-IR');

        summaryTitle.textContent = title.value.trim() || 'بدون عنوان';

        updateStatus();
    }

    function updateDates() {
        const start = parseLocalDate(startsAt.value);
        const end = parseLocalDate(endsAt.value);

        startPersian.textContent = start
            ? formatDate(startsAt.value)
            : 'تاریخ شمسی مشخص نشده است.';

        endPersian.textContent = end
            ? formatDate(endsAt.value)
            : 'تاریخ شمسی مشخص نشده است.';

        summaryStart.textContent = formatDate(startsAt.value);
        summaryEnd.textContent = formatDate(endsAt.value);

        if (start) {
            const minDate = new Date(
                start.getTime() - start.getTimezoneOffset() * 60000
            ).toISOString().slice(0, 16);

            endsAt.min = minDate;
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

        summaryStatus.textContent = 'آماده ذخیره تغییرات';
    }

    title.addEventListener('input', updateTitle);

    startsAt.addEventListener('input', updateDates);
    startsAt.addEventListener('change', updateDates);

    endsAt.addEventListener('input', updateDates);
    endsAt.addEventListener('change', updateDates);

    form.addEventListener('submit', function (event) {
        const start = parseLocalDate(startsAt.value);
        const end = parseLocalDate(endsAt.value);

        if (!title.value.trim()) {
            event.preventDefault();
            title.focus();
            return;
        }

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

        submitBtn.disabled = true;
        submitBtn.style.opacity = '.7';
    });

    updateTitle();
    updateDates();

})();
</script>
@endpush
