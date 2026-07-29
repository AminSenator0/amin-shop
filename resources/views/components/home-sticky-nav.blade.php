@props(['sections' => []])

@if($store['homepageStickyNavEnabled'] && count($sections) > 1)
<nav
    class="home-sticky-nav"
    aria-label="ناوبری سریع صفحه اصلی"
    x-data="homeStickyNav(@js(array_values($sections)))"
    x-init="init()"
    :class="{ 'is-visible': visible }"
>
    <div class="home-sticky-nav-inner">
        <div class="home-sticky-nav-track" x-ref="track">
            <template x-for="section in sections" :key="section.id">
                <button
                    type="button"
                    class="home-sticky-nav-chip"
                    :class="{ 'is-active': active === section.id }"
                    @click="scrollTo(section.id)"
                    x-text="section.label"
                ></button>
            </template>
        </div>
    </div>
</nav>
@endif
