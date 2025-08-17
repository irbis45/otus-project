<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Новостной портал') }} - @yield('title', 'Главная')</title>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        main {
            flex: 1 0 auto;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .featured-news {
            margin-bottom: 2rem;
        }
        .news-card {
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        .news-card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 2rem 0;
            flex-shrink: 0;
        }
        /* Адаптивные стили */
        @media (max-width: 767.98px) {
            .navbar-collapse {
                margin-top: 1rem;
            }
            .nav-item {
                margin-bottom: 0.5rem;
            }
            .card-title {
                font-size: 1.25rem;
            }
            .featured-news .card {
                margin-bottom: 1.5rem;
            }
            .search-form {
                margin: 0.5rem 0;
                width: 100%;
            }
            .footer {
                text-align: center;
            }
            .footer .text-md-end {
                text-align: center !important;
                margin-top: 1rem;
            }
            .social-icons {
                margin-bottom: 1rem;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            /* Улучшение читаемости мета-информации */
            .card-text.text-muted small {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }
        /* Улучшение отображения новостей в списке */
        @media (max-width: 575.98px) {
            .news-card .row {
                flex-direction: column;
            }
            .news-card .col-md-4 {
                margin-bottom: 1rem;
            }
        }
    </style>

    @yield('head')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('head_end')
</head>
@php
    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $popularCategory
     */
@endphp

@php
    /**
     * @var ?\App\ViewModels\UserViewModel $authUser
     */
@endphp
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">Новостной портал</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                   @include('partials.menu.top.index')

                    <form class="d-flex me-lg-3 mb-3 mb-lg-0 search-form" action="{{ route('news.search') }}" method="GET">
                        <input class="form-control me-2" type="search" name="query" placeholder="Поиск новостей..." required value="{{ $searchQuery ?? '' }}">
                        <button class="btn btn-outline-light" type="submit">Поиск</button>
                    </form>

                    <ul class="navbar-nav">
                        @if($authUser)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <img src="{{ userlogo($authUser->name()) }}" alt="User Logo" class="rounded-circle me-2 user-logo">
                                    {{ $authUser->name() }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if(Auth::user()->is_admin)
                                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Админ-панель</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Мой профиль</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">{{ __('Выход') }}</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>

                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Вход') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Регистрация') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>О проекте</h5>
                    <p>Новостной портал - ваш источник актуальной информации и новостей со всего мира.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Категории</h5>
                    <ul class="list-unstyled">
                        @foreach(array_slice($popularCategories, 0, 5) as $popularCategory)
                            <li><a href="{{ route('news.category', $popularCategory->slug) }}" class="text-white">{{ $popularCategory->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Контакты</h5>
                    <p><i class="fas fa-envelope me-2"></i> info@newsportal.com</p>
                    <p><i class="fas fa-phone me-2"></i> +7 (999) 123-45-67</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4 border-white">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-0">&copy; {{ date('Y') }} Новостной портал. Все права защищены.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Политика конфиденциальности</a>
                    <a href="#" class="text-white">Условия использования</a>
                </div>
            </div>
        </div>
    </footer>

    @yield('body_end')
</body>
</html>
