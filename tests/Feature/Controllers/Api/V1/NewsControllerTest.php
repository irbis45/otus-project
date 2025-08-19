<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\DTO\PaginatedResult;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Exceptions\NewsSaveException;
use App\Application\Core\News\Services\ThumbnailService;
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
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('api')]
#[Group('api-news')]
class NewsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_INDEX  = '/api/v1/news';
    protected const URL_SHOW   = '/api/v1/news/%d';
    protected const URL_STORE  = '/api/v1/news';
    protected const URL_UPDATE = '/api/v1/news/%d';
    protected const URL_DELETE = '/api/v1/news/%d';

    private const GUARD = 'api';

    private NewsDTO $newsDTO;
    private User $user;
    private Category $category;
    private PaginatedResult $paginatedResult;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        // Создаем категорию напрямую в базе данных
        $this->category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true
        ]);

        $authorDTO = new AuthorDTO(
            id: $this->user->id,
            name: $this->user->name,
            email: $this->user->email,
        );

        $categoryDTO = new CategoryDTO(
            id: $this->category->id,
            name: $this->category->name,
            slug: $this->category->slug
        );

        $this->newsDTO = new NewsDTO(
            id:          123,
            title:       'Заголовок новости',
            slug:        'zagolovok-novosti',
            content:     'Текст новости',
            thumbnail:   null,
            publishedAt: (new \DateTimeImmutable('today'))->modify('+1 day'),
            createdAt:   new \DateTimeImmutable('today'),
            excerpt:     'Краткое описание новости',
            active:      true,
            featured:    false,
            views:       0,
            updatedAt:   new \DateTimeImmutable('today'),
            author:      $authorDTO,
            category:    $categoryDTO,
        );

        $this->paginatedResult = new PaginatedResult(
            items: [$this->newsDTO],
            total: 1,
            limit: 10,
            offset: 0
        );

        $this->cacheMock = Mockery::mock(CacheInterface::class)->shouldIgnoreMissing();
        $this->app->instance(CacheInterface::class, $this->cacheMock);

        $this->thumbnailServiceMock = Mockery::mock(ThumbnailService::class)->shouldIgnoreMissing();
        $this->app->instance(ThumbnailService::class, $this->thumbnailServiceMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_paginated_news_list(): void
    {
        // Arrange
        $fetchAllFetcher = Mockery::mock(FetchAllFetcher::class);
        $fetchAllFetcher->shouldReceive('fetch')
            ->once()
            ->with(Mockery::type(FetchAllQuery::class))
            ->andReturn($this->paginatedResult);

        $this->app->instance(FetchAllFetcher::class, $fetchAllFetcher);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->getJson(self::URL_INDEX . '?limit=10&page=1');

        // Assert
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'slug', 'content', 'excerpt',
                        'thumbnail', 'published_at', 'active', 'featured',
                        'views', 'created_at', 'updated_at', 'author', 'category'
                    ]
                ],
                'meta' => ['total', 'limit', 'offset']
            ])
            ->assertJson([
                'meta' => [
                    'total' => 1,
                    'limit' => 10,
                    'offset' => 0
                ]
            ]);
    }

    public function test_index_with_custom_pagination_parameters(): void
    {
        // Arrange
        $customPaginatedResult = new PaginatedResult(
            items: [$this->newsDTO],
            total: 1,
            limit: 5,
            offset: 10
        );

        $fetchAllFetcher = Mockery::mock(FetchAllFetcher::class);
        $fetchAllFetcher->shouldReceive('fetch')
            ->once()
            ->with(Mockery::on(function (FetchAllQuery $query) {
                return $query->limit === 5 && $query->offset === 10;
            }))
            ->andReturn($customPaginatedResult);

        $this->app->instance(FetchAllFetcher::class, $fetchAllFetcher);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->getJson(self::URL_INDEX . '?limit=5&page=3');

        // Assert
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'meta' => [
                    'total' => 1,
                    'limit' => 5,
                    'offset' => 10
                ]
            ]);
    }

    public function test_show_returns_news_by_id(): void
    {
        // Arrange
        $fetchByIdFetcher = Mockery::mock(FetchByIdFetcher::class);
        $fetchByIdFetcher->shouldReceive('fetch')
            ->once()
            ->with(Mockery::type(FetchByIdQuery::class))
            ->andReturn($this->newsDTO);

        $this->app->instance(FetchByIdFetcher::class, $fetchByIdFetcher);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->getJson(sprintf(self::URL_SHOW, 123));

        // Assert
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'slug', 'content', 'excerpt',
                    'thumbnail', 'published_at', 'active', 'featured',
                    'views', 'created_at', 'updated_at', 'author', 'category'
                ]
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Заголовок новости',
                    'content' => 'Текст новости'
                ]
            ]);
    }

    public function test_show_returns_404_when_news_not_found(): void
    {
        // Arrange
        $fetchByIdFetcher = Mockery::mock(FetchByIdFetcher::class);
        $fetchByIdFetcher->shouldReceive('fetch')
            ->once()
            ->with(Mockery::type(FetchByIdQuery::class))
            ->andThrow(new NewsNotFoundException('Новость не найдена'));

        $this->app->instance(FetchByIdFetcher::class, $fetchByIdFetcher);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->getJson(sprintf(self::URL_SHOW, 999));

        // Assert
        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(['message' => 'Новость не найдена']);
    }

    public function test_store_creates_new_news_successfully(): void
    {
        // Arrange
        $createNewsHandler = Mockery::mock(CreateNewsHandler::class);
        $createNewsHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(CreateNewsCommand::class))
            ->andReturn($this->newsDTO);

        $this->app->instance(CreateNewsHandler::class, $createNewsHandler);

        $newsData = [
            'title' => 'Новая новость',
            'content' => 'Содержание новой новости',
            'excerpt' => 'Краткое описание',
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => false,
            'published_at' => '2024-01-01 12:00:00'
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->postJson(self::URL_STORE, $newsData);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'slug', 'content', 'excerpt',
                    'thumbnail', 'active', 'featured',
                    'views', 'author', 'category'
                ]
            ]);
    }

    public function test_store_with_thumbnail_url_processes_thumbnail_successfully(): void
    {
        // Arrange
        $createNewsHandler = Mockery::mock(CreateNewsHandler::class);
        $createNewsHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (CreateNewsCommand $command) {
                return $command->thumbnail === 'news/thumbnail.jpg';
            }))
            ->andReturn($this->newsDTO);

        $this->app->instance(CreateNewsHandler::class, $createNewsHandler);

        $this->thumbnailServiceMock->shouldReceive('downloadAndStore')
            ->once()
            ->with('https://example.com/image.jpg')
            ->andReturn('news/thumbnail.jpg');

        $newsData = [
            'title' => 'Новая новость',
            'content' => 'Содержание новой новости',
            'category_id' => $this->category->id,
            'thumbnail_url' => 'https://example.com/image.jpg'
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->postJson(self::URL_STORE, $newsData);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_store_returns_error_when_thumbnail_processing_fails(): void
    {
        // Arrange
        $this->thumbnailServiceMock->shouldReceive('downloadAndStore')
            ->once()
            ->with('https://example.com/image.jpg')
            ->andThrow(new \RuntimeException('Ошибка загрузки изображения'));

        $newsData = [
            'title' => 'Новая новость',
            'content' => 'Содержание новой новости',
            'category_id' => $this->category->id,
            'thumbnail_url' => 'https://example.com/image.jpg'
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->postJson(self::URL_STORE, $newsData);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(['message' => 'Ошибка загрузки миниатюры: Ошибка загрузки изображения']);
    }

    public function test_store_returns_error_when_handler_fails(): void
    {
        // Arrange
        $createNewsHandler = Mockery::mock(CreateNewsHandler::class);
        $createNewsHandler->shouldReceive('handle')
            ->once()
            ->andThrow(new \Exception('Ошибка базы данных'));

        $this->app->instance(CreateNewsHandler::class, $createNewsHandler);

        $newsData = [
            'title' => 'Новая новость',
            'content' => 'Содержание новой новости',
            'category_id' => $this->category->id
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->postJson(self::URL_STORE, $newsData);

        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson(['message' => 'Ошибка при сохранении новости']);
    }

    public function test_update_updates_news_successfully(): void
    {
        // Arrange
        $updateNewsHandler = Mockery::mock(UpdateNewsHandler::class);
        $updateNewsHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(UpdateNewsCommand::class))
            ->andReturn($this->newsDTO);

        $this->app->instance(UpdateNewsHandler::class, $updateNewsHandler);

        $updateData = [
            'title' => 'Обновленный заголовок',
            'content' => 'Обновленное содержание',
            'active' => false
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->putJson(sprintf(self::URL_UPDATE, 123), $updateData);

        // Assert
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'slug', 'content', 'excerpt',
                    'thumbnail', 'published_at', 'active', 'featured',
                    'views', 'created_at', 'updated_at', 'author', 'category'
                ]
            ]);
    }

    public function test_update_with_thumbnail_url_processes_thumbnail_successfully(): void
    {
        // Arrange
        $updateNewsHandler = Mockery::mock(UpdateNewsHandler::class);
        $updateNewsHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (UpdateNewsCommand $command) {
                return $command->thumbnail === 'news/updated-thumbnail.jpg';
            }))
            ->andReturn($this->newsDTO);

        $this->app->instance(UpdateNewsHandler::class, $updateNewsHandler);

        $this->thumbnailServiceMock->shouldReceive('downloadAndStore')
            ->once()
            ->with('https://example.com/new-image.jpg')
            ->andReturn('news/updated-thumbnail.jpg');

        $updateData = [
            'title' => 'Обновленный заголовок',
            'thumbnail_url' => 'https://example.com/new-image.jpg'
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->putJson(sprintf(self::URL_UPDATE, 123), $updateData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_update_returns_404_when_news_not_found(): void
    {
        // Arrange
        $updateNewsHandler = Mockery::mock(UpdateNewsHandler::class);
        $updateNewsHandler->shouldReceive('handle')
            ->once()
            ->andThrow(new NewsNotFoundException('Новость не найдена'));

        $this->app->instance(UpdateNewsHandler::class, $updateNewsHandler);

        $updateData = ['title' => 'Обновленный заголовок'];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->putJson(sprintf(self::URL_UPDATE, 999), $updateData);

        // Assert
        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(['message' => 'Новость не найдена']);
    }

    public function test_update_returns_error_when_thumbnail_processing_fails(): void
    {
        // Arrange
        $this->thumbnailServiceMock->shouldReceive('downloadAndStore')
            ->once()
            ->with('https://example.com/image.jpg')
            ->andThrow(new \RuntimeException('Ошибка загрузки изображения'));

        $updateData = [
            'title' => 'Обновленный заголовок',
            'thumbnail_url' => 'https://example.com/image.jpg'
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->putJson(sprintf(self::URL_UPDATE, 123), $updateData);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(['message' => 'Ошибка загрузки миниатюры: Ошибка загрузки изображения']);
    }

    public function test_update_returns_error_when_handler_fails(): void
    {
        // Arrange
        $updateNewsHandler = Mockery::mock(UpdateNewsHandler::class);
        $updateNewsHandler->shouldReceive('handle')
            ->once()
            ->andThrow(new \Exception('Ошибка базы данных'));

        $this->app->instance(UpdateNewsHandler::class, $updateNewsHandler);

        $updateData = ['title' => 'Обновленный заголовок'];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->putJson(sprintf(self::URL_UPDATE, 123), $updateData);

        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson(['message' => 'Ошибка при обновлении новостиОшибка базы данных']);
    }

    public function test_destroy_deletes_news_successfully(): void
    {
        // Arrange
        $deleteNewsHandler = Mockery::mock(DeleteNewsHandler::class);
        $deleteNewsHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(DeleteNewsCommand::class));

        $this->app->instance(DeleteNewsHandler::class, $deleteNewsHandler);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->deleteJson(sprintf(self::URL_DELETE, 123));

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_destroy_returns_404_when_news_not_found(): void
    {
        // Arrange
        $deleteNewsHandler = Mockery::mock(DeleteNewsHandler::class);
        $deleteNewsHandler->shouldReceive('handle')
            ->once()
            ->andThrow(new NewsNotFoundException('Новость не найдена'));

        $this->app->instance(DeleteNewsHandler::class, $deleteNewsHandler);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->deleteJson(sprintf(self::URL_DELETE, 999));

        // Assert
        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(['message' => 'Новость не найдена']);
    }

    public function test_destroy_returns_error_when_handler_fails(): void
    {
        // Arrange
        $deleteNewsHandler = Mockery::mock(DeleteNewsHandler::class);
        $deleteNewsHandler->shouldReceive('handle')
            ->once()
            ->andThrow(new \Exception('Ошибка базы данных'));

        $this->app->instance(DeleteNewsHandler::class, $deleteNewsHandler);

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->deleteJson(sprintf(self::URL_DELETE, 123));

        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson(['message' => 'Ошибка при удалении новости']);
    }

    public function test_unauthorized_access_returns_401(): void
    {
        // Act & Assert
        $this->getJson(self::URL_INDEX)->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->getJson(sprintf(self::URL_SHOW, 123))->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->postJson(self::URL_STORE, [])->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->putJson(sprintf(self::URL_UPDATE, 123), [])->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->deleteJson(sprintf(self::URL_DELETE, 123))->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_store_validation_fails_with_invalid_data(): void
    {
        // Arrange
        $invalidData = [
            'title' => '', // Пустой заголовок
            'content' => 'a', // Слишком короткий контент
            'category_id' => 999999 // Несуществующая категория
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->postJson(self::URL_STORE, $invalidData);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title', 'content', 'category_id']);
    }

    public function test_update_validation_fails_with_invalid_data(): void
    {
        // Arrange
        $invalidData = [
            'title' => 'a', // Слишком короткий заголовок
            'thumbnail_url' => 'invalid-url' // Невалидный URL
        ];

        // Act
        $response = $this->actingAs($this->user, self::GUARD)
            ->putJson(sprintf(self::URL_UPDATE, 123), $invalidData);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title', 'thumbnail_url']);
    }
}
