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

@section('title', 'Управление новостями')

@php
    /**
     * @var App\Application\Core\News\DTO\PaginatedResult $news
     */

    /**
     * @var \App\Application\Core\News\DTO\NewsDTO $newsItem
     */
@endphp

@section('content')
    <div class="container-fluid px-0">
        <div class="row mb-4">
            <div class="col-md-6 mb-2 mb-md-0">
                <h1 class="h2">Управление новостями</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Добавить новость
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <form action="{{ route('admin.news.index') }}" method="GET" class="row g-3 search-form">
                    <div class="col-md-4 col-sm-12 mb-2 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Поиск новостей..."
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <select name="status" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Активные</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Неактивные</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <select name="orderBy" class="form-select">
                            <option value="id" {{ request('orderBy') === 'id' ? 'selected' : '' }}>По ID</option>
                            <option value="title" {{ request('orderBy') === 'title' ? 'selected' : '' }}>По заголовку</option>
                            <option value="created_at" {{ request('orderBy') === 'created_at' ? 'selected' : '' }}>По дате создания</option>
                            <option value="published_at" {{ request('orderBy') === 'published_at' ? 'selected' : '' }}>По дате публикации</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-sm-6 mb-2 mb-md-0">
                        <select name="direction" class="form-select">
                            <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>↓</option>
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>↑</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Найти
                        </button>
                    </div>
                    <div class="col-md-1 col-sm-12">
                        @if(request('search') || request('status') || request('orderBy') || request('direction'))
                            <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary w-100">
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
                        <span class="ms-2">Найдено: <strong>{{ $news->total() }}</strong> {{ trans_choice('новостей|новость|новости', $news->total()) }}</span>
                    </div>
                @endif

                @if($news->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'id', 'direction' => request('orderBy') === 'id' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark">
                                        ID
                                        @if(request('orderBy') === 'id')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'title', 'direction' => request('orderBy') === 'title' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark">
                                        Заголовок
                                        @if(request('orderBy') === 'title')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="d-none d-md-table-cell">Категория</th>
                                <th class="d-none d-lg-table-cell">Автор</th>
                                <th>Статус</th>
                                <th class="d-none d-lg-table-cell">
                                    <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'created_at', 'direction' => request('orderBy') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark">
                                        Дата создания
                                        @if(request('orderBy') === 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="d-none d-md-table-cell">
                                    <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'published_at', 'direction' => request('orderBy') === 'published_at' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark">
                                        Опубликовано
                                        @if(request('orderBy') === 'published_at')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($news as $newsItem)
                                <tr>
                                    <td>{{ $newsItem->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.news.edit', $newsItem->id) }}"
                                           class="text-decoration-none fw-bold">
                                            {{ Str::limit($newsItem->title, 50) }}
                                        </a>
                                         <div class="d-md-none mt-1">
                                             <small class="text-muted">{{ $newsItem->category->name ?? '#deleted' }}</small>
                                         </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $newsItem->category->name ?? '#deleted' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $newsItem->author->name ?? '#deleted' }}</td>
                                    <td>
                                        @if($newsItem->active)
                                            <span class="badge bg-success">Активна</span>
                                        @else
                                            <span class="badge bg-secondary">Неактивна</span>
                                        @endif

                                        @if($newsItem->featured)
                                            <span class="badge bg-info">Главная</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">{{ $newsItem->createdAt?->format('d.m.Y H:i') }}</td>
                                    <td class="d-none d-md-table-cell">{{ $newsItem->publishedAt?->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.news.show', $newsItem->id) }}"
                                               class="btn btn-outline-primary" target="_blank" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.news.edit', $newsItem->id) }}"
                                               class="btn btn-outline-secondary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.news.destroy', $newsItem->id) }}" method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Вы уверены? Это действие нельзя отменить.')">
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
                            {{ $news->links() }}
                        </nav>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Новости не найдены. Создайте первую новость.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
