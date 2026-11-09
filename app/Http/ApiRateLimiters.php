<?php

declare(strict_types=1);

namespace App\Http;

use App\Helpers\ResponseHelper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimiters
{
    public static function register(): void
    {
        $response = static function (Request $request, array $headers) {
            return ResponseHelper::error(
                ['ar' => 'تجاوزت عدد الطلبات المسموح، يرجى المحاولة لاحقًا', 'en' => 'Too many requests. Please try again later'],
                (object) ['code' => 'RATE_LIMIT_EXCEEDED', 'retry_after' => (int) ($headers['Retry-After'] ?? 60)],
                429,
            )->withHeaders($headers);
        };
        RateLimiter::for('login', static function (Request $request) use ($response): array {
            $phone = $request->input('phone_number');
            $phone = is_string($phone) ? trim($phone) : '';
            $ip = (string) $request->ip();

            // Neither passwords nor raw phone numbers appear in cache keys.
            return [
                Limit::perMinute(30)->by('ip:'.hash('sha256', $ip))->response($response),
                Limit::perMinute(5)->by('phone-ip:'.hash('sha256', $phone.'|'.$ip))->response($response),
            ];
        });
        RateLimiter::for('signup', static fn (Request $request) => Limit::perMinutes(10, 5)->by(hash('sha256', (string) $request->ip()))->response($response));
        RateLimiter::for('feedback', static fn (Request $request) => Limit::perMinutes(10, 5)->by(hash('sha256', (string) $request->ip()))->response($response));
        RateLimiter::for('review', static fn (Request $request) => Limit::perMinute(10)->by((string) $request->user()->getAuthIdentifier())->response($response));
    }
}
