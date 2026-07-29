<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function getRedirectUrl(): string
    {
        if ($this->input('from') === 'panel') {
            return route('user.reviews.index');
        }

        return parent::getRedirectUrl();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'from' => ['nullable', 'string', 'in:panel'],
        ];
    }
}
