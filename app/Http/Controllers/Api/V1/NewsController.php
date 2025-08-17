<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Exceptions\NewsSaveException;
use App\Application\Core\News\UseCases\Commands\Create\Command as CreateNewsCommand;
use App\Application\Core\News\UseCases\Commands\Create\Handler as CreateNewsHandler;
use App\Application\Core\News\UseCases\Commands\Delete\Command as DeleteNewsCommand;
use App\Application\Core\News\UseCases\Commands\Delete\Handler as DeleteNewsHandler;
use App\Application\Core\News\UseCases\Commands\Update\Command as UpdateNewsCommand;
use App\Application\Core\News\UseCases\Commands\Update\Handler as UpdateNewsHandler;
use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Fetcher as FetchAllFetcher;
use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Query as FetchAllQuery;
use App\Application\Core\News\UseCases\Queries\FetchById\Fetcher as FetchByIdFetcher;
use App\Application\Core\News\UseCases\Queries\FetchById\Query as FetchByIdQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateNewsRequest;
use App\Http\Requests\Api\UpdateNewsRequest;
use App\Http\Resources\Mappers\NewsApiModelMapper;
use App\Http\Resources\NewsResource;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Application\Core\News\Services\ThumbnailService;

class NewsController extends Controller
{
    public function __construct(
        private ThumbnailService $thumbnailService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(FetchAllFetcher $fetchAllFetcher, Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $limit;

        $query = new FetchAllQuery(limit: $limit, offset: $offset);
        $paginatedResult = $fetchAllFetcher->fetch($query);

        $apiModels = array_map(
            fn($newsDTO) => NewsApiModelMapper::map($newsDTO),
            $paginatedResult->items
        );

        return response()->json([
                                    'data' => NewsResource::collection($apiModels),
                                    'meta' => [
                                        'total' => $paginatedResult->total,
                                        'limit' => $paginatedResult->limit,
                                        'offset' => $paginatedResult->offset,
                                    ],
                                ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateNewsHandler $createNewsHandler, AuthManager $authManager, CreateNewsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $thumbnailPath = null;
        if (!empty($validated['thumbnail_url'])) {
            try {
                $thumbnailPath = $this->processThumbnail($validated['thumbnail_url']);
            } catch (\RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $command = new CreateNewsCommand(
            title:       $validated['title'],
            content:     $validated['content'],
            excerpt:     $validated['excerpt'] ?? null,
            authorId:    $authManager->user()->getAuthIdentifier(),
            categoryId:  $validated['category_id'],
            publishedAt: isset($validated['published_at']) && $validated['published_at'] ? new \DateTimeImmutable($validated['published_at']) : new \DateTimeImmutable('now'),
            active:      $validated['active'] ?? false,
            featured:    $validated['featured'] ?? false,
            thumbnail: $thumbnailPath
        );

        try {
            $newsDTO = $createNewsHandler->handle($command);
            $apiModel = NewsApiModelMapper::map($newsDTO);

            return response()->json(['data' => $apiModel], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при сохранении новости'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FetchByIdFetcher $fetchByIdFetcher, int $id): JsonResponse
    {
        try {
            $query = new FetchByIdQuery($id);
            $newsDTO = $fetchByIdFetcher->fetch($query);

            $apiModel = NewsApiModelMapper::map($newsDTO);

            return response()->json(['data' => new NewsResource($apiModel)]);

        } catch (NewsNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsHandler $updateNewsHandler, UpdateNewsRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $thumbnailPath = null;
        if (!empty($validated['thumbnail_url'])) {
            try {
                $thumbnailPath = $this->processThumbnail($validated['thumbnail_url']);
            } catch (\RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $command = new UpdateNewsCommand(
            id:          $id,
            title: $validated['title'] ?? null,
            content: $validated['content'] ?? null,
            excerpt: $validated['excerpt'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            publishedAt: isset($validated['published_at']) ? new \DateTimeImmutable($validated['published_at']) : null,
            active: $validated['active'] ?? null,
            featured: $validated['featured'] ?? null,
            thumbnail: $thumbnailPath,
        );

        try {
            $newsDTO = $updateNewsHandler->handle($command);
            $apiModel = NewsApiModelMapper::map($newsDTO);

            return response()->json(['data' => new NewsResource($apiModel)]);

        } catch (NewsNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);

        } catch (NewsSaveException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при обновлении новости'. $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteNewsHandler $deleteNewsHandler, int $id)
    {
        try {
            $deleteNewsHandler->handle(new DeleteNewsCommand($id));

            return response()->noContent();

        } catch (NewsNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);

        } catch (NewsSaveException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception) {
            return response()->json(['message' => 'Ошибка при удалении новости'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function processThumbnail(?string $thumbnailUrl): ?string
    {
        if (!$thumbnailUrl) {
            return null;
        }
        try {
            return $this->thumbnailService->downloadAndStore($thumbnailUrl);
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка загрузки миниатюры: ' . $e->getMessage());
        }
    }
}
