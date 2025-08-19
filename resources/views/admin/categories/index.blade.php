@extends('layouts.admin')

@push('styles')
<style>
.search-highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
}

.search-form {
    transition: all 0.3s ease;
}

.search-form:focus-within {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.search-results-info {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>
@endpush

@section('title', 'Управление категориями')

@php
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $categories
     */

    /**
     * @var \App\Application\Core\Category\DTO\CategoryDTO $category
     */
@endphp

@section('content')
    <div class="container-fluid px-0">
        <div class="row mb-4">
            <div class="col-md-6 mb-2 mb-md-0">
                <h1 class="h2">Управление категориями</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Добавить категорию
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <form action="{{ route('admin.categories.index') }}" method="GET" class="row g-3 search-form">
                    <div class="col-md-6 col-sm-12 mb-2 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Поиск категорий..."
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <select name="status" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Активные</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Неактивные</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Найти
                        </button>
                    </div>
                    <div class="col-md-1 col-sm-12">
                        @if(request('search') || request('status'))
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i> Очистить
                            </a>
                        @else
                            <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                <i class="fas fa-times me-1"></i> Очистить
                            </button>
                        @endif
                    </div>
                </form>
            </div>
            <div class="card-body">
                @if(request('search') || request('status'))
                    <div class="alert alert-info mb-3 search-results-info">
                        <i class="fas fa-search me-2"></i>
                        @if(request('search'))
                            Поиск по запросу: <strong>"{{ request('search') }}"</strong>
                        @endif
                        @if(request('status'))
                            @if(request('search')) и @endif
                            Фильтр по статусу: <strong>{{ request('status') === '1' ? 'Активные' : 'Неактивные' }}</strong>
                        @endif
                        <span class="ms-2">Найдено: <strong>{{ $categories->total() }}</strong> {{ trans_choice('категорий|категория|категории', $categories->total()) }}</span>
                    </div>
                @endif
                
                @if($categories->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th class="d-none d-md-table-cell">Слаг</th>
                                <th>Активность</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                           class="text-decoration-none fw-bold">
                                            {{ $category->name }}
                                        </a>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $category->slug }}</td>
                                    <td>
                                        @if($category->active)
                                            <span class="badge bg-success">Активна</span>
                                        @else
                                            <span class="badge bg-secondary">Неактивна</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.categories.show', $category->id) }}"
                                               class="btn btn-outline-primary" target="_blank" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                               class="btn btn-outline-secondary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Вы уверены? Это действие может затронуть связанные новости.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            {{ $categories->links() }}
                        </nav>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Категории не найдены. Создайте первую категорию.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
