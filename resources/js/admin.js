import { initPersianInputs } from './persian-inputs';

function initAutoSubmitSelects() {
    document.querySelectorAll('select[data-auto-submit]').forEach((select) => {
        if (select.dataset.autoSubmitBound === 'true') {
            return;
        }

        select.dataset.autoSubmitBound = 'true';
        select.addEventListener('change', () => select.form?.requestSubmit());
    });
}

async function initAdminPanel() {
    try {
        await initPersianInputs();
    } catch (error) {
        console.error('Failed to initialize admin Persian inputs:', error);
    }

    initAutoSubmitSelects();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminPanel);
} else {
    initAdminPanel();
}
