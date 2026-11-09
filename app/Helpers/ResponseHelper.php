<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

// This Class For Response Result To return Success or error based on status comes from api
class ResponseHelper
{

    // This Function To return Success result
    public static function success($data = null, $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    public static function error($message = 'Error', $errors = null, int $code = 500,): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], $code);
    }

    /**
     * Check if a value is a valid date (any format)
     *
     * @param mixed $value
     * @return bool
     */
    public static function isValidDate($value)
    {
        // strtotime يحول أي string مقبول لتاريخ إلى timestamp
        return strtotime($value) !== false;
    }
}
