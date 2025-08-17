@props(['comment'])

@php
    /**
     * @var \App\Application\Core\Comment\DTO\CommentDTO $comment
     */
@endphp

@php
    /**
     * @var ?\App\ViewModels\UserViewModel $authUser
     */
@endphp
<div class="comment mb-4" id="comment-{{ $comment->id }}">
    <div class="d-flex">
        <div class="flex-shrink-0">
            <div class="avatar bg-light rounded-circle p-3 shadow">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="flex-grow-1 ms-3">
            <div class="d-flex align-items-center mb-1 flex-wrap">
                <h5 class="mb-0 me-2">{{ $comment->author?->name ?? 'Гость' }}</h5>
                <small class="text-muted">{{ $comment->createdAt?->format('d.m.Y H:i') }}</small>
            </div>
            <p>{{ $comment->text }}</p>

            @if ($authUser)
                @if(is_null($comment->parentId))
                    <div class="d-flex flex-wrap mb-2">
                        <button class="btn btn-sm btn-link px-0 reply-button me-3" data-id="{{ $comment->id }}">
                            <i class="fas fa-reply me-1"></i>Ответить
                        </button>
                    </div>
                    <div class="reply-form mt-2 d-none" id="reply-form-{{ $comment->id }}">
                        <form action="{{ route('comments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="news_id" value="{{ $comment->newsId }}">
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <div class="mb-3">
                                <textarea class="form-control form-control-sm" name="text" rows="2" required placeholder="Ваш ответ..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Отправить</button>
                            <button type="button" class="btn btn-sm btn-light cancel-reply" data-id="{{ $comment->id }}">Отмена</button>
                        </form>
                    </div>
                @endif
            @endif

            @if($comment->replies)
                <div class="replies mt-3 ms-5">
                    @foreach($comment->replies as $reply)
                        <x-comment :comment="$reply" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
