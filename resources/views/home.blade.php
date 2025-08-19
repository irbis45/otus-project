@extends('layouts.app')

@section('title', __('home.title'))

@php
    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $news
     */
@endphp

@php
    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $category
     */
@endphp

@section('content')
    <div class="row">

        @if(count($featuredNews) > 0)
            <div class="col-12 featured-news">
                <h2 class="mb-4">{{ __('home.featured_news') }}</h2>
                <div class="row">
                    @foreach($featuredNews as $feature)
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card h-100 news-card shadow-sm">
                                @if($feature->thumbnail)
                                    <img src="{{ $feature->thumbnail }}" class="card-img-top" alt="{{ $feature->title }}">
                                @else
                                    <div class="placeholder-400x225 card-img-top"></div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $feature->title }}</h5>
                                    <p class="card-text text-muted">
                                        <small>
                                            @if ($feature->category)
                                                <span><i class="fas fa-folder me-1"></i> <a href="{{ route('news.category', $feature->category->slug) }}"
                                                                                            class="text-decoration-none">{{ $feature->category->name }}</a></span>
                                            @endif
                                            <span><i class="fas fa-calendar me-1"></i> {{ $feature->publishedAt->format('d.m.Y') }}</span>
                                            <span><i class="fas fa-eye me-1"></i> {{ $feature->views }}</span>
                                        </small>
                                    </p>
                                    <p class="card-text">{{ $feature->excerpt ?: Str::limit(strip_tags($feature->content), 150) }}</p>
                                    <a href="{{ route('news.show', $feature->slug) }}" class="btn btn-primary mt-auto">{{ __('home.read_more') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="col-lg-8 col-md-7">
            <h2 class="mb-4">{{ __('home.latest_news') }}</h2>
            @if($latestNews->total() > 0)
                @foreach($latestNews as $news)

                    <div class="card mb-4 news-card shadow-sm">
                        <div class="row g-0">
                            <div class="col-md-4">
                                @if($news->thumbnail)
                                    <img src="{{ $news->thumbnail }}" class="img-fluid rounded-start" alt="{{ $news->title }}">
                                @else
                                    <div class="placeholder-300x200 img-fluid rounded-start"></div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $news->title }}</h5>
                                    <p class="card-text text-muted">
                                        <small>
                                            @if ($news->category)
                                                <span><i class="fas fa-folder me-1"></i> <a href="{{ route('news.category', $news->category->slug) }}"
                                                                                        class="text-decoration-none">{{ $news?->category?->name }}</a></span>
                                            @endif
                                            <span><i class="fas fa-calendar me-1"></i> {{ $news->publishedAt->format('d.m.Y') }}</span>
                                            @if ($news->author)
                                                <span><i class="fas fa-user me-1"></i> {{ $news->author->name }}</span>
                                            @endif
                                            <span><i class="fas fa-eye me-1"></i> {{ $news->views }}</span>
                                        </small>
                                    </p>
                                    <p class="card-text">{{ $news->excerpt ?: Str::limit(strip_tags($news->content), 150) }}</p>
                                    <a href="{{ route('news.show', $news->slug) }}" class="btn btn-sm btn-outline-primary">{{ __('home.read_more') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                 <div class="d-flex justify-content-center mt-4">
                     <nav>
                         {{ $latestNews->links() }}
                     </nav>
                 </div>
            @else
                <div class="alert alert-info">
                    {{ __('home.no_news') }}
                </div>
            @endif
        </div>

        <div class="col-lg-4 col-md-5 mt-4 mt-md-0">
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
