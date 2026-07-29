const tokenMeta = () => document.head.querySelector('meta[name="csrf-token"]');

export function syncCsrfTokens() {
    const meta = tokenMeta();
    if (!meta?.content) {
        return;
    }

    document.querySelectorAll('input[name="_token"]').forEach((input) => {
        input.value = meta.content;
    });
}

export function initCsrf() {
    const meta = tokenMeta();
    if (!meta?.content || !window.axios) {
        return;
    }

    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = meta.content;

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            window.location.reload();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            syncCsrfTokens();
        }
    });
}
