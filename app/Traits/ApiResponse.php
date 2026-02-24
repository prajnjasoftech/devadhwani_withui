<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response.
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a success response with pagination meta.
     */
    protected function successWithPagination($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Return an error response.
     */
    protected function error(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'error' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a not found response.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return a validation error response.
     */
    protected function validationError($errors): JsonResponse
    {
        return response()->json([
            'status' => false,
            'error' => collect($errors->all())->implode(', '),
            'errors' => $errors,
        ], 422);
    }
}
