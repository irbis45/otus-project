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

@section('title', __('Управление пользователями'))
@php
    /**
     * @var Illuminate\Pagination\LengthAwarePaginator $users
     */

    /**
     * @var \App\Application\Core\User\DTO\UserDTO $user
     */
@endphp
@section('content')
    <div class="container-fluid px-0">
        <div class="row mb-4">
            <div class="col-md-6 mb-2 mb-md-0">
                <h1 class="h2">{{ __('Управление пользователями') }}</h1>
            </div>
            <div class="col-md-6 text-md-end">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('users.actions.add') }}
                    </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 search-form">
                    <div class="col-md-8 col-sm-6 mb-2 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="{{ __('users.search.placeholder') }}" 
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-3 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> {{ __('users.actions.search') }}
                        </button>
                    </div>
                    <div class="col-md-2 col-sm-3">
                        @if(request('search'))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i> {{ __('users.actions.clear') }}
                            </a>
                        @else
                            <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                <i class="fas fa-times me-1"></i> {{ __('users.actions.clear') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
            <div class="card-body">
                @if(request('search'))
                    <div class="alert alert-info mb-3 search-results-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('users.search.results', ['query' => request('search'), 'count' => $users->total(), 'users' => trans_choice('users.user|users.user_genitive|users.users_genitive', $users->total())]) }}
                    </div>
                @endif
                
                @if($users->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('users.table.id') }}</th>
                                <th>{{ __('users.table.name') }}</th>
                                <th>{{ __('users.table.email') }}</th>
                                <th>{{ __('users.table.roles') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('users.table.registration') }}</th>
                                <th>{{ __('users.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                           class="text-decoration-none">
                                            {!! \App\Helpers\SearchHelper::highlight($user->name, request('search')) !!}
                                        </a>
                                    </td>
                                    <td class="text-break">{!! \App\Helpers\SearchHelper::highlight($user->email, request('search')) !!}</td>
                                    <td>
                                        @if(!empty($user->roles) && count($user->roles) > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-secondary me-1">{{ ucfirst($role) }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">{{ __('users.table.no_roles') }}</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $user->createdAt->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.users.show', $user->id) }}"
                                               class="btn btn-outline-primary" title="{{ __('users.actions.view') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                               class="btn btn-outline-secondary" title="{{ __('users.actions.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user->id) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Вы уверены? Это действие может быть необратимо.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"
                                                            title="{{ __('users.actions.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('users.table.no_users') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            {{ $users->links() }}
                        </nav>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>{{ __('users.search.no_results') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = document.querySelector('form[action*="admin.users.index"]');
    let searchTimeout;

    // Автопоиск при вводе с задержкой
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 2 || this.value.length === 0) {
                searchForm.submit();
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
});
</script>
@endpush
