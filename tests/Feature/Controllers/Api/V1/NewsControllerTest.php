<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\DTO\PaginatedResult;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Exceptions\NewsSaveException;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\News\UseCases\Commands\Create\Handler as CreateNewsHandler;
use App\Application\Core\News\UseCases\Commands\Delete\Command as DeleteNewsCommand;
use App\Application\Core\News\UseCases\Commands\Delete\Handler as DeleteNewsHandler;
use App\Application\Core\News\UseCases\Commands\Update\Command as UpdateNewsCommand;
use App\Application\Core\News\UseCases\Commands\Update\Handler as UpdateNewsHandler;
use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Fetcher as FetchAllFetcher;
use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Query as FetchAllQuery;
use App\Application\Core\News\UseCases\Queries\FetchById\Fetcher as FetchByIdFetcher;
use App\Application\Core\News\UseCases\Queries\FetchById\Query as FetchByIdQuery;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
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

    protected const URL_INDEX = '/api/v1/news';
    protected const URL_SHOW = '/api/v1/news/%d';
    protected const URL_STORE = '/api/v1/news';
    protected const URL_UPDATE = '/api/v1/news/%d';
    protected const URL_DELETE = '/api/v1/news/%d';

    private const GUARD = 'api';

    private NewsDTO $newsDTO;


    public function setUp(): void
    {
        parent::setUp();

        $authorDTO = new AuthorDTO(
            id: $this->faker->numberBetween(1, 100), name: $this->faker->name(), email: $this->faker->safeEmail(),
        );

        $categoryDTO = new CategoryDTO(
            id: $this->faker->numberBetween(1, 100), name: $this->faker->word(), slug: $this->faker->slug()
        );

        $this->newsDTO = new NewsDTO(
            id:          123,
            title:       'Заголовок новости',
            content:     'Текст новости',
            active:     true,
            thumbnail:   null,
            // или URL/путь к картинке
            createdAt:   new \DateTimeImmutable('today'),
            publishedAt: (new \DateTimeImmutable('today'))->modify('+1 day'),
            updatedAt:   new \DateTimeImmutable('today'),
            author:      $authorDTO,
            category:    $categoryDTO,
        );

        $this->cacheMock = Mockery::mock(CacheInterface::class)->shouldIgnoreMissing();
        $this->app->instance(CacheInterface::class, $this->cacheMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_paginated_news(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $limit  = 5;
        $page   = 2;
        $offset = ($page - 1) * $limit;

        $newsDTOs = [
            $this->newsDTO,
        ];

        $paginatedResult = new PaginatedResult(
            items: $newsDTOs, total: 100, limit: $limit, offset: $offset,
        );

        $fetcherMock = Mockery::mock(FetchAllFetcher::class);
        $fetcherMock->shouldReceive('fetch')->once()->with(
                Mockery::on(
                    fn($query
                    ) => $query instanceof FetchAllQuery && $query->limit === $limit && $query->offset === $offset
                )
            )->andReturn($paginatedResult);

        $this->app->instance(FetchAllFetcher::class, $fetcherMock);

        $response = $this->getJson(self::URL_INDEX . "?limit=$limit&page=$page");

        $response->assertStatus(Response::HTTP_OK);

        $responseData = $response->json();

        // Проверим наличие ключей и структур
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
        $firstItem = $responseData['data'][0];
        if (is_object($firstItem)) {
            $firstItem = (array)$firstItem;
        }
        $this->assertArrayHasKey('id', $firstItem);

        $this->assertArrayHasKey('meta', $responseData);

        $this->assertEquals(100, $responseData['meta']['total']);
        $this->assertEquals($limit, $responseData['meta']['limit']);
        $this->assertEquals($offset, $responseData['meta']['offset']);
    }


    public function test_show_returns_news_item(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $newsId = $this->newsDTO->id;

        $fetcherMock = Mockery::mock(FetchByIdFetcher::class);
        $fetcherMock->shouldReceive('fetch')->once()
            //->with(Mockery::type('object'))
            // ->with(Mockery::type(FetchByIdQuery::class))
                    ->with(
                Mockery::on(function ($query) use ($newsId) {
                    return $query instanceof FetchByIdQuery && $query->id === $newsId;
                })
            )->andReturn($this->newsDTO);

        $this->app->instance(FetchByIdFetcher::class, $fetcherMock);

        $response = $this->getJson(sprintf(self::URL_SHOW, $newsId));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['data' => ['id', 'title', 'content']]);
    }

    public function test_show_returns_404_if_news_not_found(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $newsId = 1000;

        $fetcherMock = Mockery::mock(FetchByIdFetcher::class);
        $fetcherMock->shouldReceive('fetch')->once()->andThrow(NewsNotFoundException::class);

        $this->app->instance(FetchByIdFetcher::class, $fetcherMock);

        $response = $this->getJson(sprintf(self::URL_SHOW, $newsId));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJsonStructure(['message']);
    }

    public function test_store_creates_news_successfully(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $handlerMock = Mockery::mock(CreateNewsHandler::class);
        $handlerMock->shouldReceive('handle')->once()->andReturn($this->newsDTO);
        $this->app->instance(CreateNewsHandler::class, $handlerMock);

        $response = $this->postJson(self::URL_STORE, [
            'title'        => $this->newsDTO->title,
            'content'      => $this->newsDTO->content,
            'author_id'    => $this->newsDTO->author?->id,
            'category_id'  => $this->newsDTO->category?->id,
            'published_at' => $this->newsDTO->publishedAt->format('Y-m-d H:i'),
            'active'     => $this->newsDTO->active,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $response->assertJsonStructure(['data' => ['id', 'title', 'content']]);
    }

    public function test_store_returns_internal_error_on_generic_exception()
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $handlerMock = Mockery::mock(CreateNewsHandler::class);
        $handlerMock->shouldReceive('handle')->once()->andThrow(new \Exception('Unexpected error'));

        $this->app->instance(CreateNewsHandler::class, $handlerMock);

        $response = $this->postJson(self::URL_STORE, [
            'title'        => $this->newsDTO->title,
            'content'      => $this->newsDTO->content,
            'author_id'    => $this->newsDTO->author?->id,
            'category_id'  => $this->newsDTO->category?->id,
            'published_at' => $this->newsDTO->publishedAt->format('Y-m-d H:i'),
            'active'     => $this->newsDTO->active,
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson(['message' => 'Ошибка при сохранении новости']);
    }

    public function test_update_updates_news_successfully(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $isAdmin = true;

        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('hasRole')->with('admin')->andReturn($isAdmin);

        $authManagerMock = Mockery::mock(\Illuminate\Auth\AuthManager::class);
        $authManagerMock->shouldReceive('user')->andReturn($userMock);

        $handlerMock = Mockery::mock(UpdateNewsHandler::class);
        $handlerMock->shouldReceive('handle')->once()->with(
                Mockery::on(function (UpdateNewsCommand $command) use ($isAdmin) {
                    // Проверяем, что authorId всегда передан (даже если не админ)
                    return $command instanceof UpdateNewsCommand && $command->authorId !== null;
                }),
                $isAdmin
            )->andReturn($this->newsDTO);


        $this->app->instance(UpdateNewsHandler::class, $handlerMock);
        $this->app->instance(\Illuminate\Auth\AuthManager::class, $authManagerMock);

        $response = $this->putJson(sprintf(self::URL_UPDATE, $this->newsDTO->id), [
            'title'        => $this->newsDTO->title,
            'content'      => $this->newsDTO->content,
            'author_id'    => $this->newsDTO->author?->id,
            'category_id'  => $this->newsDTO->category?->id,
            'published_at' => $this->newsDTO->publishedAt->format('Y-m-d H:i'),
            'active'     => $this->newsDTO->active,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['data' => ['id', 'title', 'content']]);
    }


    public function test_handle_calls_setAuthor_when_user_is_admin(): void
    {
        $newsId   = 123;
        $authorId = 10;
        $categoryId = 5;

        $newsRepository     = Mockery::mock(NewsRepositoryInterface::class);
        $categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $userRepository     = Mockery::mock(UserRepositoryInterface::class);
        $cache              = Mockery::mock(CacheInterface::class);

        $newsMock = Mockery::mock(News::class)->makePartial();
        $newsMock->shouldReceive('getColumnName')->andReturnUsing(function ($property) {
            return $property;
        });

        // Проверяем, что attachAuthor вызовется один раз с объектом, у которого getId() == $authorId
        $newsMock->shouldReceive('attachAuthor')->once()->with(
            Mockery::on(function ($author) use ($authorId) {
                if (is_int($author) || is_string($author)) {
                    return $author == $authorId;
                }

                if (is_object($author) && method_exists($author, 'getId')) {
                    return $author->getId() === $authorId;
                }

                return false;
            })
        );

        $newsMock->shouldReceive('getId')->andReturn($newsId);
        $newsMock->shouldReceive('getTitle')->andReturn('Title');
        $newsMock->shouldReceive('getContent')->andReturn('Content');
        $newsMock->shouldReceive('getColumnName')->andReturn(false);
        $newsMock->shouldReceive('getThumbnail')->andReturn(null);
        $newsMock->shouldReceive('getActive')->andReturn(true);
        $newsMock->shouldReceive('getCreatedAt')->andReturn(new Carbon());
        $newsMock->shouldReceive('getUpdatedAt')->andReturn(new Carbon());
        $newsMock->shouldReceive('getPublishedAt')->andReturn(new Carbon());

        $newsRepository->shouldReceive('find')->with($newsId)->andReturn($newsMock);

        $authorMock = Mockery::mock(User::class);
        $authorMock->shouldReceive('getId')->andReturn($authorId);
        $authorMock->shouldReceive('getName')->andReturn('Test Author');
        $authorMock->shouldReceive('getEmail')->andReturn('author@example.com');

        $userRepository->shouldReceive('find')->with($authorId)->andReturn($authorMock);

        $categoryMock = Mockery::mock(Category::class);
        $categoryMock->shouldReceive('getId')->andReturn($categoryId);
        $categoryMock->shouldReceive('getName')->andReturn('Test Category');
        $categoryMock->shouldReceive('getSlug')->andReturn('test-category');
        $categoryRepository->shouldReceive('find')->with($categoryId)->andReturn($categoryMock);


        $newsRepository->shouldReceive('save')->with($newsMock)->andReturn(true);

        // Кешируем вызовы flushTagged
        $cache->shouldReceive('flushTagged')->once()->andReturnNull();

        // Создаём хендлер с моками репозиториев и кеша
        $handler = new UpdateNewsHandler(
            $newsRepository, $categoryRepository, $userRepository, $cache
        );

        $command = new UpdateNewsCommand(
            id:          $newsId,
            title:       'Updated Title',
            content:     'Updated Content',
            authorId:    $authorId,
            categoryId:  $categoryId,
            publishedAt: null,
            active:     true,
        // thumbnail: null, // Убедитесь, нужен ли здесь параметр, раскомментируйте если требуется
        );

        // Вызываем handle с isAdmin = true, проверяем, что setAuthor будет вызван
        $resultDto = $handler->handle($command, true);

        $this->assertInstanceOf(NewsDTO::class, $resultDto);
    }

    public function test_update_handles_not_found_and_error_exceptions(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('hasRole')->with('admin')->andReturn(false);

        $authManagerMock = Mockery::mock(\Illuminate\Auth\AuthManager::class);
        $authManagerMock->shouldReceive('user')->andReturn($userMock);

        // Exception карта ошибок
        $exceptionsToTest = [
            ['exception' => NewsNotFoundException::class, 'status' => Response::HTTP_NOT_FOUND],
            ['exception' => NewsSaveException::class, 'status' => Response::HTTP_INTERNAL_SERVER_ERROR],
            ['exception' => \Exception::class, 'status' => Response::HTTP_INTERNAL_SERVER_ERROR],
        ];

        foreach ($exceptionsToTest as $case) {
            $handlerMock = Mockery::mock(UpdateNewsHandler::class);
            $handlerMock->shouldReceive('handle')->once()->andThrow(new $case['exception']('Test message'));

            $this->app->instance(UpdateNewsHandler::class, $handlerMock);
            $this->app->instance(\Illuminate\Auth\AuthManager::class, $authManagerMock);

            $response = $this->putJson(sprintf(self::URL_UPDATE, $this->newsDTO->id), [
                'title'        => $this->newsDTO->title,
                'content'      => $this->newsDTO->content,
                'author_id'    => $this->newsDTO->author?->id,
                'category_id'  => $this->newsDTO->category?->id,
                'published_at' => $this->newsDTO->publishedAt->format('Y-m-d H:i'),
                'active'     => $this->newsDTO->active,
            ]);

            $response->assertStatus($case['status']);
            $response->assertJsonStructure(['message']);
        }
    }

    public function test_destroy_deletes_news_successfully(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $newsId = 20;

        $handlerMock = Mockery::mock(DeleteNewsHandler::class);

        $handlerMock->shouldReceive('handle')
                    ->once()
                    ->with(Mockery::on(function ($command) use ($newsId) {
                        return $command instanceof DeleteNewsCommand
                               && $command->id === $newsId;
                    }))->andReturn(true);

        $this->app->instance(DeleteNewsHandler::class, $handlerMock);

        $response = $this->deleteJson(sprintf(self::URL_DELETE, $newsId));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_destroy_handles_not_found_and_errors()
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], self::GUARD);

        $newsId = 20;

        $exceptionsToTest = [
            ['exception' => NewsNotFoundException::class, 'status' => Response::HTTP_NOT_FOUND],
            ['exception' => NewsSaveException::class, 'status' => Response::HTTP_INTERNAL_SERVER_ERROR],
            ['exception' => \Exception::class, 'status' => Response::HTTP_INTERNAL_SERVER_ERROR],
        ];

        foreach ($exceptionsToTest as $case) {
            $handlerMock = Mockery::mock(DeleteNewsHandler::class);
            $handlerMock->shouldReceive('handle')->once()->andThrow(new $case['exception']('Test message'));

            $this->app->instance(DeleteNewsHandler::class, $handlerMock);

            $response = $this->deleteJson(sprintf(self::URL_DELETE, $newsId));

            $response->assertStatus($case['status']);
            $response->assertJsonStructure(['message']);
        }
    }
}
