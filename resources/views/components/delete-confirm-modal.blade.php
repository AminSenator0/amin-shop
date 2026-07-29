<div
    x-data="deleteConfirmModal()"
    x-on:show-delete-confirm.window="show($event.detail)"
    x-on:keydown.escape.window="open && close()"
    class="delete-confirm-root"
    :class="open && 'delete-confirm-root--open'"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="delete-confirm-backdrop"
        @click="close()"
        aria-hidden="true"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="delete-confirm-dialog"
        role="alertdialog"
        aria-modal="true"
        :aria-labelledby="$id('delete-confirm-title')"
        :aria-describedby="$id('delete-confirm-message')"
        @click.stop
    >
        <button type="button" class="delete-confirm-close" @click="close()" aria-label="بستن">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="delete-confirm-body">
            <div class="delete-confirm-icon" :class="variant === 'warning' ? 'delete-confirm-icon--warning' : 'delete-confirm-icon--danger'">
                <svg x-show="variant === 'warning'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <svg x-show="variant !== 'warning'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </div>

            <h3 :id="$id('delete-confirm-title')" class="delete-confirm-title" x-text="title"></h3>
            <p :id="$id('delete-confirm-message')" class="delete-confirm-message" x-text="message"></p>
        </div>

        <div class="delete-confirm-actions">
            <button type="button" class="delete-confirm-btn delete-confirm-btn-cancel" @click="close()" x-text="cancelText"></button>
            <button
                type="button"
                class="delete-confirm-btn delete-confirm-btn-confirm"
                :class="variant === 'warning' ? 'delete-confirm-btn-confirm--warning' : 'delete-confirm-btn-confirm--danger'"
                @click="confirm()"
                x-text="confirmText"
            ></button>
        </div>
    </div>
</div>
