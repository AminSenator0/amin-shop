const DEFAULTS = {
    title: 'تأیید حذف',
    message: 'آیا از حذف این مورد مطمئن هستید؟ این عمل قابل بازگشت نیست.',
    confirmText: 'بله، حذف شود',
    cancelText: 'انصراف',
    variant: 'danger',
};

let modalController = null;
const pendingQueue = [];

function getFormMethod(form) {
    const methodInput = form.querySelector('input[name="_method"]');

    if (methodInput) {
        return methodInput.value.toUpperCase();
    }

    return (form.getAttribute('method') || 'GET').toUpperCase();
}

function resolveConfirmOptions(source, form) {
    const element = source || form;

    return {
        title: element?.dataset?.confirmTitle || form?.dataset?.confirmTitle || DEFAULTS.title,
        message: element?.dataset?.confirmMessage || form?.dataset?.confirmMessage || DEFAULTS.message,
        confirmText: element?.dataset?.confirmText || form?.dataset?.confirmText || DEFAULTS.confirmText,
        cancelText: element?.dataset?.cancelText || form?.dataset?.cancelText || DEFAULTS.cancelText,
        variant: element?.dataset?.confirmVariant || form?.dataset?.confirmVariant || DEFAULTS.variant,
    };
}

function flushPendingQueue() {
    if (!modalController) {
        return;
    }

    while (pendingQueue.length > 0) {
        modalController.show(pendingQueue.shift());
    }
}

export function showDeleteConfirm(detail = {}) {
    if (modalController) {
        modalController.show(detail);

        return;
    }

    pendingQueue.push(detail);
    window.dispatchEvent(new CustomEvent('show-delete-confirm', { detail }));
}

function findForm(element) {
    if (!element) {
        return null;
    }

    if (element instanceof HTMLFormElement) {
        return element;
    }

    if (element.form instanceof HTMLFormElement) {
        return element.form;
    }

    const closest = element.closest('form');

    return closest instanceof HTMLFormElement ? closest : null;
}

function shouldSkipForm(form) {
    return !form || form.dataset.noDeleteConfirm !== undefined;
}

function submitForm(form, submitter = null) {
    form.dataset.deleteConfirmed = '1';

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit(submitter ?? undefined);
    } else {
        form.submit();
    }
}

function interceptDeleteAction(event, trigger) {
    const form = findForm(trigger);

    if (shouldSkipForm(form)) {
        return false;
    }

    if (form && form.dataset.deleteConfirmed === '1') {
        return false;
    }

    const isDeleteForm = form && getFormMethod(form) === 'DELETE';
    const isMarkedTrigger = trigger.hasAttribute('data-delete-confirm');

    if (!isDeleteForm && !isMarkedTrigger) {
        return false;
    }

    event.preventDefault();
    event.stopPropagation();

    const options = resolveConfirmOptions(trigger, form);

    showDeleteConfirm({
        form: isDeleteForm ? form : null,
        ...options,
        callback: () => {
            if (form) {
                submitForm(form, trigger instanceof HTMLButtonElement || trigger instanceof HTMLInputElement ? trigger : null);
            } else {
                trigger.dispatchEvent(new CustomEvent('delete-confirmed', { bubbles: true }));
            }
        },
    });

    return true;
}

export function initDeleteConfirm() {
    document.addEventListener('alpine:init', () => {
        Alpine.data('deleteConfirmModal', () => ({
            open: false,
            title: DEFAULTS.title,
            message: DEFAULTS.message,
            confirmText: DEFAULTS.confirmText,
            cancelText: DEFAULTS.cancelText,
            variant: DEFAULTS.variant,
            pendingForm: null,
            pendingSubmitter: null,
            pendingCallback: null,

            init() {
                modalController = this;
                this.open = false;
                document.body.classList.remove('overflow-hidden');

                this.$watch('open', (value) => {
                    document.body.classList.toggle('overflow-hidden', value);
                });

                flushPendingQueue();
            },

            destroy() {
                if (modalController === this) {
                    modalController = null;
                }
            },

            show(detail = {}) {
                this.pendingForm = detail.form ?? null;
                this.pendingSubmitter = detail.submitter ?? null;
                this.pendingCallback = detail.callback ?? null;
                this.title = detail.title ?? DEFAULTS.title;
                this.message = detail.message ?? DEFAULTS.message;
                this.confirmText = detail.confirmText ?? DEFAULTS.confirmText;
                this.cancelText = detail.cancelText ?? DEFAULTS.cancelText;
                this.variant = detail.variant ?? DEFAULTS.variant;
                this.open = true;
            },

            confirm() {
                if (this.pendingCallback) {
                    this.pendingCallback();
                } else if (this.pendingForm) {
                    submitForm(this.pendingForm, this.pendingSubmitter);
                }

                this.close();
            },

            close() {
                this.open = false;
                this.pendingForm = null;
                this.pendingSubmitter = null;
                this.pendingCallback = null;
            },
        }));
    });

    document.addEventListener('click', (event) => {
        const submitter = event.target.closest('button[type="submit"], input[type="submit"]');

        if (submitter && interceptDeleteAction(event, submitter)) {
            return;
        }

        const markedTrigger = event.target.closest('[data-delete-confirm]');

        if (markedTrigger) {
            interceptDeleteAction(event, markedTrigger);
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.deleteConfirmed === '1') {
            delete form.dataset.deleteConfirmed;

            return;
        }

        if (shouldSkipForm(form) || getFormMethod(form) !== 'DELETE') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        showDeleteConfirm({
            form,
            submitter: event.submitter ?? null,
            ...resolveConfirmOptions(event.submitter, form),
            callback: () => submitForm(form, event.submitter ?? null),
        });
    }, true);
}
