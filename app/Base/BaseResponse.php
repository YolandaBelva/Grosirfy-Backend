<?php

namespace App\Base;

use Illuminate\Http\Response;

class BaseResponse extends Response
{
    /**
     * Set response JSON
     * 
     * @param boolean $success
     * @param string|int $code
     * @param string $message
     * @param array $errors
     * @param array $data
     * 
     * @return mixed
     */
    protected static function responseJson($success, $code, $message = null, $errors = [], $data = null)
    {
        $param['status'] = $success;
        $param['response_code'] = $code;
        $param['response_message'] = $message;
        $param['errors'] = $errors;
        $param['data'] = $data;

        return $param;
    }

    /**
     * Set responses success
     * 
     * @param array $data
     * @param string|null $message
     * @param array $errors
     * @param int $code
     * 
     * @return mixed
     */
    public static function success($data = null, $message = null, $errors = [], $code = 200)
    {
        return self::responseJson(true, $code, $message, $errors, $data);
    }

    /**
     * Set responses error
     * 
     * @param array $data
     * @param string|null $message
     * @param array $errors
     * @param int $code
     * 
     * @return mixed
     */
    public static function error($data = null, $message = null, $errors = [], $code = 401)
    {
        return self::responseJson(false, $code, $message, $errors, $data);
    }
}