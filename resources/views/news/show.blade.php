@extends('layouts.app')

@php
    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $news
     */
@endphp

@php
    /**
     * @var \App\Application\Core\Comment\DTO\ResultDTO $comments
     */
@endphp

@php
    /**
     * @var \App\Application\Core\Comment\DTO\CommentDTO $comment
     */
@endphp

@php
    /**
     * @var ?\App\ViewModels\UserViewModel $authUser
     */
@endphp

@section('title', $news->title)

@section('content')
    <div class="row">
        <div class="col-lg-8 col-md-7">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Главная</a></li>
                    @if ($news->category)
                        <li class="breadcrumb-item"><a href="{{ route('news.category', $news->category->slug) }}">{{ $news->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $news->title }}</li>
                </ol>
            </nav>

            <article class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title h2">{{ $news->title }}</h1>
                    <div class="d-flex flex-wrap justify-content-between text-muted mb-4">
                        <div class="mb-2 mb-sm-0">
                            @if ($news->author)
                                <span class="me-2"><i class="fas fa-user me-1"></i> {{ $news->author->name }}</span>
                            @endif
                            @if ($news->category)
                                <span class="me-2"><i class="fas fa-folder me-1"></i> {{ $news->category->name }}</span>
                            @endif
                            <span><i class="fas fa-calendar me-1"></i> {{ $news->publishedAt->format('d.m.Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="me-2"><i class="fas fa-eye me-1"></i> {{ $news->views }}</span>
                        </div>
                    </div>

                    @if($news->thumbnail)
                        <img src="{{ $news->thumbnail }}" class="img-fluid rounded mb-4" alt="{{ $news->title }}">
                    @endif

                    <div class="card-text">
                        {!! $news->content !!}
                    </div>

                    <hr class="my-4">

                    <div class="d-flex flex-wrap justify-content-between">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-2 mb-sm-0"><i class="fas fa-arrow-left me-2"></i>Вернуться на главную</a>
                        <div class="social-share">
                            <span class="me-2">Поделиться:</span>
                            <a href="#" class="btn btn-sm btn-outline-primary me-1"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-info me-1"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-success"><i class="fab fa-telegram"></i></a>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Комментарии -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h3 class="card-title h5 mb-0">
                        <i class="fas fa-comments me-2"></i>Комментарии
                    </h3>
                </div>
                <div class="card-body">
                    @if ($authUser)
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="news_id" value="{{ $news->id }}">
                            <div class="mb-3">
                                <label for="content" class="form-label">Ваш комментарий</label>
                                <textarea class="form-control @error('text') is-invalid @enderror" id="text" name="text" rows="3" required>{{ old('text') }}</textarea>
                                @error('text')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Отправить</button>
                        </form>
                    @else
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>Чтобы оставить комментарий, пожалуйста, <a href="{{ route('login') }}">войдите</a> или <a href="{{ route('register') }}">зарегистрируйтесь</a>.
                        </div>
                    @endif

                    <div class="comments-list">
                            @forelse($comments as $comment)
                                <x-comment :comment="$comment" />
                            @empty
                                <div class="text-center text-muted">
                                    <i class="fas fa-comment-slash fa-2x mb-3"></i>
                                    <p>Нет комментариев. Будьте первым, кто оставит комментарий!</p>
                                </div>
                            @endforelse
                        </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-5 mt-4 mt-md-0">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Популярные категории</h5>
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

@section('body_end')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.reply-button').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const form = document.getElementById(`reply-form-${id}`);
                    if (form) {
                        form.classList.remove('d-none');
                    }
                });
            });

            document.querySelectorAll('.cancel-reply').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const form = document.getElementById(`reply-form-${id}`);
                    if (form) {
                        form.classList.add('d-none');
                    }
                });
            });
        });
    </script>
@endsection
