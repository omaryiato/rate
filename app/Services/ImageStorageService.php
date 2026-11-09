<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;
use Throwable;

class ImageStorageService
{
    /** Use generated filenames; user-supplied names never become filesystem paths. */
    public function store(UploadedFile $file, string $folder): string
    {
        $path = $folder.'/'.Str::uuid().'.webp';
        $encoded = Image::decode($file)->encodeUsingFormat(Format::WEBP, quality: 85, strip: true);
        if (! Storage::disk('documents')->put($path, (string) $encoded)) {
            throw new RuntimeException('Image storage failed.');
        }

        return 'documents/'.$path;
    }

    /** Cleanup is best effort: never hide a database exception or fail a saved update. */
    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'documents/') || str_contains($path, '..') || str_contains($path, '\\')) {
            return;
        }
        try {
            if (! Storage::disk('documents')->delete(substr($path, strlen('documents/')))) {
                throw new RuntimeException('Image cleanup failed.');
            }
        } catch (Throwable) {
            try {
                Log::warning('Image cleanup failed.');
            } catch (Throwable) {
                // Preserve the primary business operation when logging is unavailable.
            }
        }
    }
}
