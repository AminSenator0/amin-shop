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

function initSalesChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) {
        return;
    }

    const data = JSON.parse(canvas.dataset.chart || '{}');
    const labels = (data.labels || []).map((label) => toPersianDigits(label));
    const revenues = data.revenues || [];
    const orders = data.orders || [];

    initChart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'درآمد (تومان)',
                    data: revenues,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'تعداد سفارش',
                    data: orders,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderDash: [4, 4],
                    tension: 0.4,
                    yAxisID: 'y1',
                },
            ],
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
                        font: { family: PERSIAN_FONT, size: 12 },
                        callback: formatRevenueTick,
                    },
                },
                y1: {
                    position: 'left',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { family: PERSIAN_FONT, size: 12 },
                        stepSize: 1,
                        callback: (value) => toPersianDigits(value),
                    },
                },
            },
        },
    });
}

function initMonthlySalesChart() {
    const canvas = document.getElementById('monthlySalesChart');
    if (!canvas) {
        return;
    }

    const data = JSON.parse(canvas.dataset.chart || '{}');
    const labels = (data.labels || []).map((label) => toPersianDigits(label));
    const revenues = data.revenues || [];
    const orders = data.orders || [];

    initChart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'درآمد ماهانه (تومان)',
                    data: revenues,
                    backgroundColor: 'rgba(99, 102, 241, 0.75)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'تعداد سفارش',
                    data: orders,
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.35,
                    pointRadius: 3,
                    yAxisID: 'y1',
                },
            ],
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

function initVisitorsChart() {
    const canvas = document.getElementById('visitorsChart');
    if (!canvas) {
        return;
    }

    const data = JSON.parse(canvas.dataset.chart || '{}');
    const labels = (data.labels || []).map((label) => toPersianDigits(label));
    const visitors = data.visitors || [];

    initChart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'بازدیدکننده یکتا',
                    data: visitors,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                },
            ],
        },
        options: {
            ...baseChartOptions(),
            plugins: {
                ...baseChartOptions().plugins,
                legend: {
                    ...baseChartOptions().plugins.legend,
                    display: false,
                },
                tooltip: {
                    ...baseChartOptions().plugins.tooltip,
                    callbacks: {
                        label(context) {
                            return `بازدیدکننده: ${formatNumber(context.parsed.y ?? context.raw)} نفر`;
                        },
                    },
                },
            },
            scales: {
                ...baseChartOptions().scales,
                y: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
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

function initDoughnutChart(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    const items = JSON.parse(canvas.dataset.chart || '[]');
    if (!items.length) {
        return;
    }

    initChart(canvas, {
        type: 'doughnut',
        data: {
            labels: items.map((item) => item.label),
            datasets: [
                {
                    data: items.map((item) => item.count),
                    backgroundColor: items.map((item) => item.color),
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl: true,
                    labels: {
                        font: { family: PERSIAN_FONT, size: 11 },
                        usePointStyle: true,
                        padding: 12,
                    },
                },
                tooltip: {
                    rtl: true,
                    titleFont: { family: PERSIAN_FONT, size: 13 },
                    bodyFont: { family: PERSIAN_FONT, size: 12 },
                    callbacks: {
                        label(context) {
                            const value = formatNumber(context.parsed ?? context.raw);

                            return `${context.label}: ${value}`;
                        },
                    },
                },
            },
        },
    });
}

function initAutoRefresh() {
    const element = document.getElementById('dashboard-refresh');
    if (!element) {
        return;
    }

    const totalSeconds = Number(element.dataset.refreshSeconds || 300);
    let remaining = totalSeconds;

    const timer = setInterval(() => {
        remaining -= 1;

        if (remaining <= 0) {
            clearInterval(timer);
            window.location.reload();

            return;
        }

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        const timeLabel = minutes > 0
            ? `${toPersianDigits(minutes)}:${toPersianDigits(String(seconds).padStart(2, '0'))}`
            : `${toPersianDigits(seconds)} ثانیه`;

        element.textContent = `به‌روزرسانی خودکار تا ${timeLabel} دیگر`;
    }, 1000);
}

document.addEventListener('DOMContentLoaded', () => {
    initSalesChart();
    initMonthlySalesChart();
    initVisitorsChart();
    initDoughnutChart('orderStatusChart');
    initDoughnutChart('paymentStatusChart');
    initAutoRefresh();
});
