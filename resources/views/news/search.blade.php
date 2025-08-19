@extends('layouts.app')

@php
    /**
     * @var App\Application\Core\News\DTO\PaginatedResult $news
     */
@endphp

@php
    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $newsItem
     */
@endphp

@section('title', __('news.search.title', ['query' => $searchQuery]))

@section('content')
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4">{{ __('news.search.results', ['query' => $searchQuery]) }}</h1>

            @if($news->total() > 0)
                <p class="text-muted mb-4">{{ __('news.search.found', ['count' => $news->total(), 'news' => trans_choice('новостей|новость|новости', $news->total())]) }}</p>

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
                                            @if($item->category)
                                                <i class="fas fa-folder me-1"></i> <a href="{{ route('news.category', $item->category->slug) }}" class="text-decoration-none">{{ $item->category->name }}</a> |
                                            @endif

                                            <i class="fas fa-calendar me-1"></i> {{ $item->publishedAt->format('d.m.Y') }} |
                                            @if ( $item->author)
                                                <i class="fas fa-user me-1"></i> {{ $item->author->name }} |
                                            @endif
                                            <i class="fas fa-eye me-1"></i> {{ $item->views }}
                                        </small>
                                    </p>
                                    <p class="card-text">{{ $item->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 150) }}</p>
                                    <a href="{{ route('news.show', $item->slug) }}" class="btn btn-sm btn-outline-primary">{{ __('home.read_more') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-center">
                    <nav>
                        {{ $news->links() }}
                    </nav>

                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>{{ __('news.search.nothing_found') }}
                </div>
                <a href="{{ route('home') }}" class="btn btn-primary">{{ __('news.search.back_to_home') }}</a>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">{{ __('home.categories') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($popularCategories as $category)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('news.category', $category->slug) }}" class="text-decoration-none">{{ $category->name }}</a>
                                <span class="badge bg-primary rounded-pill">{{ $category->newsCount ?:0 }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
