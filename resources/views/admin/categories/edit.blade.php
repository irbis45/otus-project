@extends('layouts.admin')

@php
    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $category
     */
@endphp

@section('title', 'Редактирование категории')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Редактирование категории</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Вернуться к списку
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active"
                                   value="1" {{ old('active', $category->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Активна
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                               name="name" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted">ID: {{ $category->id }}</span>
                        </div>
                        <div>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

