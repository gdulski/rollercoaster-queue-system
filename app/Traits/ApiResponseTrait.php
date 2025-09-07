<?php

declare(strict_types=1);

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * API Response Trait
 * 
 * Provides consistent API response methods following DRY principles
 * 
 * @package App\Traits
 */
trait ApiResponseTrait
{
    /**
     * Send success response
     * 
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function successResponse($data = null, string $message = '', int $statusCode = 200): ResponseInterface
    {
        $response = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->respond($response, $statusCode);
    }

    /**
     * Send error response
     * 
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return ResponseInterface
     */
    protected function errorResponse(string $message, int $statusCode = 400, $errors = null): ResponseInterface
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $statusCode);
    }

    /**
     * Send validation error response
     * 
     * @param array $errors
     * @return ResponseInterface
     */
    protected function validationErrorResponse(array $errors): ResponseInterface
    {
        return $this->errorResponse('Dane wejściowe są nieprawidłowe', 400, $errors);
    }

    /**
     * Send not found response
     * 
     * @param string $message
     * @return ResponseInterface
     */
    protected function notFoundResponse(string $message = 'Zasób nie został znaleziony'): ResponseInterface
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Send server error response
     * 
     * @param string $message
     * @return ResponseInterface
     */
    protected function serverErrorResponse(string $message = 'Wystąpił błąd serwera'): ResponseInterface
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Send created response
     * 
     * @param mixed $data
     * @param string $message
     * @return ResponseInterface
     */
    protected function createdResponse($data, string $message = 'Zasób został pomyślnie utworzony'): ResponseInterface
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Send updated response
     * 
     * @param mixed $data
     * @param string $message
     * @return ResponseInterface
     */
    protected function updatedResponse($data, string $message = 'Zasób został pomyślnie zaktualizowany'): ResponseInterface
    {
        return $this->successResponse($data, $message, 200);
    }
}
