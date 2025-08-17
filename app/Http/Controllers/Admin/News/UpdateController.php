<?php

namespace App\Http\Controllers\Admin\News;

use App\Application\Core\Category\UseCases\Queries\FetchAll\Fetcher as CategoriesFetcher;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Services\ThumbnailService;
use App\Application\Core\News\UseCases\Commands\Update\Command;
use App\Application\Core\News\UseCases\Commands\Update\Handler as UpdateHandler;
use App\Application\Core\News\UseCases\Queries\FetchById\Fetcher as NewsFetcher;
use App\Application\Core\News\UseCases\Queries\FetchById\Query as NewsQuery;
use App\Application\Core\User\UseCases\Queries\FetchAll\Fetcher as UsersFetcher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNewsRequest;
use DateTimeImmutable;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class UpdateController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return View
     */
    public function edit(NewsFetcher $newsFetcher, AuthManager $authManager, CategoriesFetcher $categoriesFetcher, UsersFetcher $usersFetcher, string $newsId): View
    {
        Gate::authorize('news.update', $newsId);

        try {
            $query = new NewsQuery((int)$newsId);
            $news = $newsFetcher->fetch($query);

            $isAdmin = $authManager->user()->hasRole('admin');

            $categories = $categoriesFetcher->fetch()->results;
            $authors = $isAdmin ? $usersFetcher->fetch()->results : [];

            return view('admin.news.edit', compact('news', 'categories', 'authors', 'isAdmin'));

        } catch (NewsNotFoundException) {
            throw new NotFoundHttpException('Новость не найдена');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsRequest $request, UpdateHandler $updateNewsUseCase, AuthManager $authManager, ThumbnailService $thumbnailService, string $newsId): RedirectResponse
    {
        Gate::authorize('news.update', $newsId);

        try {
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $thumbnailService->saveUploadedFile($request->file('thumbnail'));
            }

            $command = new Command(
                id: (int)$newsId,
                title: $request->get('title'),
                content: $request->get('content'),
                excerpt: $request->filled('excerpt') ? $request->get('excerpt') : null,
                authorId: $authManager->user()->getAuthIdentifier(),
                categoryId: $request->get('category_id'),
                publishedAt: $request->filled('published_at') ? new DateTimeImmutable($request->get('published_at')) : new DateTimeImmutable('now'),
                active: $request->boolean('active', true),
                featured: $request->boolean('featured'),
                thumbnail: $thumbnailPath,
                deleteThumbnail: $request->boolean('delete_image'),
            );

            $news = $updateNewsUseCase->handle($command);

        } catch (NewsNotFoundException) {
            throw new NotFoundHttpException('Новость не найдена');
        }

        return redirect()->route('admin.news.show', $news->id);
    }
}
