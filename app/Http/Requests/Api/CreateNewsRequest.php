<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateNewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'content' => ['required', 'string', 'min:2'],
            'excerpt' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'url'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'active' => ['boolean'],
            'featured' => ['boolean'],
            'published_at' => ['date'],
        ];
    }
}
