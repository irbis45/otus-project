@extends('layouts.admin')

@section('title', 'Редактирование пользователя')

@php
    /**
     * @var \App\Application\Core\User\DTO\UserDTO $user
     */
@endphp

@php
    /**
     * @var \App\Application\Core\Role\DTO\RoleDTO $role
     */
@endphp

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Редактирование пользователя</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> К списку
                </a>
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info">
                    <i class="fas fa-eye me-1"></i> Просмотр
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">{{ $user->name }}</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Имя <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                               name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                               name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Оставьте пустым, если не хотите менять пароль.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Подтверждение нового пароля</label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation">
                    </div>

                    <div class="mb-3">
                        <label for="roles" class="form-label">Роли</label>
                        <select name="roles[]" id="roles" class="form-select @error('roles') is-invalid @enderror" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}"
                                        @if(collect(old('roles', $user->roles))->contains($role->slug)) selected @endif>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('roles')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('roles.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <span
                                class="text-muted">ID: {{ $user->id }} | Регистрация: {{ $user->createdAt->format('d.m.Y H:i') }}</span>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
