<?php

namespace Tests\Feature\Controllers\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем необходимые директории для тестов
        $this->createTestDirectories();
    }

    protected function tearDown(): void
    {
        // Очищаем созданные директории
        $this->cleanupTestDirectories();
        
        parent::tearDown();
    }

    protected function createTestDirectories(): void
    {
        // Создаем необходимые директории для тестов
        $directories = [
            storage_path('framework/testing/disks/public/news'),
            storage_path('framework/testing/disks/public/categories'),
            storage_path('framework/testing/disks/public/users'),
            storage_path('framework/testing/disks/public/comments'),
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    protected function cleanupTestDirectories(): void
    {
        $directories = [
            storage_path('framework/testing/disks/public/news'),
            storage_path('framework/testing/disks/public/categories'),
            storage_path('framework/testing/disks/public/users'),
            storage_path('framework/testing/disks/public/comments'),
        ];

        foreach ($directories as $directory) {
            if (file_exists($directory)) {
                $this->removeDirectoryRecursively($directory);
            }
        }
    }

    protected function removeDirectoryRecursively(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectoryRecursively($path);
            } else {
                unlink($path);
            }
        }

        if (is_dir($directory)) {
            rmdir($directory);
        }
    }
}
