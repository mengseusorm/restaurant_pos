<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a text message to a Telegram user
     *
     * @param string $chatId
     * @param string $message
     * @param array $options
     * @return array|null
     */
    public function sendMessage(string $chatId, string $message, array $options = []): ?array
    {
        try {
            if (empty($this->botToken)) {
                Log::warning('Telegram bot token not configured');
                return null;
            }

            $payload = array_merge([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::post("{$this->baseUrl}/sendMessage", $payload);

            if ($response->successful()) {
                Log::info("Telegram message sent successfully to chat ID: {$chatId}");
                return $response->json();
            } else {
                Log::error("Failed to send Telegram message", [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Telegram message sending error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'message' => $message
            ]);
            return null;
        }
    }

    /**
     * Send a message with inline keyboard containing web app button
     *
     * @param string $chatId
     * @param string $message
     * @param string $buttonText
     * @param string $webAppUrl
     * @param array $options
     * @return array|null
     */
    public function sendMessageWithWebAppButton(string $chatId, string $message, string $buttonText, string $webAppUrl, array $options = []): ?array
    {
        try {
            if (empty($this->botToken)) {
                Log::warning('Telegram bot token not configured');
                return null;
            }

            $keyboard = [
                'inline_keyboard' => [[
                    [
                        'text' => $buttonText,
                        'web_app' => ['url' => $webAppUrl]
                    ]
                ]]
            ];

            $payload = array_merge([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($keyboard)
            ], $options);

            $response = Http::post("{$this->baseUrl}/sendMessage", $payload);

            if ($response->successful()) {
                Log::info("Telegram message with web app button sent successfully", [
                    'chat_id' => $chatId,
                    'web_app_url' => $webAppUrl
                ]);
                return $response->json();
            } else {
                Log::error("Failed to send Telegram message with web app button", [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Telegram web app message sending error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'web_app_url' => $webAppUrl
            ]);
            return null;
        }
    }

    /**
     * Send a photo with caption to a Telegram user
     *
     * @param string $chatId
     * @param string $photoUrl
     * @param string $caption
     * @param array $options
     * @return array|null
     */
    public function sendPhoto(string $chatId, string $photoUrl, string $caption = '', array $options = []): ?array
    {
        try {
            if (empty($this->botToken)) {
                Log::warning('Telegram bot token not configured');
                return null;
            }

            $payload = array_merge([
                'chat_id' => $chatId,
                'photo' => $photoUrl,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::post("{$this->baseUrl}/sendPhoto", $payload);

            if ($response->successful()) {
                Log::info("Telegram photo sent successfully to chat ID: {$chatId}");
                return $response->json();
            } else {
                Log::error("Failed to send Telegram photo", [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Telegram photo sending error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'photo_url' => $photoUrl
            ]);
            return null;
        }
    }

    /**
     * Send a location to a Telegram user
     *
     * @param string $chatId
     * @param float $latitude
     * @param float $longitude
     * @param array $options
     * @return array|null
     */
    public function sendLocation(string $chatId, float $latitude, float $longitude, array $options = []): ?array
    {
        try {
            if (empty($this->botToken)) {
                Log::warning('Telegram bot token not configured');
                return null;
            }

            $payload = array_merge([
                'chat_id' => $chatId,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ], $options);

            $response = Http::post("{$this->baseUrl}/sendLocation", $payload);

            if ($response->successful()) {
                Log::info("Telegram location sent successfully to chat ID: {$chatId}");
                return $response->json();
            } else {
                Log::error("Failed to send Telegram location", [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Telegram location sending error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
            return null;
        }
    }

    /**
     * Check if the bot token is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    /**
     * Get bot information
     *
     * @return array|null
     */
    public function getMe(): ?array
    {
        try {
            if (empty($this->botToken)) {
                return null;
            }

            $response = Http::get("{$this->baseUrl}/getMe");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error("Failed to get Telegram bot info: " . $e->getMessage());
            return null;
        }
    }
}