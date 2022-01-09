<?php


namespace App\Libraries;


class CommonFunction
{
    public static function response($success = false, $status_code = 501, $data = '', $message = '')
    {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ], $status_code);
    }
}
