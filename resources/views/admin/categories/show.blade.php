@extends('layouts.admin')
@php
    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $category
     */
@endphp

@section('title', $category->name)

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Просмотр категории</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> К списку
                </a>
                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Редактировать
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>ID:</strong> {{ $category->id }}</p>
                        <p class="mb-1"><strong>Название:</strong> {{ $category->name }}</p>
                        <p class="mb-1"><strong>Слаг:</strong> {{ $category->slug }}</p>
                        <p class="mb-1"><strong>Активность:</strong> {{ $category->active ? 'Активна' : 'Неактивна' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($category->description)
            <div class="mb-4">
                <h4 class="h6">Описание:</h4>
                <div class="p-3 bg-light rounded">{{ $category->description }}</div>
            </div>
        @endif
    </div>
@endsection
