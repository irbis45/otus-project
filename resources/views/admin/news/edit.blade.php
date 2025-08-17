@extends('layouts.admin')

@section('title', 'Редактирование новости')

@php
    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $news
     */

    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $category
     */
@endphp

@section('content')

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Редактирование новости</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.news.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> К списку
                </a>
                <a href="{{ route('admin.news.show', $news->id) }}" class="btn btn-info">
                    <i class="fas fa-eye me-1"></i> Просмотр
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="author" class="form-label"><strong>Автор:</strong></label>
                            <input type="text" id="author" class="form-control" value="{{ $news->author->name ?? '#deleted' }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Заголовок <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                               name="title" value="{{ old('title', $news->title) }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Категория <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                                    name="category_id" required>
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->id }}" {{ old('category_id', $news->category->id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="published_at" class="form-label">Дата публикации</label>
                            <input type="datetime-local"
                                   class="form-control @error('published_at') is-invalid @enderror" id="input_published_at"
                                   name="published_at"
                                   value="{{ old('published_at', $news->publishedAt?->format('Y-m-d\TH:i')) }}">
                            @error('published_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Краткое описание</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $news->excerpt) }}</textarea>
                        @error('excerpt')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Краткое описание статьи для отображения в списках (необязательно)</div>
                    </div>


                    <div class="mb-3">
                        <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content"
                                  name="content" rows="10" required>{{ old('content', $news->content) }}</textarea>
                        @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Изображение</label>
                        @if($news->thumbnail)
                            <div class="mb-2">
                                <img src="{{ $news->thumbnail }}" alt="{{ $news->title }}" class="img-thumbnail" style="max-height: 200px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                                    <label class="form-check-label" for="delete_image">
                                        Удалить текущее изображение
                                    </label>
                                </div>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail">
                        @error('thumbnail')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Рекомендуемый размер: 800x450 пикселей. Максимальный размер: 2MB.</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $news->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">
                                    Активна (опубликована)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" {{ old('featured', $news->featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">
                                    Показывать в главных новостях
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted">ID: {{ $news->id }} | Создано: {{ $news->createdAt->format('d.m.Y H:i') }} | Просмотры: {{ $news?->views }}</span>
                        </div>
                        <div>
                            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary me-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>

    </script>
@endsection
