<?php

namespace App\Http\Requests\Admin;

use App\Rules\ValidJalaliDate;
use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $postId = $this->route('blog_post')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($postId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'image' => UploadRules::file('blog_image'),
            'remove_image' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'string', 'max:20', new ValidJalaliDate],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('published_at')) {
            $parsed = parse_jalali($this->input('published_at'));
            if ($parsed) {
                $this->merge(['published_at_parsed' => $parsed]);
            }
        }
    }
}
