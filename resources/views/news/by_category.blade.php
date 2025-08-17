@extends('layouts.app')

@php
    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $category
     */
@endphp

@php
    /**
     * @var App\Application\Core\News\DTO\PaginatedResult $news
     */

    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $newsItem
     */
@endphp


@section('title', $category->name)

@section('content')
    <div class="row">
        <div class="col-lg-8 col-md-7">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Главная</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                </ol>
            </nav>

            @if($category->description)
                <div class="alert alert-light mb-4">
                    {{ $category->description }}
                </div>
            @endif

            @if($news->total() > 0)
                @foreach($news as $item)
                    <div class="card mb-4 news-card shadow-sm">
                        <div class="row g-0">
                            <div class="col-md-4">
                                @if($item->thumbnail)
                                    <img src="{{ $item->thumbnail }}" class="img-fluid rounded-start" alt="{{ $item->title }}">
                                @else
                                    <div class="placeholder-300x200 img-fluid rounded-start"></div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $item->title }}</h5>
                                    <p class="card-text text-muted">
                                        <small>
                                            <span><i class="fas fa-calendar me-1"></i> {{ $item->publishedAt->format('d.m.Y') }}</span>
                                            <span><i class="fas fa-user me-1"></i> {{ $item->author->name ?? 'Unknown' }}</span>
                                            <span><i class="fas fa-eye me-1"></i> {{ $item->views }}</span>
                                        </small>
                                    </p>
                                    <p class="card-text">{{ $item->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 150) }}</p>
                                    <a href="{{ route('news.show', $item->slug) }}" class="btn btn-sm btn-outline-primary">Читать полностью</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-center mt-4">
                    <nav>
                        {{ $news->links() }}
                    </nav>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>В этой категории пока нет новостей.
                </div>
                <a href="{{ route('home') }}" class="btn btn-primary">Вернуться на главную</a>
            @endif
        </div>

        <div class="col-lg-4 col-md-5 mt-4 mt-md-0">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Категории</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($popularCategories as $cat)
                            <li class="list-group-item d-flex justify-content-between align-items-center {{ $cat->id == $category->id ? 'active bg-light' : '' }}">
                                <a href="{{ route('news.category', $cat->slug) }}" class="text-decoration-none {{ $cat->id == $category->id ? 'fw-bold' : '' }}">{{ $cat->name }}</a>
                                <span class="badge bg-primary rounded-pill">{{ $cat->newsCount ?:0 }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
