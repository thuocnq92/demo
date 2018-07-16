<?php

namespace App\Http\Controllers\API;

use App\Http\Helper\LogToChannels;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class APIBaseController extends Controller
{
    /**
     * @param $result
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * @param $error
     * @param array $errorMessages
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        // Write log when error
        $contextLog = [
            'user_login' => Auth::check() ? Auth::user()->id : null,
            'input' => request()->all(),
            'errors' => $errorMessages
        ];
        $log = new LogToChannels();
        $log->error('api_error', $error, $contextLog);

        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            // If error code is 422 data always is object
            if ($code == 422) {
                if (count($errorMessages->toArray())) {
                    foreach ($errorMessages->toArray() as $key => $errors) {
                        if (is_array($errors) && count($errors)) {
                            foreach ($errors as $error) {
                                $response['data']['errors'][] = [
                                    'key' => $key,
                                    'error' => $error
                                ];
                            }
                        }
                    }
                } else {
                    $response['data']['errors'] = [];
                }
            } else {
                $response['data'] = $errorMessages;
            }
        }

        return response()->json($response, $code);
    }
}
