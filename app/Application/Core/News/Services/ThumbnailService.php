<?php

declare(strict_types=1);

namespace App\Application\Core\News\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ThumbnailService
{
    private const DISK = 'public';
    private const DIR = 'news';

    public function downloadAndStore(string $url): string
    {
        $response = Http::timeout(10)->get($url);

        if (!$response->ok()) {
            throw new \RuntimeException('Сервер вернул ответ с ошибкой ' . $response->status());
        }

        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $ext = $ext ?: 'jpg';
        $filename = self::DIR . '/' . uniqid() . '.' . $ext;

        if (!Storage::disk(self::DISK)->put($filename, $response->body())) {
            throw new \RuntimeException('Не удалось сохранить файл на сервере');
        }

        return $filename;
    }

    public function saveUploadedFile(UploadedFile $file): string
    {
        return $file->store(self::DIR, self::DISK);
    }

    public function deleteFile(string $path): bool
    {
        /*if (preg_match('#^https?://#i', $path)) {
            return false;
        }*/

        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->delete($path);
        }

        return false;
    }

    public function getPublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

       /* if (preg_match('#^https?://#i', $path)) {
            return $path;
        }*/

        return Storage::disk(self::DISK)->url($path);
    }
}
