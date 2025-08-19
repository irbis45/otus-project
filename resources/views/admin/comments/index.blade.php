@extends('layouts.admin')

@section('title', __('Управление комментариями'))

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

@php
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $comments
     */

    /**
     * @var \App\Application\Core\Comment\DTO\CommentDTO $comment
     */
@endphp

@section('content')
<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col-md-6 mb-2 mb-md-0">
            <h1 class="h2">{{ __('Управление комментариями') }}</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <form action="{{ route('admin.comments.index') }}" method="GET" class="row g-3 search-form">
                <div class="col-md-3 col-sm-12 mb-2 mb-md-0">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="{{ __('comments.search.placeholder') }}"
                               value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                    <div class="position-relative">
                        <input type="text"
                               id="news-search"
                               class="form-control"
                               placeholder="{{ __('comments.filters.search_news') }}"
                               value="{{ $selectedNews ? $selectedNews->getTitle() : '' }}"
                               autocomplete="off">
                        <input type="hidden"
                               name="news_id"
                               id="news-id"
                               value="{{ request('news_id') }}">
                        <div id="news-dropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                        @if(request('news_id'))
                            <button type="button"
                                    id="clear-news"
                                    class="btn btn-sm btn-outline-secondary position-absolute"
                                    style="right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 6px;">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <select name="status" class="form-select">
                        <option value="">{{ __('comments.filters.all_statuses') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> {{ __('comments.actions.search') }}
                    </button>
                </div>
                <div class="col-md-2 col-sm-6">
                    @if(request('search') || request('news_id') || request('status'))
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i> {{ __('comments.actions.clear') }}
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary w-100" disabled>
                            <i class="fas fa-times me-1"></i> {{ __('comments.actions.clear') }}
                        </button>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body">
            @if(request('search') || request('news_id') || request('status'))
                <div class="alert alert-info mb-3 search-results-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('comments.search.results', [
                        'count' => $comments->total(),
                        'comments' => trans_choice('comments.comment|comments.comment_genitive|comments.comments_genitive', $comments->total())
                    ]) }}
                    @if(request('search'))
                        <br><strong>{{ __('comments.search.query') }}:</strong> "{{ request('search') }}"
                    @endif
                    @if(request('news_id'))
                        <br><strong>{{ __('comments.filters.news') }}:</strong>
                        {{ $selectedNews ? $selectedNews->getTitle() : '#' . request('news_id') }}
                    @endif
                    @if(request('status'))
                        <br><strong>{{ __('comments.filters.status') }}:</strong>
                        @php
                            $selectedStatus = collect($statuses)->firstWhere('value', request('status'));
                        @endphp
                        {{ $selectedStatus ? $selectedStatus->label : request('status') }}
                    @endif
                </div>
            @endif

            @if($comments->total() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                                                    <thead>
                            <tr>
                                <th>{{ __('comments.table.id') }}</th>
                                <th>{{ __('comments.table.news') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('comments.table.author') }}</th>
                                <th>{{ __('comments.table.comment') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('comments.table.date') }}</th>
                                <th>{{ __('comments.table.status') }}</th>
                                <th>{{ __('comments.table.actions') }}</th>
                            </tr>
                            </thead>
                        <tbody>
                            @foreach($comments as $comment)

                                <tr>
                                    <td>{{ $comment->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.news.edit', $comment->newsId) }}" class="text-decoration-none">
                                            {{ $comment->newsId }}
                                        </a>
                                        <div class="d-md-none mt-1">
                                            <small class="text-muted">{{ $comment->author?->name  ?? '#deleted'}}</small>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        @if($comment->author)
                                            {!! \App\Helpers\SearchHelper::highlight($comment->author->name, request('search')) !!}
                                        @else
                                            #deleted
                                        @endif
                                    </td>
                                    <td class="text-break">{!! \App\Helpers\SearchHelper::highlight(Str::limit($comment->text, 50), request('search')) !!}</td>
                                    <td class="d-none d-md-table-cell">{{ $comment?->createdAt->format('d.m.Y H:i') }}</td>
                                    <td>
                                        @php
                                            $status = $comment->status;
                                            $badgeClasses = [
                                                'pending' => 'bg-warning',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                            ];
                                        @endphp
                                        @if ($status)
                                            <span class="badge {{ $badgeClasses[$status->value] ?? 'bg-secondary' }}">
                                                {{ $status->label }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('comments.status.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.comments.show', $comment->id) }}" class="btn btn-outline-primary" target="_blank" title="{{ __('comments.actions.view') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.comments.edit', $comment->id) }}" class="btn btn-outline-secondary" title="{{ __('comments.actions.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены? Это действие нельзя отменить.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ __('comments.actions.delete') }}">
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
                        {{ $comments->links() }}
                    </nav>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>{{ __('comments.search.no_results') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Ищем элементы формы
    const searchInput = document.querySelector('input[name="search"]');
    let searchForm = document.querySelector('form.search-form');

    // Если не нашли по классу, ищем по action
    if (!searchForm) {
        searchForm = document.querySelector('form[action*="admin.comments.index"]');
    }

    // Если и это не сработало, ищем любую форму на странице
    if (!searchForm) {
        searchForm = document.querySelector('form');
    }

    const newsSearchInput = document.getElementById('news-search');
    const newsIdInput = document.getElementById('news-id');
    const newsDropdown = document.getElementById('news-dropdown');
    const clearNewsButton = document.getElementById('clear-news');

    let searchTimeout;
    let newsSearchTimeout;


    // Проверяем существование обязательных элементов
    if (!searchInput || !searchForm) {
        console.error('Обязательные элементы формы не найдены');
        console.error('searchInput:', searchInput);
        console.error('searchForm:', searchForm);
        return;
    }

    // Автопоиск при вводе с задержкой (только для текстового поиска)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    // НЕ отправляем форму автоматически при поиске по новостям
                    if (newsIdInput && !newsIdInput.value) {
                        searchForm.submit();
                    }
                }
            }, 500);
        });

        // Очистка поиска при нажатии Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                searchForm.submit();
            }
        });
    }

    // Автообновление при изменении фильтров статуса
    const statusFilter = document.querySelector('select[name="status"]');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            searchForm.submit();
        });
    }

        // Автокомплит для новостей
    if (newsSearchInput) {

        // Предотвращаем отправку формы при вводе в поле новостей
        newsSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        newsSearchInput.addEventListener('input', function() {
            const query = this.value;

            clearTimeout(newsSearchTimeout);

            if (query.length < 2) {
                newsDropdown.style.display = 'none';
                return;
            }

            newsSearchTimeout = setTimeout(() => {
                const url = `{{ route('admin.api.news.search') }}?q=${encodeURIComponent(query)}&limit=10`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        newsDropdown.innerHTML = '';

                        if (data.length === 0) {
                            newsDropdown.innerHTML = '<div class="dropdown-item text-muted">Новости не найдены</div>';
                        } else {
                            data.forEach(news => {
                                const item = document.createElement('div');
                                item.className = 'dropdown-item cursor-pointer';
                                item.style.cursor = 'pointer';
                                item.textContent = news.title;
                                item.addEventListener('click', function() {
                                    newsSearchInput.value = news.title;
                                    newsIdInput.value = news.id;
                                    newsDropdown.style.display = 'none';
                                    // НЕ отправляем форму автоматически
                                    // Пользователь должен нажать "Найти" или "Применить фильтры"
                                });
                                newsDropdown.appendChild(item);
                            });
                        }

                        newsDropdown.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Ошибка поиска новостей:', error);
                        newsDropdown.innerHTML = '<div class="dropdown-item text-danger">Ошибка загрузки</div>';
                        newsDropdown.style.display = 'block';
                    });
            }, 300);
        });

        // Скрыть dropdown при клике вне поля
        document.addEventListener('click', function(e) {
            if (!newsSearchInput.contains(e.target) && !newsDropdown.contains(e.target)) {
                newsDropdown.style.display = 'none';
            }
        });

        // Очистка выбранной новости
        if (clearNewsButton) {
            clearNewsButton.addEventListener('click', function() {
                newsSearchInput.value = '';
                newsIdInput.value = '';
                searchForm.submit();
            });
        }
    }

    // Обработчик кнопки "Применить фильтры"
    const applyFiltersButton = document.getElementById('apply-filters');
    if (applyFiltersButton) {
        applyFiltersButton.addEventListener('click', function() {
            searchForm.submit();
        });
    }


});
</script>
@endpush
