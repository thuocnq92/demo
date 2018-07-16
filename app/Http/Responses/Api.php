<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * API base class
 *
 */
class Api
{

    const OK = 1;
    const ER = 0;

    /**
     * Return success API with data
     *
     * @param array  $data
     * @param int    $httpCode
     * @return JsonResponse
     */
    public static function success($data = [], $messageCode, $httpCode = 200)
    {
        return response()->json([
                    'success' => self::OK,
                    'data'    => (object) $data,
                    'message' => self::getMessage($messageCode)
                        ], $httpCode);
    }

    /**
     * Return success API without data
     *
     * @param int $httpCode
     * @return JsonResponse
     */
    public static function successOnly($httpCode = 200)
    {
        return response()->json([
                    'success' => self::OK,
                        ], $httpCode);
    }

    /**
     * Return error API
     *
     * @param string $errorCode
     * @param int    $httpCode
     * @return JsonResponse
     */
    public static function error($errorCode, $httpCode = 200)
    {
        $error = [];
        $error['message'] = self::getMessage($errorCode);
        return response()->json([
                    'success' => self::ER,
                    'error'   => (object) $error,
                        ], $httpCode);
    }

    /**
     * Get message by message code
     *
     * @param string $messageCode Message code
     * @return string message
     *
     */
    public static function getMessage($messageCode)
    {
        $messages = [
            'E0001' => 'No comment',
            'M0001' => 'Get comment success',
            'M0002' => 'Create comment success',
            'M0003' => 'Change comment success',
        ];
        return array_key_exists($messageCode, $messages) ? $messages[$messageCode] : '';
    }
}
