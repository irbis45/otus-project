<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchNewsRequest extends FormRequest
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
            'query' => 'required|string|min:3|max:255',
        ];
    }

    public function messages()
    {
        return [
            'query.required' => 'Поле для поиска обязательно для заполнения.',
            'query.string' => 'Поисковый запрос должен быть строкой.',
            'query.min' => 'Поисковый запрос должен содержать не менее 3 символов.',
            'query.max' => 'Поисковый запрос не должен превышать 255 символов.',
        ];
    }
}
