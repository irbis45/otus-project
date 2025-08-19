<?php

namespace Tests\Unit\Console\Commands;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\UseCases\Queries\FetchPopular\Fetcher as PopularCategoriesFetcher;
use App\Application\Core\Category\UseCases\Queries\FetchPopular\Query as PopularCategoriesFetcherQuery;
use App\Application\Core\News\UseCases\Queries\FetchFeatured\Fetcher as FeaturedNewsFetcher;
use App\Application\Core\News\UseCases\Queries\FetchFeatured\Query as FeaturedNewsFetcherQuery;
use App\Application\Core\Category\DTO\ResultDTO as CategoryResultDTO;
use App\Application\Core\News\DTO\ResultDTO as NewsResultDTO;
use App\Console\Commands\WarmCacheCommand;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class WarmCacheCommandTest extends TestCase
{
    use RefreshDatabase;

    private CacheInterface $cache;
    private FeaturedNewsFetcher $featuredNewsFetcher;
    private PopularCategoriesFetcher $popularCategoriesFetcher;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->featuredNewsFetcher = Mockery::mock(FeaturedNewsFetcher::class);
        $this->popularCategoriesFetcher = Mockery::mock(PopularCategoriesFetcher::class);
        
        $this->app->bind(CacheInterface::class, fn() => $this->cache);
        $this->app->bind(FeaturedNewsFetcher::class, fn() => $this->featuredNewsFetcher);
        $this->app->bind(PopularCategoriesFetcher::class, fn() => $this->popularCategoriesFetcher);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_command_exists()
    {
        $commands = \Artisan::all();
        $this->assertArrayHasKey('cache:warm', $commands);
    }

    public function test_command_without_arguments_executes_successfully()
    {
        $this->cache->shouldReceive('hasWithTags')->andReturn(true);
        $this->cache->shouldReceive('hasTagged')->andReturn(true);

        $this->artisan('cache:warm')
            ->expectsOutput('Прогрев кэша для всех основных сущностей...')
            ->expectsOutput('Кэш популярных категорий уже существует, пропускаем.')
            ->expectsOutput('Кэш важных новостей уже существует, пропускаем.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_categories_entity()
    {
        $this->cache->shouldReceive('hasWithTags')
            ->with(['categories', 'news_count'], 'popular_categories_list')
            ->andReturn(true);

        $this->artisan('cache:warm', ['entity' => 'categories'])
            ->expectsOutput('Прогрев кэша для сущности: categories')
            ->expectsOutput('Кэш популярных категорий уже существует, пропускаем.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_news_entity()
    {
        $this->cache->shouldReceive('hasTagged')
            ->with('news', 'featured_news_list')
            ->andReturn(true);

        $this->artisan('cache:warm', ['entity' => 'news'])
            ->expectsOutput('Прогрев кэша для сущности: news')
            ->expectsOutput('Кэш важных новостей уже существует, пропускаем.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_force_option_categories()
    {
        $this->popularCategoriesFetcher->shouldReceive('fetch')
            ->with(Mockery::type(PopularCategoriesFetcherQuery::class))
            ->once()
            ->andReturn(new CategoryResultDTO([]));

        $this->artisan('cache:warm', ['entity' => 'categories', '--force' => true])
            ->expectsOutput('Прогрев кэша для сущности: categories')
            ->expectsOutput('Начинаем прогрев кэша популярных категорий...')
            ->expectsOutput('Кэш популярных категорий успешно прогрет.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_force_option_news()
    {
        $this->featuredNewsFetcher->shouldReceive('fetch')
            ->with(Mockery::type(FeaturedNewsFetcherQuery::class))
            ->once()
            ->andReturn(new NewsResultDTO([]));

        $this->artisan('cache:warm', ['entity' => 'news', '--force' => true])
            ->expectsOutput('Прогрев кэша для сущности: news')
            ->expectsOutput('Начинаем прогрев кэша важных новостей...')
            ->expectsOutput('Кэш важных новостей успешно прогрет.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_unknown_entity()
    {
        $this->artisan('cache:warm', ['entity' => 'unknown'])
            ->expectsOutput('Прогрев кэша для сущности: unknown')
            ->expectsOutput('Неизвестная сущность: unknown')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_without_force_does_not_warm_existing_cache()
    {
        $this->cache->shouldReceive('hasWithTags')
            ->with(['categories', 'news_count'], 'popular_categories_list')
            ->andReturn(false);
        $this->cache->shouldReceive('hasTagged')
            ->with('news', 'featured_news_list')
            ->andReturn(false);

        $this->popularCategoriesFetcher->shouldReceive('fetch')
            ->with(Mockery::type(PopularCategoriesFetcherQuery::class))
            ->once()
            ->andReturn(new CategoryResultDTO([]));
        
        $this->featuredNewsFetcher->shouldReceive('fetch')
            ->with(Mockery::type(FeaturedNewsFetcherQuery::class))
            ->once()
            ->andReturn(new NewsResultDTO([]));

        $this->artisan('cache:warm')
            ->expectsOutput('Прогрев кэша для всех основных сущностей...')
            ->expectsOutput('Начинаем прогрев кэша популярных категорий...')
            ->expectsOutput('Кэш популярных категорий успешно прогрет.')
            ->expectsOutput('Начинаем прогрев кэша важных новостей...')
            ->expectsOutput('Кэш важных новостей успешно прогрет.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_force_bypasses_cache_check()
    {
        $this->popularCategoriesFetcher->shouldReceive('fetch')
            ->with(Mockery::type(PopularCategoriesFetcherQuery::class))
            ->once()
            ->andReturn(new CategoryResultDTO([]));
        
        $this->featuredNewsFetcher->shouldReceive('fetch')
            ->with(Mockery::type(FeaturedNewsFetcherQuery::class))
            ->once()
            ->andReturn(new NewsResultDTO([]));

        $this->artisan('cache:warm', ['--force' => true])
            ->expectsOutput('Прогрев кэша для всех основных сущностей...')
            ->expectsOutput('Начинаем прогрев кэша популярных категорий...')
            ->expectsOutput('Кэш популярных категорий успешно прогрет.')
            ->expectsOutput('Начинаем прогрев кэша важных новостей...')
            ->expectsOutput('Кэш важных новостей успешно прогрет.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_cache_miss_for_categories_only()
    {
        $this->cache->shouldReceive('hasWithTags')
            ->with(['categories', 'news_count'], 'popular_categories_list')
            ->andReturn(false);

        $this->popularCategoriesFetcher->shouldReceive('fetch')
            ->with(Mockery::type(PopularCategoriesFetcherQuery::class))
            ->once()
            ->andReturn(new CategoryResultDTO([]));

        $this->artisan('cache:warm', ['entity' => 'categories'])
            ->expectsOutput('Прогрев кэша для сущности: categories')
            ->expectsOutput('Начинаем прогрев кэша популярных категорий...')
            ->expectsOutput('Кэш популярных категорий успешно прогрет.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_cache_miss_for_news_only()
    {
        $this->cache->shouldReceive('hasTagged')
            ->with('news', 'featured_news_list')
            ->andReturn(false);

        $this->featuredNewsFetcher->shouldReceive('fetch')
            ->with(Mockery::type(FeaturedNewsFetcherQuery::class))
            ->once()
            ->andReturn(new NewsResultDTO([]));

        $this->artisan('cache:warm', ['entity' => 'news'])
            ->expectsOutput('Прогрев кэша для сущности: news')
            ->expectsOutput('Начинаем прогрев кэша важных новостей...')
            ->expectsOutput('Кэш важных новостей успешно прогрет.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_signature_contains_correct_options()
    {
        $command = new WarmCacheCommand($this->cache, $this->featuredNewsFetcher, $this->popularCategoriesFetcher);
        
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);
        $signature = $signatureProperty->getValue($command);
        
        $this->assertStringContainsString('entity?', $signature);
        $this->assertStringContainsString('--f|force', $signature);
    }

    public function test_command_description_is_correct()
    {
        $command = new WarmCacheCommand($this->cache, $this->featuredNewsFetcher, $this->popularCategoriesFetcher);
        
        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        $description = $descriptionProperty->getValue($command);
        
        $this->assertEquals('Warms up the cache for the main entities or application pages', $description);
    }

    public function test_command_with_case_sensitive_entity_names()
    {
        $this->artisan('cache:warm', ['entity' => 'CATEGORIES'])
            ->expectsOutput('Прогрев кэша для сущности: CATEGORIES')
            ->expectsOutput('Неизвестная сущность: CATEGORIES')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);

        $this->artisan('cache:warm', ['entity' => 'News'])
            ->expectsOutput('Прогрев кэша для сущности: News')
            ->expectsOutput('Неизвестная сущность: News')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_empty_entity_string()
    {
        $this->cache->shouldReceive('hasWithTags')->andReturn(true);
        $this->cache->shouldReceive('hasTagged')->andReturn(true);

        $this->artisan('cache:warm', ['entity' => ''])
            ->expectsOutput('Прогрев кэша для всех основных сущностей...')
            ->expectsOutput('Кэш популярных категорий уже существует, пропускаем.')
            ->expectsOutput('Кэш важных новостей уже существует, пропускаем.')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_numeric_entity()
    {
        $this->artisan('cache:warm', ['entity' => '123'])
            ->expectsOutput('Прогрев кэша для сущности: 123')
            ->expectsOutput('Неизвестная сущность: 123')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_with_special_characters_in_entity()
    {
        $this->artisan('cache:warm', ['entity' => 'cat@egories'])
            ->expectsOutput('Прогрев кэша для сущности: cat@egories')
            ->expectsOutput('Неизвестная сущность: cat@egories')
            ->expectsOutput('Прогрев кэша завершён.')
            ->assertExitCode(0);
    }

    public function test_command_signature_and_description_are_accessible()
    {
        $command = new WarmCacheCommand($this->cache, $this->featuredNewsFetcher, $this->popularCategoriesFetcher);
        
        $this->assertInstanceOf(Command::class, $command);
        
        // Проверяем, что команда правильно зарегистрирована
        $commands = \Artisan::all();
        $this->assertArrayHasKey('cache:warm', $commands);
        $this->assertInstanceOf(WarmCacheCommand::class, $commands['cache:warm']);
    }
}
