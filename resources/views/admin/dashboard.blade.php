@extends('layouts.admin')

@section('title', 'Панель управления')
@section('heading', 'Панель управления')

@php
    /**
     * @var ?\App\ViewModels\UserViewModel $authUser
     */
@endphp

@section('content')
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="card border-primary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-newspaper fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Новости</h5>
                    <h2 class="display-6 fw-bold">{{ $totalNews ?? 0 }}</h2>
                    <p class="card-text">Всего новостей в системе</p>
                    @if ($authUser?->hasPermission('view_news') || $authUser?->hasRole('admin'))
                        <a href="{{ route('admin.news.index') }}" class="btn btn-primary">Управление новостями</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <i class="fas fa-list fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Категории</h5>
                    <h2 class="display-6 fw-bold">{{ $totalCategories ?? 0 }}</h2>
                    <p class="card-text">Всего категорий в системе</p>
                    @if ($authUser?->hasPermission('view_categories') || $authUser?->hasRole('admin'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-success">Управление категориями</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-warning h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Пользователи</h5>
                    <h2 class="display-6 fw-bold">{{ $totalUsers ?? 0 }}</h2>
                    <p class="card-text">Всего пользователей в системе</p>
                    @if ($authUser?->hasPermission('view_users') || $authUser?->hasRole('admin'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-warning">Управление пользователями</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-info h-100">
                <div class="card-body text-center">
                    <i class="fas fa-comments fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Комментарии</h5>
                    <h2 class="display-6 fw-bold">{{ $totalComments ?? 0 }}</h2>
                    <p class="card-text">Всего комментариев в системе</p>
                    @if ($authUser?->hasPermission('view_comments') || $authUser?->hasRole('admin'))
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-info">Управление комментариями</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
