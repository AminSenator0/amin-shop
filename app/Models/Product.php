<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'short_description',
        'price',
        'compare_price',
        'sku',
        'stock',
        'weight',
        'sizes',
        'colors',
        'size_chart',
        'image',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sizes' => 'array',
            'colors' => 'array',
            'size_chart' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    // ─── متدهای واریانت ────────────────────────────────────
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function hasVariants(): bool
    {
        try {
            return $this->variants()->exists();
        } catch (\Illuminate\Database\QueryException $e) {
            return false;
        }
    }

    public function variantStock(?string $size, ?string $color): int
    {
        if (! $this->hasSizes() && ! $this->hasColors()) {
            return $this->stock;
        }

        $variant = $this->variants()
            ->where('size', $size)
            ->where('color', $color)
            ->first();

        return $variant?->stock ?? 0;
    }

    public function variantIsInStock(?string $size, ?string $color): bool
    {
        return $this->variantStock($size, $color) > 0;
    }

    public function hasEnoughStock(int $quantity, ?string $size = null, ?string $color = null): bool
    {
        return $this->variantStock($size, $color) >= $quantity;
    }

    public function decrementVariantStock(?string $size, ?string $color, int $quantity): void
    {
        if ($this->hasVariants()) {
            $variant = $this->variants()
                ->where('size', $size)
                ->where('color', $color)
                ->first();

            if ($variant) {
                $variant->decrement('stock', $quantity);
                $this->update(['stock' => $this->variants()->sum('stock')]);
                return;
            }
        }

        $this->decrement('stock', $quantity);
    }

    public function incrementVariantStock(?string $size, ?string $color, int $quantity): void
    {
        if ($this->hasVariants()) {
            $variant = $this->variants()
                ->where('size', $size)
                ->where('color', $color)
                ->first();

            if ($variant) {
                $variant->increment('stock', $quantity);
                $this->update(['stock' => $this->variants()->sum('stock')]);
                return;
            }
        }

        $this->increment('stock', $quantity);
    }
    // ─── پایان متدهای واریانت ──────────────────────────────

    // ─── متدهای Attribute ──────────────────────────────────
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_attribute_values')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function hasAttributes(): bool
    {
        return $this->attributeValues()->exists();
    }
    // ─── پایان متدهای Attribute ────────────────────────────

    // ─── متدهای جدید Custom Fields ──────────────────────────
    public function customFields(): HasMany
    {
        return $this->hasMany(ProductCustomField::class)->orderBy('sort_order');
    }

    public function hasCustomFields(): bool
    {
        try {
            return $this->customFields()->exists();
        } catch (\Illuminate\Database\QueryException $e) {
            return false;
        }
    }
    // ─── پایان متدهای جدید Custom Fields ───────────────────

    public function thumbnail(): ?string
    {
        if ($this->image) {
            return $this->image;
        }

        return $this->images->first()?->path;
    }

    public function thumbnailUrl(): ?string
    {
        $path = $this->thumbnail();

        if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        if ($this->isMenPerfume()) {
            return men_perfume_image_url($this->category?->slug);
        }

        return null;
    }

    public function isMenPerfume(): bool
    {
        if (mb_stripos($this->name, 'عطر مردانه') !== false || mb_stripos($this->name, 'ادکلن مردانه') !== false) {
            return true;
        }

        $category = $this->relationLoaded('category') ? $this->category : null;

        return $category?->name === 'عطر و ادکلن' && mb_stripos($this->name, 'مردانه') !== false;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function averageRating(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    public function isInStock(): bool
    {
        if ($this->hasVariants()) {
            return $this->variants()->sum('stock') > 0;
        }
        return $this->stock > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function discountPercent(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function savingsAmount(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) ($this->compare_price - $this->price);
    }

    public function isLowStock(int $threshold = 5): bool
    {
        if ($this->hasVariants()) {
            $total = $this->variants()->sum('stock');
            return $total > 0 && $total <= $threshold;
        }
        return $this->stock > 0 && $this->stock <= $threshold;
    }

    public static function parseOptionsList(?string $text): ?array
    {
        if (blank($text)) {
            return null;
        }

        $items = preg_split('/[\r\n,،]+/u', $text);
        $items = array_values(array_filter(array_map('trim', $items)));

        return $items !== [] ? $items : null;
    }

    public function hasSizes(): bool
    {
        return filled($this->sizes);
    }

    public function hasColors(): bool
    {
        return filled($this->colors);
    }

    public function hasSelectableOptions(): bool
    {
        return $this->hasSizes() || $this->hasColors();
    }

    public function isValidSize(?string $size): bool
    {
        return ! $this->hasSizes() || in_array($size, $this->sizes, true);
    }

    public function isValidColor(?string $color): bool
    {
        return ! $this->hasColors() || in_array($color, $this->colors, true);
    }

    public function optionsLabel(?string $size = null, ?string $color = null): ?string
    {
        $parts = [];

        if ($size) {
            $parts[] = 'سایز '.$size;
        }

        if ($color) {
            $parts[] = 'رنگ '.$color;
        }

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    public function hasSizeChart(): bool
    {
        return filled($this->size_chart['rows'] ?? null);
    }

    public function isApparelCategory(?Category $category = null): bool
    {
        $category ??= $this->relationLoaded('category') ? $this->category : $this->category()->first();

        if (! $category) {
            return false;
        }

        return (bool) preg_match('/(پوشاک|کفش|لباس|پیراهن|کت|هودی|شلوار)/u', $category->name);
    }

    public static function parseSizeChart(?string $json): ?array
    {
        if (blank($json)) {
            return null;
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            return null;
        }

        $columns = array_values(array_filter(array_map(
            fn ($column) => is_string($column) ? trim($column) : '',
            $data['columns'] ?? []
        )));

        $rows = [];

        foreach ($data['rows'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $size = trim((string) ($row['size'] ?? ''));

            if ($size === '') {
                continue;
            }

            $values = array_map(
                fn ($value) => trim((string) $value),
                $row['values'] ?? []
            );

            while (count($values) < count($columns)) {
                $values[] = '';
            }

            $rows[] = [
                'size' => $size,
                'values' => array_slice($values, 0, count($columns)),
            ];
        }

        if ($columns === [] || $rows === []) {
            return null;
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    public static function defaultClothingSizeChart(): array
    {
        return [
            'columns' => ['قد لباس (cm)', 'عرض سینه (cm)', 'عرض شانه (cm)', 'قد آستین (cm)'],
            'rows' => [
                ['size' => 'S', 'values' => ['', '', '', '']],
                ['size' => 'M', 'values' => ['', '', '', '']],
                ['size' => 'L', 'values' => ['', '', '', '']],
                ['size' => 'XL', 'values' => ['', '', '', '']],
            ],
        ];
    }

    public static function defaultShoeSizeChart(): array
    {
        return [
            'columns' => ['طول کف (cm)', 'سایز اروپا', 'سایز UK'],
            'rows' => [
                ['size' => '38', 'values' => ['', '', '']],
                ['size' => '39', 'values' => ['', '', '']],
                ['size' => '40', 'values' => ['', '', '']],
                ['size' => '41', 'values' => ['', '', '']],
                ['size' => '42', 'values' => ['', '', '']],
            ],
        ];
    }
}