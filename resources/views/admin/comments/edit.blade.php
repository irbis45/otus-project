@extends('layouts.admin')

@section('title', 'Редактирование комментария')

@php
    /**
     * @var \App\Application\Core\Comment\DTO\CommentDTO $comment
     */
@endphp

@php
    /**
     * @var \App\Application\Core\Comment\DTO\StatusDTO $status
     */
@endphp

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Редактирование комментария</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Вернуться к списку
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-4">
                    <h5>Информация</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Новость:</strong> <a
                                    href="{{ route('admin.news.edit', $comment->newsId) }}">{{ $comment->newsId }}</a>
                            </p>
                            <p><strong>Автор:</strong> {{ $comment->author->name ?? '#deleted' }}
                                ({{ $comment->author?->email }})</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Создан:</strong> {{ $comment?->createdAt->format('d.m.Y H:i') }}</p>
                            <p><strong>Обновлен:</strong> {{ $comment?->updatedAt->format('d.m.Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- @if($errors->any())
                     <div class="alert alert-danger">
                         <ul>
                             @foreach($errors->all() as $error)
                                 <li>{{ $error }}</li>
                             @endforeach
                         </ul>
                     </div>
                 @endif--}}

                <form action="{{ route('admin.comments.update', $comment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="status" class="form-label">Статус<span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                @foreach($statuses as $status)
                                    <option
                                        value="{{ $status->value }}" {{ old('status', $comment->status->value) == $status->value ? 'selected' : '' }}>
                                        {{ $status->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Комментарий</label>
                        <textarea class="form-control @error('text') is-invalid @enderror" id="text" name="text"
                                  rows="5" required>{{ old('text', $comment->text) }}</textarea>
                        @error('text')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted">ID: {{ $comment->id }}</span>
                        </div>
                        <div>
                            <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary me-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
