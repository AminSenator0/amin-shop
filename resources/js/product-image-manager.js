import { showDeleteConfirm } from './delete-confirm';

document.addEventListener('alpine:init', () => {
    Alpine.data('productImageManager', (config) => ({
        required: config.required,
        items: [],
        primary: config.initialPrimary,
        removedImages: config.removedImages ?? [],
        nextUid: 1,

        init() {
            this.items = (config.initialItems ?? []).map((item) => ({
                ...item,
                uid: this.makeUid(),
            }));

            if (!this.primary && this.items.length > 0) {
                this.primary = this.tokenFor(this.items[0]);
            }

            const form = this.$el.closest('form');

            if (form) {
                form.addEventListener('submit', () => this.prepareSubmit(), true);
            }
        },

        makeUid() {
            return `item-${this.nextUid++}`;
        },

        toPersian(value) {
            return String(value).replace(/[0-9]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]);
        },

        tokenFor(item) {
            if (item.type === 'existing') return `existing:${item.id}`;
            if (item.type === 'cached') return `cached:${item.key}`;
            return `new:${item.newIndex}`;
        },

        isPrimary(item) {
            return this.primary === this.tokenFor(item);
        },

        setPrimary(item) {
            this.primary = this.tokenFor(item);
        },

        addFiles(event) {
            const files = Array.from(event.target.files ?? []);

            files.forEach((file) => {
                const newIndex = this.items.filter((item) => item.type === 'new').length;
                this.items.push({
                    uid: this.makeUid(),
                    type: 'new',
                    file,
                    newIndex,
                    url: URL.createObjectURL(file),
                    name: file.name,
                });
            });

            if (!this.primary && this.items.length > 0) {
                this.setPrimary(this.items[0]);
            }

            event.target.value = '';
        },

        removeItem(item, index) {
            showDeleteConfirm({
                title: 'حذف تصویر',
                message: 'آیا از حذف این تصویر مطمئن هستید؟',
                callback: () => this.performRemoveItem(item, index),
            });
        },

        performRemoveItem(item, index) {
            if (item.type === 'existing') {
                this.removedImages.push(item.id);
            }

            if (item.type === 'new' && item.url?.startsWith('blob:')) {
                URL.revokeObjectURL(item.url);
            }

            this.items.splice(index, 1);

            if (this.isPrimary(item)) {
                this.primary = this.items.length ? this.tokenFor(this.items[0]) : null;
            }
        },

        moveLeft(index) {
            if (index === 0) return;
            const [moved] = this.items.splice(index, 1);
            this.items.splice(index - 1, 0, moved);
        },

        moveRight(index) {
            if (index >= this.items.length - 1) return;
            const [moved] = this.items.splice(index, 1);
            this.items.splice(index + 1, 0, moved);
        },

        prepareSubmit() {
            let newIndex = 0;

            this.items.forEach((item) => {
                if (item.type === 'new') {
                    item.newIndex = newIndex++;
                }
            });

            this.$refs.sortInput.value = JSON.stringify(
                this.items.map((item) => this.tokenFor(item))
            );
            this.$refs.primaryInput.value = this.primary ?? '';

            const dt = new DataTransfer();

            this.items.forEach((item) => {
                if (item.type === 'new') {
                    dt.items.add(item.file);
                }
            });

            this.$refs.fileInput.files = dt.files;
        },
    }));
});
