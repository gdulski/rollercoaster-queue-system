<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Traits\ApiResponseTrait;
use App\Traits\JsonInputTrait;
use App\Validation\ValidationFactory;
use App\Enums\ValidationEntity;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Base API Controller
 * 
 * Provides common functionality for all API controllers following DRY principles
 * 
 * @package App\Controllers\Api
 */
abstract class BaseApiController extends ResourceController
{
    use ApiResponseTrait, JsonInputTrait;

    /**
     * Handle service operation with error handling
     * 
     * @param callable $operation
     * @param string $errorMessage
     * @return mixed
     */
    protected function handleServiceOperation(callable $operation, string $errorMessage = 'Wystąpił błąd serwera')
    {
        try {
            return $operation();
        } catch (\Exception $e) {
            log_message('error', $errorMessage . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate required ID parameter
     * 
     * @param mixed $id
     * @return string|null Returns error message or null if valid
     */
    protected function validateId($id): ?string
    {
        if (empty($id)) {
            return 'ID jest wymagane';
        }
        return null;
    }

    /**
     * Get and validate JSON input with error handling
     * 
     * @return array|ResponseInterface
     */
    protected function getValidatedJsonInput()
    {
        $input = $this->getJsonInput();
        if ($input instanceof ResponseInterface) {
            return $input;
        }
        return $input;
    }

    /**
     * Validate data using CodeIgniter validation rules
     * 
     * @param array $data
     * @param ValidationEntity $entity
     * @return bool
     */
    protected function validateWithRules(array $data, ValidationEntity $entity): bool
    {
        $validation = \Config\Services::validation();
        $rules = ValidationFactory::getRules($entity);
        $messages = ValidationFactory::getMessages($entity);
        
        $validation->setRules($rules);
        
        // Set custom error messages
        foreach ($messages as $field => $fieldMessages) {
            foreach ($fieldMessages as $rule => $message) {
                $validation->setRule($field, $field, $rules[$field], [$message]);
            }
        }
        
        return $validation->run($data);
    }

    /**
     * Get validation errors
     * 
     * @return array
     */
    protected function getValidationErrors(): array
    {
        $validation = \Config\Services::validation();
        return $validation->getErrors();
    }

    /**
     * Validate time range
     * 
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    protected function validateTimeRange(string $startTime, string $endTime): bool
    {
        $startTimestamp = strtotime($startTime);
        $endTimestamp = strtotime($endTime);
        return $endTimestamp > $startTimestamp;
    }
}
