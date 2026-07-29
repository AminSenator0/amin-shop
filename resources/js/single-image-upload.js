document.addEventListener('alpine:init', () => {
    window.Alpine.data('singleImageUpload', (config) => ({
        preview: config.existingUrl || null,
        hasExisting: Boolean(config.existingUrl),
        removed: false,
        fileName: '',

        pick() {
            this.$refs.fileInput.click();
        },

        onFileChange(event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            if (this.preview?.startsWith('blob:')) {
                URL.revokeObjectURL(this.preview);
            }

            this.preview = URL.createObjectURL(file);
            this.fileName = file.name;
            this.removed = false;
        },

        remove() {
            if (this.preview?.startsWith('blob:')) {
                URL.revokeObjectURL(this.preview);
            }

            this.preview = null;
            this.fileName = '';
            this.$refs.fileInput.value = '';

            if (this.hasExisting) {
                this.removed = true;
            }
        },
    }));
});
