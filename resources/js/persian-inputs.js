import $ from 'jquery';
import 'select2/dist/css/select2.min.css';
import '../css/select2-rtl.css';
import 'persian-datepicker/dist/css/persian-datepicker.min.css';

window.$ = window.jQuery = $;

let pluginsReady;

function ensureJqueryPlugins() {
    pluginsReady ??= import('select2')
        .then(() => import('persian-date/dist/persian-date.min.js'))
        .then(() => import('persian-datepicker/dist/js/persian-datepicker.min.js'));

    return pluginsReady;
}

function toEnglishDigits(value) {
    if (!value) return value;
    const persian = '۰۱۲۳۴۵۶۷۸۹';
    const arabic = '٠١٢٣٤٥٦٧٨٩';
    return String(value).replace(/[۰-۹٠-٩]/g, (d) => {
        let i = persian.indexOf(d);
        if (i >= 0) return String(i);
        i = arabic.indexOf(d);
        return i >= 0 ? String(i) : d;
    });
}

function toPersianDigits(value) {
    if (!value) return value;
    return String(value).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function normalizeMobile(value) {
    let phone = toEnglishDigits(value).replace(/\D+/g, '');

    if (phone.startsWith('0098')) {
        phone = phone.slice(4);
    } else if (phone.startsWith('98') && phone.length >= 12) {
        phone = phone.slice(2);
    }

    if (phone.startsWith('9') && phone.length === 10) {
        phone = '0' + phone;
    }

    return phone.slice(0, 11);
}

function normalizeIranPhone(value) {
    let phone = toEnglishDigits(value).replace(/\D+/g, '');

    if (phone.startsWith('09') || (phone.startsWith('9') && phone.length <= 10)) {
        return normalizeMobile(phone);
    }

    return phone.slice(0, 11);
}

function formatPriceInput(value) {
    const digits = toEnglishDigits(value).replace(/[^\d]/g, '');
    if (!digits) return '';
    return toPersianDigits(Number(digits).toLocaleString('en-US'));
}

function initSelect2() {
    $('select').not('[data-no-select2]').each(function () {
        const $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) return;

        const autoSubmit = $el.is('[data-auto-submit]')
            || ($el.attr('onchange') || '').includes('form.submit');

        if (autoSubmit) {
            $el.removeAttr('onchange');
        }

        $el.select2({
            dir: 'rtl',
            width: '100%',
            language: {
                noResults: () => 'نتیجه‌ای یافت نشد',
                searching: () => 'در حال جستجو...',
            },
        });

        if (autoSubmit) {
            $el.on('select2:select select2:clear', function () {
                $el.closest('form')[0]?.submit();
            });
        }
    });
}

function initJalaliDatepicker() {
    const selector = [
        '[data-jalali-date]',
        '.admin-body input[type="text"][name$="_date"]',
        '.admin-body input[type="text"][name$="_at"]',
        '.admin-body input[type="text"][name^="date_"]',
    ].join(', ');

    $(selector).not('[type="hidden"], [data-no-jalali-date]').each(function () {
        const $el = $(this);
        if ($el.data('datepicker')) return;

        $el.attr({
            autocomplete: 'off',
            dir: $el.attr('dir') || 'ltr',
            inputmode: $el.attr('inputmode') || 'numeric',
        });

        $el.persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: Boolean($el.val()),
            persianDigit: true,
            observer: true,
            calendar: {
                persian: { locale: 'fa' },
            },
            toolbox: {
                calendarSwitch: { enabled: false },
            },
        });

        const input = $el.get(0);
        input?.closest('form')?.addEventListener('submit', () => {
            input.value = toEnglishDigits(input.value);
        });
    });
}

function initNumericInputs() {
    document.querySelectorAll('[data-numeric-input]').forEach((input) => {
        if (input.dataset.numericInit === 'true') return;
        input.dataset.numericInit = 'true';

        input.addEventListener('input', () => {
            const pos = input.selectionStart;
            const before = input.value.length;
            input.value = toEnglishDigits(input.value).replace(/[^\d]/g, '');

            if (pos !== null && pos !== undefined) {
                const after = input.value.length;
                const nextPos = Math.max(0, pos - (before - after));
                input.setSelectionRange(nextPos, nextPos);
            }
        });

        input.closest('form')?.addEventListener('submit', () => {
            input.value = toEnglishDigits(input.value).replace(/[^\d]/g, '');
        });
    });
}

function initPriceInputs() {
    document.querySelectorAll('[data-price-input]').forEach((input) => {
        if (input.value) {
            input.value = formatPriceInput(input.value);
        }

        input.addEventListener('input', () => {
            input.value = formatPriceInput(input.value);
        });

        input.closest('form')?.addEventListener('submit', () => {
            input.value = toEnglishDigits(input.value).replace(/[^\d]/g, '');
        });
    });
}

function initPhoneInputs() {
    document.querySelectorAll('[data-phone-input], [data-iran-phone-input]').forEach((input) => {
        if (input.dataset.phoneInit === 'true') return;
        input.dataset.phoneInit = 'true';

        const normalize = input.hasAttribute('data-iran-phone-input')
            ? normalizeIranPhone
            : normalizeMobile;

        input.setAttribute('dir', 'ltr');
        input.setAttribute('inputmode', 'numeric');

        input.addEventListener('input', () => {
            input.value = normalize(input.value);
        });

        input.closest('form')?.addEventListener('submit', () => {
            input.value = normalize(input.value);
        });
    });
}

function initFileInputs() {
    document.querySelectorAll('[data-admin-file]').forEach((input) => {
        const nameEl = input.closest('label')?.querySelector('[data-file-name]');
        if (!nameEl) return;

        const emptyText = nameEl.textContent?.trim() || 'فایلی انتخاب نشده';

        input.addEventListener('change', () => {
            if (!input.files?.length) {
                nameEl.textContent = emptyText;
                return;
            }

            if (input.multiple) {
                const count = input.files.length;
                nameEl.textContent = `${toPersianDigits(String(count))} فایل انتخاب شد`;
                return;
            }

            nameEl.textContent = input.files[0].name;
        });
    });
}

async function initPersianInputs() {
    await ensureJqueryPlugins();

    initSelect2();
    initJalaliDatepicker();
    initNumericInputs();
    initPriceInputs();
    initPhoneInputs();
    initFileInputs();
}

export { toEnglishDigits, toPersianDigits, normalizeMobile, formatPriceInput, initPersianInputs };
