import '@fontsource/vazirmatn/400.css';
import '@fontsource/vazirmatn/500.css';
import '@fontsource/vazirmatn/600.css';
import '@fontsource/vazirmatn/700.css';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const PERSIAN_FONT = 'Vazirmatn, Tahoma, sans-serif';

Chart.defaults.font.family = PERSIAN_FONT;
Chart.defaults.plugins.tooltip.rtl = true;
Chart.defaults.plugins.tooltip.titleFont = { family: PERSIAN_FONT };
Chart.defaults.plugins.tooltip.bodyFont = { family: PERSIAN_FONT };
Chart.defaults.plugins.tooltip.footerFont = { family: PERSIAN_FONT };

const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';

function toPersianDigits(value) {
    return String(value).replace(/\d/g, (digit) => PERSIAN_DIGITS[Number(digit)]);
}

function formatRevenueTick(value) {
    if (value >= 1_000_000) {
        return toPersianDigits(`${(value / 1_000_000).toFixed(1)} میلیون`);
    }

    if (value >= 1_000) {
        return toPersianDigits(`${Math.round(value / 1_000)} هزار`);
    }

    return toPersianDigits(value);
}

function formatNumber(value) {
    return toPersianDigits(new Intl.NumberFormat('fa-IR').format(value ?? 0));
}

function refreshChartFonts(chart) {
    chart.update('none');
}

function baseChartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                rtl: true,
                labels: {
                    font: { family: PERSIAN_FONT, size: 12 },
                    usePointStyle: true,
                    padding: 16,
                },
            },
            tooltip: {
                rtl: true,
                titleFont: { family: PERSIAN_FONT, size: 13 },
                bodyFont: { family: PERSIAN_FONT, size: 12 },
            },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    font: { family: PERSIAN_FONT, size: 11 },
                    maxRotation: 45,
                    minRotation: 0,
                },
            },
        },
    };
}

function initChart(canvas, config) {
    const chart = new Chart(canvas, config);

    if (document.fonts) {
        document.fonts.ready.then(() => refreshChartFonts(chart));
        document.fonts.addEventListener('loadingdone', () => refreshChartFonts(chart));
    }

    return chart;
}

function initDualAxisChart(canvasId, type, revenueLabel, barStyle = false) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    const data = JSON.parse(canvas.dataset.chart || '{}');
    const labels = (data.labels || []).map((label) => toPersianDigits(label));
    const revenues = data.revenues || [];
    const orders = data.orders || [];

    const revenueDataset = barStyle
        ? {
            label: revenueLabel,
            data: revenues,
            backgroundColor: 'rgba(99, 102, 241, 0.75)',
            borderColor: '#6366f1',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'y',
        }
        : {
            label: revenueLabel,
            data: revenues,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.08)',
            fill: true,
            tension: 0.4,
            yAxisID: 'y',
        };

    const orderDataset = barStyle
        ? {
            label: 'تعداد سفارش',
            data: orders,
            type: 'line',
            borderColor: '#f59e0b',
            backgroundColor: 'transparent',
            borderWidth: 2,
            tension: 0.35,
            pointRadius: 3,
            yAxisID: 'y1',
        }
        : {
            label: 'تعداد سفارش',
            data: orders,
            borderColor: '#10b981',
            backgroundColor: 'transparent',
            borderDash: [4, 4],
            tension: 0.4,
            yAxisID: 'y1',
        };

    initChart(canvas, {
        type,
        data: {
            labels,
            datasets: [revenueDataset, orderDataset],
        },
        options: {
            ...baseChartOptions(),
            interaction: { mode: 'index', intersect: false },
            plugins: {
                ...baseChartOptions().plugins,
                tooltip: {
                    ...baseChartOptions().plugins.tooltip,
                    callbacks: {
                        label(context) {
                            const label = context.dataset.label || '';
                            const value = formatNumber(context.parsed.y ?? context.raw);

                            return `${label}: ${value}`;
                        },
                    },
                },
            },
            scales: {
                ...baseChartOptions().scales,
                y: {
                    position: 'right',
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { family: PERSIAN_FONT, size: 11 },
                        callback: formatRevenueTick,
                    },
                },
                y1: {
                    position: 'left',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { family: PERSIAN_FONT, size: 11 },
                        stepSize: 1,
                        callback: (value) => toPersianDigits(value),
                    },
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDualAxisChart('financialDailyChart', 'line', 'درآمد (تومان)');
    initDualAxisChart('financialMonthlyChart', 'bar', 'درآمد ماهانه (تومان)', true);
});
