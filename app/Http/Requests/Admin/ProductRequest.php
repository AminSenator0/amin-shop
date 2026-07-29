<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductFormUploadCache;
use App\Support\UploadRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($product),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'compare_price' => ['nullable', 'integer', 'min:0'],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product),
            ],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'sizes' => ['nullable', 'string', 'max:1000'],
            'colors' => ['nullable', 'string', 'max:1000'],
            'size_chart' => ['nullable', 'string', 'max:20000'],
            'image_sort' => ['nullable', 'string'],
            'primary_image' => ['nullable', 'string', 'max:100'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('product_images', 'id')],
            'images' => ['nullable', 'array', 'max:10'],
            ...UploadRules::files('images', 'product', 10),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $cache = app(ProductFormUploadCache::class);
        $cache->storeFromRequest($this);

        $cached = $cache->get();

        if ($cached !== null) {
            session()->flashInput(array_merge(
                $this->except(['images', 'image']),
                [
                    'image_sort' => $cached['image_sort'] ?? $this->input('image_sort'),
                    'primary_image' => $cached['primary_image'] ?? $this->input('primary_image'),
                    'remove_images' => $cached['remove_images'] ?? $this->input('remove_images', []),
                ]
            ));
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->countEffectiveImages() === 0) {
                $validator->errors()->add('images', 'حداقل یک تصویر برای محصول لازم است.');
            }

            if ($this->countEffectiveImages() > 10) {
                $validator->errors()->add('images', 'حداکثر ۱۰ تصویر می‌توانید بارگذاری کنید.');
            }
        });
    }

    private function countEffectiveImages(): int
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $removed = array_map('intval', (array) $this->input('remove_images', []));
        $sort = json_decode($this->input('image_sort', '[]'), true) ?: [];
        $count = 0;

        foreach ($sort as $entry) {
            if (! is_string($entry)) {
                continue;
            }

            if (str_starts_with($entry, 'existing:')) {
                $id = (int) substr($entry, 9);

                if ($product && ! in_array($id, $removed, true)) {
                    $belongs = ProductImage::where('product_id', $product->id)->where('id', $id)->exists();

                    if ($belongs) {
                        $count++;
                    }
                }

                continue;
            }

            if (str_starts_with($entry, 'cached:')) {
                $key = substr($entry, 7);
                $path = app(ProductFormUploadCache::class)->getPathByKey($key);

                if ($path && Storage::disk('local')->exists($path)) {
                    $count++;
                }

                continue;
            }

            if (str_starts_with($entry, 'new:')) {
                $index = (int) substr($entry, 4);
                $files = array_values($this->file('images', []) ?? []);

                if (isset($files[$index])) {
                    $count++;
                }
            }
        }

        if ($count > 0) {
            return $count;
        }

        if ($this->hasFile('images')) {
            return count(array_filter($this->file('images', [])));
        }

        if (app(ProductFormUploadCache::class)->hasItems()) {
            return count(app(ProductFormUploadCache::class)->get()['items'] ?? []);
        }

        if ($product) {
            return $product->images()->whereNotIn('id', $removed)->count();
        }

        return 0;
    }
}
