@extends('layouts.admin')

@section('title', $news->title)

@php
    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $news
     */
@endphp

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Просмотр новости</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.news.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> К списку
                </a>
                <a href="{{ route('admin.news.edit', $news->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Редактировать
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title h5 mb-0">{{ $news->title }}</h3>
                </div>
                <div>
                    @if($news->active)
                        <span class="badge bg-success">Активна</span>
                    @else
                        <span class="badge bg-secondary">Неактивна</span>
                    @endif

                    @if($news->featured)
                        <span class="badge bg-info">Главная</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Категория:</strong> {{ $news->category->name ?? '#deleted' }}</p>
                        <p class="mb-1"><strong>Автор:</strong> {{ $news->author->name ?? '#deleted' }}</p>
                        <p class="mb-1"><strong>Дата публикации:</strong> {{ $news->publishedAt->format('d.m.Y H:i') }}</p>
                        <p class="mb-1"><strong>Просмотры:</strong> {{ $news?->views }}</p>
                        <p class="mb-1"><strong>Комментарии:</strong> {{--{{ $news->comments->count() }}--}}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Создано:</strong> {{ $news->createdAt->format('d.m.Y H:i') }}</p>
                        <p class="mb-1"><strong>Обновлено:</strong> {{ $news->updatedAt->format('d.m.Y H:i') }}</p>
                        <p class="mb-1"><strong>Слаг:</strong> {{--{{ $news->slug }}--}}</p>
                        <p class="mb-1">
                            <strong>Публичная ссылка:</strong>
                            <a href="{{--{{ route('news.show', $news->slug) }}--}}" target="_blank">{{--{{ route('news.show', $news->slug) }}--}}</a>
                        </p>
                    </div>
                </div>

                @if($news->thumbnail)
                    <div class="mb-4">
                        <h4 class="h6">Изображение:</h4>
                        <img src="{{ $news->thumbnail }}" alt="{{ $news->title }}" class="img-fluid rounded mb-2" style="max-height: 300px;">
                    </div>
                @endif

                @if($news->excerpt)
                    <div class="mb-4">
                        <h4 class="h6">Краткое описание:</h4>
                        <div class="p-3 bg-light rounded">{{ $news->excerpt }}</div>
                    </div>
                @endif


                <div class="mb-4">
                    <h4 class="h6">Содержание:</h4>
                    <div class="content-box p-3 border rounded">
                        {!! $news->content !!}
                    </div>
                </div>

                <form action="{{ route('admin.news.destroy', $news->id) }}" method="POST" class="mt-4 text-end" onsubmit="return confirm('Вы уверены, что хотите удалить эту новость? Это действие нельзя отменить.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Удалить новость
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
