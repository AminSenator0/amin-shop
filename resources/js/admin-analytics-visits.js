import '@fontsource/vazirmatn/400.css';
import '@fontsource/vazirmatn/500.css';
import '@fontsource/vazirmatn/600.css';
import '@fontsource/vazirmatn/700.css';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const PERSIAN_FONT = 'Vazirmatn, Tahoma, sans-serif';

Chart.defaults.font.family = PERSIAN_FONT;
Chart.defaults.animation.duration = 900;
Chart.defaults.animation.easing = 'easeOutQuart';
Chart.defaults.plugins.tooltip.rtl = true;
Chart.defaults.plugins.tooltip.titleFont = { family: PERSIAN_FONT };
Chart.defaults.plugins.tooltip.bodyFont = { family: PERSIAN_FONT };
Chart.defaults.plugins.tooltip.footerFont = { family: PERSIAN_FONT };

const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';

const CHART_COLORS = [
    '#6366f1', '#10b981', '#f59e0b', '#ec4899',
    '#0ea5e9', '#8b5cf6', '#ef4444', '#14b8a6',
    '#f97316', '#64748b', '#84cc16', '#a855f7',
];

function toPersianDigits(value) {
    return String(value).replace(/\d/g, (digit) => PERSIAN_DIGITS[Number(digit)]);
}

function formatNumber(value) {
    return toPersianDigits(new Intl.NumberFormat('fa-IR').format(value ?? 0));
}

/** پلاگین سفارشی: سایه نرم زیر خط نمودار */
const glowPlugin = {
    id: 'glow',
    beforeDatasetDraw(chart, args) {
        const { ctx } = chart;
        const meta = chart.getDatasetMeta(args.index);

        ctx.save();
        ctx.shadowColor = meta.dataset?.borderColor || 'rgba(0,0,0,0.15)';
        ctx.shadowBlur = 14;
        ctx.shadowOffsetY = 6;
    },
    afterDatasetDraw(chart) {
        chart.ctx.restore();
    },
};

Chart.register(glowPlugin);

/** ساخت گرادیان عمودی برای ناحیه زیر خط */
function makeGradient(ctx, area, hexColor, opacityTop = 0.28) {
    const gradient = ctx.createLinearGradient(0, area.top, 0, area.bottom);
    gradient.addColorStop(0, hexToRgba(hexColor, opacityTop));
    gradient.addColorStop(1, hexToRgba(hexColor, 0));

    return gradient;
}

function hexToRgba(hex, alpha) {
    const value = hex.replace('#', '');
    const r = parseInt(value.substring(0, 2), 16);
    const g = parseInt(value.substring(2, 4), 16);
    const b = parseInt(value.substring(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function initChart(canvas, config) {
    const chart = new Chart(canvas, config);

    if (document.fonts) {
        document.fonts.ready.then(() => chart.update('none'));
        document.fonts.addEventListener('loadingdone', () => chart.update('none'));
    }

    return chart;
}

function initLineChart(canvasId, { label, borderColor, tooltipUnit }) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    const data = JSON.parse(canvas.dataset.chart || '{}');
    const labels = (data.labels || []).map((item) => toPersianDigits(item));
    const values = data.values || [];

    const ctx = canvas.getContext('2d');

    initChart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label,
                    data: values,
                    borderColor,
                    backgroundColor(context) {
                        const { chart } = context;
                        const { ctx: chartCtx, chartArea } = chart;
                        if (!chartArea) {
                            return hexToRgba(borderColor, 0.1);
                        }

                        return makeGradient(chartCtx, chartArea, borderColor);
                    },
                    fill: true,
                    tension: 0.45,
                    pointRadius: 0,
                    pointHoverRadius: 7,
                    pointHoverBorderWidth: 3,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: borderColor,
                    pointHitRadius: 12,
                    borderWidth: 3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(24, 24, 27, 0.92)',
                    titleFont: { family: PERSIAN_FONT, size: 12, weight: '600' },
                    bodyFont: { family: PERSIAN_FONT, size: 12 },
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        title(context) {
                            return `📅 ${context[0].label}`;
                        },
                        label(context) {
                            return `${label}: ${formatNumber(context.parsed.y)} ${tooltipUnit}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        font: { family: PERSIAN_FONT, size: 11 },
                        color: '#a1a1aa',
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 8,
                    },
                },
                y: {
                    position: 'right',
                    beginAtZero: true,
                    border: { display: false, dash: [4, 6] },
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawTicks: false },
                    ticks: {
                        font: { family: PERSIAN_FONT, size: 11 },
                        color: '#a1a1aa',
                        padding: 8,
                        callback: (value) => toPersianDigits(value),
                    },
                },
            },
        },
    });
}

/** پلاگین متن مرکزی دایره‌ای (مجموع کل) */
const centerTextPlugin = {
    id: 'centerText',
    afterDraw(chart) {
        const { ctx, chartArea } = chart;
        const meta = chart.getDatasetMeta(0);
        if (!meta.data.length) {
            return;
        }

        const total = chart.data.datasets[0].data.reduce((sum, v) => sum + v, 0);
        const x = (chartArea.left + chartArea.right) / 2;
        const y = (chartArea.top + chartArea.bottom) / 2;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        ctx.font = `700 20px ${PERSIAN_FONT}`;
        ctx.fillStyle = '#18181b';
        ctx.fillText(formatNumber(total), x, y - 8);

        ctx.font = `400 11px ${PERSIAN_FONT}`;
        ctx.fillStyle = '#a1a1aa';
        ctx.fillText('بازدید یکتا', x, y + 14);

        ctx.restore();
    },
};

function initDoughnutChart(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    const items = JSON.parse(canvas.dataset.chart || '[]');

    if (!items.length) {
        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.font = `12px ${PERSIAN_FONT}`;
            ctx.fillStyle = '#a1a1aa';
            ctx.textAlign = 'center';
            ctx.fillText('هنوز داده‌ای ثبت نشده است', canvas.width / 2, canvas.height / 2);
        }

        return;
    }

    initChart(canvas, {
        type: 'doughnut',
        data: {
            labels: items.map((item) => item.label),
            datasets: [
                {
                    data: items.map((item) => item.count),
                    backgroundColor: items.map((_, index) => CHART_COLORS[index % CHART_COLORS.length]),
                    hoverBackgroundColor: items.map((_, index) => CHART_COLORS[index % CHART_COLORS.length]),
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 10,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            layout: { padding: 8 },
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl: true,
                    labels: {
                        font: { family: PERSIAN_FONT, size: 11 },
                        color: '#52525b',
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                        boxWidth: 7,
                        boxHeight: 7,
                    },
                },
                tooltip: {
                    rtl: true,
                    backgroundColor: 'rgba(24, 24, 27, 0.92)',
                    titleFont: { family: PERSIAN_FONT, size: 12, weight: '600' },
                    bodyFont: { family: PERSIAN_FONT, size: 12 },
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label(context) {
                            const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                            const percent = total > 0
                                ? ` — ${formatNumber(Math.round((context.parsed / total) * 100))}٪`
                                : '';

                            return `${context.label}: ${formatNumber(context.parsed)}${percent}`;
                        },
                    },
                },
            },
        },
        plugins: [centerTextPlugin],
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLineChart('pageViewsChart', {
        label: 'بازدید صفحه',
        borderColor: '#10b981',
        tooltipUnit: 'بازدید',
    });

    initLineChart('uniqueVisitorsChart', {
        label: 'بازدید یکتا',
        borderColor: '#6366f1',
        tooltipUnit: 'نفر',
    });

    initDoughnutChart('osChart');
    initDoughnutChart('browserChart');
});