<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCommentRequest extends FormRequest
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
    public function rules()
    {
        return [
            'news_id' => ['required','exists:news,id'],
            'parent_id' => ['nullable', 'exists:comments,id'],
            'text' => ['required', 'string', 'min:2', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validator
     *
     * @return array
     */
    public function messages()
    {
        return [
            'news_id.required' => 'Поле новости обязательно для заполнения.',
            'news_id.exists' => 'Выбранная новость не найдена.',
            'parent_id.exists' => 'Родительский комментарий не найден.',
            'text.required' => 'Текст комментария обязателен для заполнения.',
            'text.string' => 'Текст комментария должен быть строкой.',
            'text.min' => 'Текст комментария не должен быть менее 2 символов.',
            'text.max' => 'Текст комментария не должен превышать 1000 символов.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        $detailedMessage = implode(' ', $errors);

        throw new HttpResponseException(
            redirect()
                ->back()
                ->withInput()
                ->with('error', $detailedMessage)
        );
    }
}
