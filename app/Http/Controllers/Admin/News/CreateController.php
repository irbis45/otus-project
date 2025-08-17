<?php

namespace App\Http\Controllers\Admin\News;

use App\Application\Core\Category\UseCases\Queries\FetchAll\Fetcher as CategoriesFetcher;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\UseCases\Commands\Create\Command;
use App\Application\Core\News\UseCases\Commands\Create\Handler as CreateHandler;
use App\Application\Core\User\UseCases\Queries\FetchAll\Fetcher as UsersFetcher;
use App\Events\NewsPublished;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateNewsRequest;
use DateTimeImmutable;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Application\Core\News\Services\ThumbnailService;

class CreateController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(AuthManager $authManager, CategoriesFetcher $categoriesFetcher, UsersFetcher $usersFetcher): View
    {
        Gate::authorize('news.create');

        $isAdmin = $authManager->user()->hasRole('admin');

        $categories = $categoriesFetcher->fetch()->results;
        $authors = $isAdmin ? $usersFetcher->fetch()->results : [];

        return view('admin.news.create', compact('categories', 'authors', 'isAdmin'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateNewsRequest $request, CreateHandler $createNewsUseCase, AuthManager $authManager, ThumbnailService $thumbnailService): RedirectResponse
    {
        Gate::authorize('news.create');

        try {
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $thumbnailService->saveUploadedFile($request->file('thumbnail'));
            }

            $command = new Command(
                title: $request->get('title'),
                content: $request->get('content'),
                excerpt: $request->filled('excerpt') ? $request->get('excerpt') : null,
                authorId: $authManager->user()->getAuthIdentifier(),
                categoryId: $request->get('category_id'),
                publishedAt: $request->filled('published_at') ? new DateTimeImmutable($request->get('published_at')) : new DateTimeImmutable('now'),
                active: $request->boolean('active', true),
                featured: $request->boolean('featured'),
                thumbnail: $thumbnailPath,
            );

            $news = $createNewsUseCase->handle($command);

            if ($news->active) {
                NewsPublished::dispatch($news->id, $news->title, $news->content);
            }

        } catch (NewsNotFoundException) {
            throw new NotFoundHttpException('News not found');
        }

        return redirect()->route('admin.news.index');
    }
}
