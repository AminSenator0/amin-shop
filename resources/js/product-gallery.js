function touchDistance(touches) {
    const dx = touches[0].clientX - touches[1].clientX;
    const dy = touches[0].clientY - touches[1].clientY;

    return Math.hypot(dx, dy);
}

document.addEventListener('alpine:init', () => {
    Alpine.data('productGallery', (urls = []) => ({
        active: 0,
        lightbox: false,
        urls,

        scale: 1,
        translateX: 0,
        translateY: 0,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOriginX: 0,
        dragOriginY: 0,
        pinchStartDistance: 0,
        pinchStartScale: 1,

        minScale: 1,
        maxScale: 4,

        prev() {
            this.active = (this.active - 1 + this.urls.length) % this.urls.length;
            this.resetZoom();
        },

        next() {
            this.active = (this.active + 1) % this.urls.length;
            this.resetZoom();
        },

        openLightbox(index) {
            this.active = index;
            this.lightbox = true;
            this.resetZoom();
            document.body.style.overflow = 'hidden';
        },

        closeLightbox() {
            this.lightbox = false;
            this.resetZoom();
            document.body.style.overflow = '';
        },

        resetZoom() {
            this.scale = 1;
            this.translateX = 0;
            this.translateY = 0;
            this.isDragging = false;
        },

        zoomIn() {
            this.setScale(Math.min(this.maxScale, this.scale + 0.5));
        },

        zoomOut() {
            this.setScale(Math.max(this.minScale, this.scale - 0.5));
        },

        setScale(nextScale) {
            this.scale = nextScale;

            if (this.scale <= 1) {
                this.translateX = 0;
                this.translateY = 0;
            } else {
                this.clampPan();
            }
        },

        onWheel(event) {
            event.preventDefault();

            const delta = event.deltaY > 0 ? -0.15 : 0.15;
            const nextScale = Math.min(this.maxScale, Math.max(this.minScale, this.scale + delta));

            if (nextScale === this.scale) {
                return;
            }

            if (nextScale <= 1) {
                this.resetZoom();

                return;
            }

            const viewport = this.$refs.lightboxViewport;
            if (! viewport) {
                this.setScale(nextScale);

                return;
            }

            const rect = viewport.getBoundingClientRect();
            const offsetX = event.clientX - rect.left - rect.width / 2;
            const offsetY = event.clientY - rect.top - rect.height / 2;
            const factor = nextScale / this.scale;

            this.translateX = offsetX - factor * (offsetX - this.translateX);
            this.translateY = offsetY - factor * (offsetY - this.translateY);
            this.scale = nextScale;
            this.clampPan();
        },

        onDoubleClick(event) {
            if (this.scale > 1) {
                this.resetZoom();

                return;
            }

            const viewport = this.$refs.lightboxViewport;
            const targetScale = 2.5;

            if (! viewport) {
                this.setScale(targetScale);

                return;
            }

            const rect = viewport.getBoundingClientRect();
            const offsetX = event.clientX - rect.left - rect.width / 2;
            const offsetY = event.clientY - rect.top - rect.height / 2;
            const factor = targetScale / this.scale;

            this.translateX = offsetX - factor * (offsetX - this.translateX);
            this.translateY = offsetY - factor * (offsetY - this.translateY);
            this.scale = targetScale;
            this.clampPan();
        },

        pointerDown(event) {
            if (event.button !== undefined && event.button !== 0) {
                return;
            }

            if (this.scale <= 1) {
                return;
            }

            event.preventDefault();
            this.isDragging = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.dragOriginX = this.translateX;
            this.dragOriginY = this.translateY;
        },

        pointerMove(event) {
            if (! this.isDragging) {
                return;
            }

            event.preventDefault();
            this.translateX = this.dragOriginX + (event.clientX - this.dragStartX);
            this.translateY = this.dragOriginY + (event.clientY - this.dragStartY);
            this.clampPan();
        },

        pointerUp() {
            this.isDragging = false;
        },

        touchStart(event) {
            if (event.touches.length === 2) {
                event.preventDefault();
                this.isDragging = false;
                this.pinchStartDistance = touchDistance(event.touches);
                this.pinchStartScale = this.scale;

                return;
            }

            if (event.touches.length === 1 && this.scale > 1) {
                this.isDragging = true;
                this.dragStartX = event.touches[0].clientX;
                this.dragStartY = event.touches[0].clientY;
                this.dragOriginX = this.translateX;
                this.dragOriginY = this.translateY;
            }
        },

        touchMove(event) {
            if (event.touches.length === 2) {
                event.preventDefault();
                const distance = touchDistance(event.touches);
                const nextScale = this.pinchStartScale * (distance / this.pinchStartDistance);
                this.setScale(Math.min(this.maxScale, Math.max(this.minScale, nextScale)));

                return;
            }

            if (! this.isDragging || event.touches.length !== 1) {
                return;
            }

            event.preventDefault();
            this.translateX = this.dragOriginX + (event.touches[0].clientX - this.dragStartX);
            this.translateY = this.dragOriginY + (event.touches[0].clientY - this.dragStartY);
            this.clampPan();
        },

        touchEnd(event) {
            if (event.touches.length < 2) {
                this.pinchStartDistance = 0;
            }

            if (event.touches.length === 0) {
                this.isDragging = false;
            }
        },

        clampPan() {
            const viewport = this.$refs.lightboxViewport;
            const image = this.$refs.lightboxImage;

            if (! viewport || ! image || ! image.naturalWidth) {
                return;
            }

            const viewportWidth = viewport.clientWidth;
            const viewportHeight = viewport.clientHeight;
            const fitScale = Math.min(
                viewportWidth / image.naturalWidth,
                viewportHeight / image.naturalHeight,
            );
            const displayWidth = image.naturalWidth * fitScale * this.scale;
            const displayHeight = image.naturalHeight * fitScale * this.scale;
            const maxX = Math.max(0, (displayWidth - viewportWidth) / 2);
            const maxY = Math.max(0, (displayHeight - viewportHeight) / 2);

            this.translateX = Math.min(maxX, Math.max(-maxX, this.translateX));
            this.translateY = Math.min(maxY, Math.max(-maxY, this.translateY));
        },

        onLightboxKeydown(event) {
            if (! this.lightbox) {
                return;
            }

            if (event.key === 'Escape') {
                this.closeLightbox();

                return;
            }

            if (this.scale > 1) {
                const step = event.shiftKey ? 80 : 40;

                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    this.translateX += step;
                    this.clampPan();
                } else if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    this.translateX -= step;
                    this.clampPan();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.translateY += step;
                    this.clampPan();
                } else if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.translateY -= step;
                    this.clampPan();
                }

                return;
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.prev();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.next();
            }
        },

        get stageStyle() {
            return {
                transform: `translate(${this.translateX}px, ${this.translateY}px) scale(${this.scale})`,
            };
        },

        get isZoomed() {
            return this.scale > 1;
        },
    }));
});
