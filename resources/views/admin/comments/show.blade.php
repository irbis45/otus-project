@extends('layouts.admin')

@section('title', 'Просмотр комментария')

@php
    /**
     * @var \App\Application\Core\Comment\DTO\CommentDTO $comment
     */
@endphp

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Просмотр комментария</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> К списку
            </a>
            <a href="{{ route('admin.comments.edit', $comment->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Редактировать
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title h5 mb-0">Комментарий #{{ $comment->id }}</h3>
                <div>
                    @php
                        $status = $comment->status;
                        $badgeClasses = [
                            'pending' => 'bg-warning',
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                        ];
                    @endphp
                    @if ($status)
                        <span class="badge {{ $badgeClasses[$status->value] ?? 'bg-secondary' }}">
                                                {{ $status->label }}
                                            </span>
                    @else
                        <span class="badge bg-secondary">Статус не задан</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Новость:</strong> <a href="{{ route('admin.news.edit', $comment->newsId) }}">{{ $comment->newsId }}</a></p>
                  {{--  <p class="mb-1"><strong>Публичная ссылка:</strong> <a href="{{ route('news.show', $comment->newsId) }}#comment-{{ $comment->id }}" target="_blank">Перейти к комментарию</a></p>--}}
                    <p class="mb-1"><strong>Автор:</strong> {{ $comment->author->name ?? '#deleted' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $comment->author?->email }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Создан:</strong> {{ $comment?->createdAt->format('d.m.Y H:i') }}</p>
                    <p class="mb-1"><strong>Обновлен:</strong> {{ $comment?->updatedAt->format('d.m.Y H:i') }}</p>
                    @if($comment->parentId)
                        <p class="mb-1"><strong>Ответ на комментарий:</strong> #{{ $comment->parentId }}</p>
                    @endif
                </div>
            </div>

            <div class="mb-4">
                <h4 class="h6">Текст комментария:</h4>
                <div class="p-3 bg-light rounded">{{ $comment->text }}</div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" onsubmit="return confirm('Вы уверены? Это действие нельзя отменить.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Удалить комментарий
                    </button>
                </form>
                <a href="{{ route('admin.comments.edit', $comment->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Редактировать
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
