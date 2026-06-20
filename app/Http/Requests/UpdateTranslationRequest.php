<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('translations')
                    ->ignore($this->route('translation'))
                    ->where(fn ($q) => $q->where('locale', $this->input('locale'))),
            ],
            'locale' => ['sometimes', 'string', 'max:10'],
            'content' => ['sometimes', 'string'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:255'],
        ];
    }
}
