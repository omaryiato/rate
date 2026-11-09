<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class LoggingService
{
    /** Logging is best effort and must never interrupt a completed business operation. */
    public function success(string $operation, ?int $userId = null): void
    {
        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/'.now()->format('Y-m-d').'/'.$operation.'.log'),
            ])->info('Operation completed successfully.', [
                'operation' => $operation,
                'user_id' => $userId,
                'status' => 'SUCCESS',
            ]);
        } catch (Throwable) {
            try {
                Log::warning('Operation logging failed.', ['operation' => $operation]);
            } catch (Throwable) {
                // Preserve the primary operation even when the fallback logger is unavailable.
            }
        }
    }
}
