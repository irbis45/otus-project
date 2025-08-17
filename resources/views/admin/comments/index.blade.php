@extends('layouts.admin')

@section('title', 'Управление комментариями')

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
            <h1 class="h2">Управление комментариями</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($comments->total() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Новость</th>
                                <th class="d-none d-md-table-cell">Автор</th>
                                <th>Комментарий</th>
                                <th class="d-none d-md-table-cell">Дата</th>
                                <th>Статус</th>
                                <th>Действия</th>
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
                                            <small class="text-muted">{{ $comment->author->name  ?? '#deleted'}}</small>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $comment->author->name ?? '#deleted' }}</td>
                                    <td class="text-break">{{ Str::limit($comment->text, 50) }}</td>
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
                                            <span class="badge bg-secondary">Статус не задан</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.comments.show', $comment->id) }}" class="btn btn-outline-primary" target="_blank" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.comments.edit', $comment->id) }}" class="btn btn-outline-secondary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены? Это действие нельзя отменить.')">
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
                        {{ $comments->links() }}
                    </nav>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Комментарии не найдены.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
