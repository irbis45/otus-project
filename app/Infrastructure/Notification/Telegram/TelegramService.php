<?php
declare(strict_types=1);

namespace App\Infrastructure\Notification\Telegram;

use App\Application\Contracts\TelegramServiceInterface;
use Illuminate\Support\Facades\Http;

class TelegramService implements TelegramServiceInterface
{
    protected string $botToken;
    protected string $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token') ?? '';
        $this->chatId = config('services.telegram.chat_id') ?? '';
    }

    /**
     * Отправить сообщение в Telegram группу
     *
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $message): bool
    {
        // Проверяем, что токен и chat_id установлены
        if (empty($this->botToken) || empty($this->chatId)) {
            \Log::warning('Telegram bot token or chat_id not configured');
            return false;
        }

        $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        return $response->successful();
    }
}
