@props([
    'value' => null,
    'apparelCategoryIds' => [],
])

@php
    $initialChart = old('size_chart');

    if ($initialChart === null && filled($value)) {
        $initialChart = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }
@endphp

<div
    class="admin-size-chart-editor md:col-span-2"
    x-data="sizeChartEditor({
        initial: @js($initialChart),
        apparelCategoryIds: @js($apparelCategoryIds),
    })"
    x-init="init()"
>
    <input type="hidden" name="size_chart" :value="serialized()">

    <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-sm font-bold text-zinc-800">جدول سایزبندی (پوشاک)</p>
                <p class="mt-1 text-xs leading-relaxed text-zinc-500">برای لباس، کفش و محصولاتی که نیاز به راهنمای سایز دارند، جدول اندازه‌ها را تکمیل کنید.</p>
            </div>
            <label class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-bold text-zinc-700">
                <input type="checkbox" class="admin-checkbox" x-model="enabled" @change="onToggle()">
                فعال‌سازی جدول
            </label>
        </div>

        <div class="mt-4 space-y-4" x-show="enabled" x-cloak>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="admin-btn-secondary !px-3 !py-1.5 text-xs" @click="applyTemplate('clothing')">قالب لباس</button>
                <button type="button" class="admin-btn-secondary !px-3 !py-1.5 text-xs" @click="applyTemplate('shoe')">قالب کفش</button>
                <button type="button" class="admin-btn-secondary !px-3 !py-1.5 text-xs" @click="syncFromSizes()">همگام با سایزهای بالا</button>
                <button type="button" class="admin-btn-secondary !px-3 !py-1.5 text-xs" @click="addColumn()">ستون جدید</button>
                <button type="button" class="admin-btn-secondary !px-3 !py-1.5 text-xs" @click="addRow()">ردیف جدید</button>
            </div>

            <div class="admin-table-wrap overflow-x-auto">
                <table class="admin-table min-w-[36rem]">
                    <thead>
                        <tr>
                            <th class="w-28">سایز</th>
                            <template x-for="(column, columnIndex) in columns" :key="'column-' + columnIndex">
                                <th>
                                    <div class="flex items-center gap-1">
                                        <input type="text" class="admin-input !py-1.5 text-xs" x-model="columns[columnIndex]" placeholder="عنوان ستون">
                                        <button type="button" class="rounded-lg p-1 text-rose-500 hover:bg-rose-50" @click="removeColumn(columnIndex)" title="حذف ستون">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </th>
                            </template>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, rowIndex) in rows" :key="'row-' + rowIndex">
                            <tr>
                                <td>
                                    <input type="text" class="admin-input !py-1.5 text-xs font-bold" x-model="row.size" placeholder="M">
                                </td>
                                <template x-for="(value, valueIndex) in row.values" :key="'value-' + rowIndex + '-' + valueIndex">
                                    <td>
                                        <input type="text" class="admin-input !py-1.5 text-xs" x-model="row.values[valueIndex]" placeholder="—" dir="ltr">
                                    </td>
                                </template>
                                <td>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50" @click="removeRow(rowIndex)" title="حذف ردیف">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-zinc-500">اندازه‌ها را بر حسب سانتی‌متر وارد کنید. این جدول در صفحه محصول به‌صورت «راهنمای سایز» نمایش داده می‌شود.</p>
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sizeChartEditor', ({ initial, apparelCategoryIds }) => ({
                enabled: false,
                columns: [],
                rows: [],
                apparelCategoryIds: apparelCategoryIds || [],

                init() {
                    const parsed = this.parseInitial(initial);

                    if (parsed) {
                        this.enabled = true;
                        this.columns = parsed.columns;
                        this.rows = parsed.rows;
                    }

                    const categorySelect = document.getElementById('category_id');

                    if (categorySelect) {
                        categorySelect.addEventListener('change', () => this.onCategoryChange(categorySelect.value));
                        this.onCategoryChange(categorySelect.value);
                    }
                },

                parseInitial(raw) {
                    if (!raw) {
                        return null;
                    }

                    try {
                        const data = typeof raw === 'string' ? JSON.parse(raw) : raw;

                        if (!data?.columns?.length || !data?.rows?.length) {
                            return null;
                        }

                        return {
                            columns: [...data.columns],
                            rows: data.rows.map((row) => ({
                                size: row.size || '',
                                values: [...(row.values || [])],
                            })),
                        };
                    } catch (error) {
                        return null;
                    }
                },

                onCategoryChange(categoryId) {
                    if (this.enabled || !categoryId) {
                        return;
                    }

                    if (this.apparelCategoryIds.map(String).includes(String(categoryId))) {
                        this.enabled = true;
                        this.applyTemplate('clothing');
                    }
                },

                onToggle() {
                    if (this.enabled && this.columns.length === 0) {
                        this.applyTemplate('clothing');
                    }
                },

                applyTemplate(type) {
                    const template = type === 'shoe'
                        ? @js(\App\Models\Product::defaultShoeSizeChart())
                        : @js(\App\Models\Product::defaultClothingSizeChart());

                    this.enabled = true;
                    this.columns = [...template.columns];
                    this.rows = template.rows.map((row) => ({
                        size: row.size,
                        values: [...row.values],
                    }));
                },

                syncFromSizes() {
                    const sizesField = document.getElementById('sizes');

                    if (!sizesField) {
                        return;
                    }

                    const sizes = sizesField.value
                        .split(/[\r\n,،]+/)
                        .map((item) => item.trim())
                        .filter(Boolean);

                    if (!sizes.length) {
                        return;
                    }

                    if (!this.columns.length) {
                        this.applyTemplate('clothing');
                    }

                    const existing = new Map(this.rows.map((row) => [row.size, row]));

                    this.rows = sizes.map((size) => {
                        if (existing.has(size)) {
                            const row = existing.get(size);
                            while (row.values.length < this.columns.length) {
                                row.values.push('');
                            }

                            return row;
                        }

                        return {
                            size,
                            values: Array(this.columns.length).fill(''),
                        };
                    });

                    this.enabled = true;
                },

                addColumn() {
                    this.columns.push('ستون جدید');
                    this.rows.forEach((row) => row.values.push(''));
                },

                removeColumn(index) {
                    this.columns.splice(index, 1);
                    this.rows.forEach((row) => row.values.splice(index, 1));
                },

                addRow() {
                    this.rows.push({
                        size: '',
                        values: Array(this.columns.length).fill(''),
                    });
                },

                removeRow(index) {
                    this.rows.splice(index, 1);
                },

                serialized() {
                    if (!this.enabled) {
                        return '';
                    }

                    const columns = this.columns.map((column) => column.trim()).filter(Boolean);
                    const rows = this.rows
                        .map((row) => ({
                            size: (row.size || '').trim(),
                            values: row.values.map((value) => (value || '').trim()),
                        }))
                        .filter((row) => row.size !== '');

                    if (!columns.length || !rows.length) {
                        return '';
                    }

                    return JSON.stringify({ columns, rows });
                },
            }));
        });
    </script>
    @endpush
@endonce
