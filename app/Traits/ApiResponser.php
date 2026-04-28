<?php

namespace App\Traits;

use App\Enums\HttpStatus;

trait ApiResponser
{
    /**
     * Standard success response (HTTP 200 OK by default).
     *
     * @param mixed $data Payload data.
     * @param string|null $message Optional success message.
     * @param HttpStatus $code HTTP status code.
     * @param mixed $meta Optional pagination or metadata.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(
        $data,
        $message = null,
        $code = HttpStatus::OK,
        $meta = null
    ) {
        $payload = [
            "success" => true,
            "api_status" => "success",
            "message" => $message,
        ];

        if (is_array($data)) {
            $payload = array_merge($payload, $data);
        } else {
            $payload["data"] = $data;
        }

        if ($meta !== null) {
            $payload["meta"] = array_merge($payload["meta"] ?? [], (array) $meta);
        }

        return response()->json($payload, $code->value);
    }

    /**
     * Created response (HTTP 201 Created by default).
     *
     * @param mixed $data Payload data.
     * @param string|null $message Optional success message.
     * @param HttpStatus $code HTTP status code.
     * @param mixed $meta Optional metadata.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createdResponse(
        $data,
        $message = null,
        $code = HttpStatus::CREATED,
        $meta = null
    ) {
        $payload = [
            "success" => true,
            "api_status" => "success",
            "message" => $message,
        ];

        if (is_array($data)) {
            $payload = array_merge($payload, $data);
        } else {
            $payload["data"] = $data;
        }

        if ($meta !== null) {
            $payload["meta"] = array_merge($payload["meta"] ?? [], (array) $meta);
        }

        return response()->json($payload, $code->value);
    }

    /**
     * Standard error response (HTTP 400 Bad Request by default).
     *
     * @param string|null $message Error message.
     * @param string|null $err_code Internal error code.
     * @param HttpStatus $code HTTP status code.
     * @param mixed $data Optional error details.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        $message = null,
        $err_code = null,
        $code = HttpStatus::BAD_REQUEST,
        $data = null
    ) {
        return response()->json([
            "success"    => false,
            "api_status" => "error",
            "message"    => $message,
            "code"       => $err_code,
            "data"       => $data,
            "error"      => [
                "code" => $err_code,
                "message" => $message,
                "details" => $data,
            ],
        ], $code->value);
    }

    /**
     * Validation error response (HTTP 422 Unprocessable Entity).
     *
     * @param mixed $message Validation errors.
     * @param string|null $err_code Internal error code.
     * @param HttpStatus $code HTTP status code.
     * @param mixed $data Optional error details.
     * @return \Illuminate\Http\JsonResponse
     */
    // 422 validation response
    protected function validateError(
        $message = null,
        $err_code = null,
        HttpStatus $code = HttpStatus::VALIDATION_ERROR,
        $data = null
    ) {
        return response()->json([
            "success"    => false,
            "api_status" => "error",
            "message"    => __('auth.invalid_data'),
            "errors"     => $message,
            "code"       => $err_code,
            "data"       => $data,
            "error"      => [
                "code" => $err_code,
                "message" => __('auth.invalid_data'),
                "details" => $message,
            ],
        ], $code->value);
    }

    /**
     * Forbidden response (HTTP 403 Forbidden).
     *
     * @param string|null $message Forbidden message.
     * @param string|null $err_code Internal error code.
     * @param HttpStatus $code HTTP status code.
     * @param mixed $data Optional details.
     * @return \Illuminate\Http\JsonResponse
     */
    // 403 forbidden response
    protected function forbiddenResponse(
        $message = null,
        $err_code = null,
        HttpStatus $code = HttpStatus::FORBIDDEN,
        $data = null
    ) {
        return response()->json(
            [
                "success"    => false,
                "api_status" => "error",
                "message"    => $message,
                "code"       => $err_code,
                "data"       => $data,
            ],
            $code->value
        );
    }
}
