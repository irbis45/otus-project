<?php

namespace App\Console\Commands;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\UseCases\Queries\FetchPopular\Fetcher as PopularCategoriesFetcher;
use App\Application\Core\Category\UseCases\Queries\FetchPopular\Query as PopularCategoriesFetcherQuery;
use App\Application\Core\News\UseCases\Queries\FetchFeatured\Fetcher as FeaturedNewsFetcher;
use App\Application\Core\News\UseCases\Queries\FetchFeatured\Query as FeaturedNewsFetcherQuery;
use Illuminate\Console\Command;

class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm
                            {entity? : Entry name (Example: categories, news)}
                            {--f|force : force warm cache}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warms up the cache for the main entities or application pages';


    /**
     * @param CacheInterface           $cache
     * @param FeaturedNewsFetcher        $featuredNewsFetcher
     * @param PopularCategoriesFetcher $popularCategoriesFetcher
     */
    public function __construct(protected CacheInterface $cache, protected FeaturedNewsFetcher $featuredNewsFetcher, protected PopularCategoriesFetcher $popularCategoriesFetcher)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $entity = $this->argument('entity');
        $force = $this->option('force');

        if ($entity) {
            $this->info("Прогрев кэша для сущности: {$entity}");
            $this->warmEntityCache($entity, $force);
        } else {
            $this->info('Прогрев кэша для всех основных сущностей...');
            $this->warmAllCaches($force);
        }

        $this->info('Прогрев кэша завершён.');
    }


    /**
     * @param string $entity
     * @param bool   $force
     *
     * @return void
     */
    protected function warmEntityCache(string $entity, bool $force): void
    {
        switch ($entity) {
            case 'categories':
                $this->warmPopularCategoriesCache($force);
                break;
            case 'news':
                $this->warmFeaturedNewsCache($force);
                break;
            default:
                $this->error("Неизвестная сущность: {$entity}");
                break;
        }
    }

    /**
     * @param bool $force
     *
     * @return void
     */
    protected function warmAllCaches(bool $force): void
    {
        $this->warmPopularCategoriesCache($force);
        $this->warmFeaturedNewsCache($force);
    }

    /**
     * @param bool $force
     *
     * @return void
     */
    protected function warmPopularCategoriesCache(bool $force): void
    {
        $cacheKey = 'popular_categories_list';

        if (!$force && $this->cache->hasWithTags(['categories', 'news_count'], $cacheKey)) {
            $this->info('Кэш популярных категорий уже существует, пропускаем.');
            return;
        }

        $this->info('Начинаем прогрев кэша популярных категорий...');

        $this->popularCategoriesFetcher->fetch(new PopularCategoriesFetcherQuery());

        $this->info('Кэш популярных категорий успешно прогрет.');
    }

    /**
     * @param bool $force
     *
     * @return void
     */
    protected function warmFeaturedNewsCache(bool $force): void
    {
        $cacheKey = 'featured_news_list';

        if (!$force && $this->cache->hasTagged('news', $cacheKey)) {
            $this->info('Кэш важных новостей уже существует, пропускаем.');
            return;
        }

        $this->info('Начинаем прогрев кэша важных новостей...');

        $this->featuredNewsFetcher->fetch(new FeaturedNewsFetcherQuery());

        $this->info('Кэш важных новостей успешно прогрет.');
    }
}
