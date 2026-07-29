function toPersianDigits(value) {
    return String(value).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function pad2(n) {
    return String(n).padStart(2, '0');
}

document.addEventListener('alpine:init', () => {
    Alpine.data('searchAutocomplete', (endpoint) => ({
        query: '',
        products: [],
        categories: [],
        open: false,
        async fetch() {
            if (this.query.length < 2) {
                this.open = false;
                this.products = [];
                this.categories = [];
                return;
            }

            try {
                const res = await fetch(`${endpoint}?q=${encodeURIComponent(this.query)}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await res.json();
                this.products = data.products || [];
                this.categories = data.categories || [];
                this.open = this.products.length > 0 || this.categories.length > 0;
            } catch {
                this.open = false;
            }
        },
    }));

    Alpine.data('reviewsCarousel', () => ({
        index: 0,
        total: 0,
        perView: 1,
        maxIndex: 0,
        canNavigate: false,
        pages: [],
        touchStartX: 0,
        isInteracting: false,
        init() {
            this.$nextTick(() => {
                this.total = this.$refs.track?.children.length || 0;
                this.recalc();
                requestAnimationFrame(() => this.recalc());

                window.addEventListener('resize', () => this.recalc());

                if (typeof ResizeObserver !== 'undefined' && this.$refs.viewport) {
                    const observer = new ResizeObserver(() => this.recalc());
                    observer.observe(this.$refs.viewport);
                }
            });
        },
        scrollOffsetForSlide(slide) {
            const viewport = this.$refs.viewport;
            if (!viewport || !slide) {
                return 0;
            }

            const slideRect = slide.getBoundingClientRect();
            const viewportRect = viewport.getBoundingClientRect();
            const isRtl = getComputedStyle(viewport).direction === 'rtl';
            const delta = isRtl
                ? slideRect.right - viewportRect.right
                : slideRect.left - viewportRect.left;

            return viewport.scrollLeft + delta;
        },
        onInteractStart() {
            this.isInteracting = true;
            this.$refs.viewport?.classList.add('is-dragging');
        },
        onInteractEnd() {
            if (!this.isInteracting) {
                return;
            }

            this.isInteracting = false;
            this.$refs.viewport?.classList.remove('is-dragging');
            this.syncIndexFromScroll();
        },
        onScroll() {
            if (this.isInteracting) {
                return;
            }

            this.syncIndexFromScroll();
        },
        syncIndexFromScroll() {
            const viewport = this.$refs.viewport;
            const track = this.$refs.track;
            if (!viewport || !track?.children.length) {
                return;
            }

            let closestIndex = 0;
            let closestDistance = Number.POSITIVE_INFINITY;
            const scrollLeft = viewport.scrollLeft;

            Array.from(track.children).forEach((slide, slideIndex) => {
                const distance = Math.abs(scrollLeft - this.scrollOffsetForSlide(slide));
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = slideIndex;
                }
            });

            this.index = Math.max(0, Math.min(closestIndex, this.maxIndex));
        },
        recalc() {
            this.total = this.$refs.track?.children.length || 0;
            const viewport = this.$refs.viewport;
            const slide = this.$refs.track?.firstElementChild;
            if (!viewport || !slide) {
                this.perView = 1;
                this.maxIndex = 0;
                this.canNavigate = false;
                this.pages = [];
                return;
            }

            const gap = parseFloat(getComputedStyle(this.$refs.track).columnGap || getComputedStyle(this.$refs.track).gap) || 0;
            const slideWidth = slide.getBoundingClientRect().width;
            if (slideWidth <= 0) {
                return;
            }

            this.perView = Math.max(1, Math.floor((viewport.clientWidth + gap) / (slideWidth + gap)));
            this.maxIndex = Math.max(0, this.total - this.perView);
            this.canNavigate = this.total > this.perView;
            this.pages = Array.from({ length: this.maxIndex + 1 }, (_, i) => i + 1);
            if (this.index > this.maxIndex) {
                this.index = this.maxIndex;
            }
        },
        goTo(i) {
            this.index = Math.max(0, Math.min(i, this.maxIndex));
            const viewport = this.$refs.viewport;
            const slide = this.$refs.track?.children[this.index];
            if (!viewport || !slide) {
                return;
            }

            const left = this.scrollOffsetForSlide(slide);
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                viewport.scrollTo({
                    left,
                    behavior: 'instant',
                });
                return;
            }

            viewport.scrollTo({
                left,
                behavior: 'smooth',
            });
        },
        next() {
            if (this.index < this.maxIndex) {
                this.goTo(this.index + 1);
            }
        },
        prev() {
            if (this.index > 0) {
                this.goTo(this.index - 1);
            }
        },
        onTouchStart(e) {
            this.touchStartX = e.touches[0].clientX;
            this.onInteractStart();
        },
        onTouchEnd(e) {
            const delta = this.touchStartX - e.changedTouches[0].clientX;
            this.onInteractEnd();

            if (Math.abs(delta) < 48) {
                return;
            }

            delta > 0 ? this.next() : this.prev();
        },
    }));

    Alpine.data('homeStickyNav', (sections) => ({
        sections,
        visible: false,
        active: sections[0]?.id ?? null,
        init() {
            const onScroll = () => {
                this.visible = window.scrollY > 520;
                let current = this.sections[0]?.id ?? null;

                for (const section of this.sections) {
                    const element = document.getElementById(section.id);
                    if (!element) {
                        continue;
                    }

                    if (element.getBoundingClientRect().top <= 160) {
                        current = section.id;
                    }
                }

                this.active = current;
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        },
        scrollTo(id) {
            const element = document.getElementById(id);
            if (!element) {
                return;
            }

            const offset = 112;
            const top = element.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
            this.active = id;
        },
    }));

    Alpine.data('flashCountdown', (endsAt) => ({
        days: '۰۰',
        hours: '۰۰',
        minutes: '۰۰',
        seconds: '۰۰',
        timer: null,
        start() {
            const end = new Date(endsAt).getTime();
            const tick = () => {
                const diff = Math.max(0, end - Date.now());
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                this.days = toPersianDigits(pad2(d));
                this.hours = toPersianDigits(pad2(h));
                this.minutes = toPersianDigits(pad2(m));
                this.seconds = toPersianDigits(pad2(s));
            };
            tick();
            this.timer = setInterval(tick, 1000);
        },
    }));
});
