const DEFAULT_DERIVATION = {
    surface: '#ffffff',
    primary_hover_darken: 0.12,
    primary_dark_darken: 0.25,
    primary_deeper_darken: 0.42,
    accent_hover_darken: 0.1,
    background_lighten: 0.95,
    border_lighten: 0.78,
    on_hero_lighten: 0.88,
    text_base: '#1a1a1a',
    text_mix_weight: 0.18,
    muted_base: '#6b7280',
    muted_mix_weight: 0.22,
};

function derivationRules() {
    return window.__STORE_THEME_DERIVATION__ ?? DEFAULT_DERIVATION;
}

export function normalizeHex(hex) {
    let value = (hex || '#16827d').replace('#', '').trim();

    if (value.length === 3) {
        value = value.split('').map((char) => char + char).join('');
    }

    return `#${value.toLowerCase()}`;
}

export function hexToChannels(hex) {
    const value = normalizeHex(hex).slice(1);

    return [
        parseInt(value.slice(0, 2), 16),
        parseInt(value.slice(2, 4), 16),
        parseInt(value.slice(4, 6), 16),
    ];
}

export function hexToRgb(hex) {
    const [r, g, b] = hexToChannels(hex);

    return `${r} ${g} ${b}`;
}

export function mixHex(hex1, hex2, weight = 0.5) {
    const [r1, g1, b1] = hexToChannels(hex1);
    const [r2, g2, b2] = hexToChannels(hex2);
    const r = Math.round(r1 * (1 - weight) + r2 * weight);
    const g = Math.round(g1 * (1 - weight) + g2 * weight);
    const b = Math.round(b1 * (1 - weight) + b2 * weight);

    return `#${[r, g, b].map((channel) => channel.toString(16).padStart(2, '0')).join('')}`;
}

export function lightenHex(hex, amount) {
    return mixHex(hex, '#ffffff', amount);
}

export function darkenHex(hex, factor = 0.15) {
    const [r, g, b] = hexToChannels(hex);

    return `#${[r, g, b]
        .map((channel) => Math.max(0, Math.round(channel * (1 - factor))).toString(16).padStart(2, '0'))
        .join('')}`;
}

export function deriveThemePalette(primary, accent) {
    const rules = derivationRules();
    const normalizedPrimary = normalizeHex(primary);
    const normalizedAccent = normalizeHex(accent);
    const primaryDark = darkenHex(normalizedPrimary, rules.primary_dark_darken ?? 0.25);
    const primaryDeeper = darkenHex(normalizedPrimary, rules.primary_deeper_darken ?? 0.42);

    return {
        primary: normalizedPrimary,
        'primary-hover': darkenHex(normalizedPrimary, rules.primary_hover_darken ?? 0.12),
        'primary-dark': primaryDark,
        'primary-deeper': primaryDeeper,
        accent: normalizedAccent,
        'accent-hover': darkenHex(normalizedAccent, rules.accent_hover_darken ?? 0.1),
        surface: rules.surface ?? '#ffffff',
        background: lightenHex(normalizedPrimary, rules.background_lighten ?? 0.95),
        text: mixHex(rules.text_base ?? '#1a1a1a', primaryDark, rules.text_mix_weight ?? 0.18),
        muted: mixHex(rules.muted_base ?? '#6b7280', primaryDark, rules.muted_mix_weight ?? 0.22),
        'on-hero': lightenHex(normalizedPrimary, rules.on_hero_lighten ?? 0.88),
        border: lightenHex(normalizedPrimary, rules.border_lighten ?? 0.78),
        'hero-from': primaryDark,
        'hero-via': normalizedPrimary,
        'hero-to': normalizedAccent,
    };
}

export function applyThemePalette(primary, accent, root = document.documentElement) {
    const palette = deriveThemePalette(primary, accent);

    Object.entries(palette).forEach(([name, hex]) => {
        root.style.setProperty(`--shop-${name}`, hex);
        root.style.setProperty(`--shop-${name}-rgb`, hexToRgb(hex));
    });

    return palette;
}
