@extends('layouts.app')

@section('title', __('categories.title'))

@section('content')
    <h1 class="mb-4">{{ __('categories.title') }}</h1>

    @if($categories->total() > 0)
        <div class="list-group">
            @foreach($categories as $category)
                <a href="{{ route('news.category', $category->slug) }}" class="list-group-item list-group-item-action text-dark">
                    <strong>{{ $category->name }}</strong>
                    @if($category->description)
                        <p class="mb-0 small text-muted">{{ Str::limit($category->description, 100) }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            <nav>
                {{ $categories->links() }}
            </nav>
        </div>
    @else
        <p>{{ __('categories.not_found') }}</p>
    @endif
@endsection
