<?php
namespace App\Http\Requests\Admin;

use App\Application\Core\Comment\Enums\CommentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // return auth()->check() && auth()->user()->can('update', $this->comment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, CommentStatus::cases())),
            ],
            'text' => ['required', 'string', 'min:2', 'max:1000'], // или любой лимит по тексту
        ];

    }

    public function messages()
    {
        return [
            'text.required' => 'Текст комментария обязателен для заполнения',
            'text.min'      => 'Текст комментария не должен менее 2 символов',
            'text.max'      => 'Текст комментария не должен превышать 1000 символов',
            'status.in' => 'Выбран недопустимый статус комментария.',
        ];
    }
}
