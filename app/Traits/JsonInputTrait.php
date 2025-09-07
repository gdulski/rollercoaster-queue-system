<?php

declare(strict_types=1);

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * JSON Input Trait
 * 
 * Provides consistent JSON input handling following DRY principles
 * 
 * @package App\Traits
 */
trait JsonInputTrait
{
    /**
     * Get and validate JSON input
     * 
     * @return array|ResponseInterface Returns array on success, ResponseInterface on error
     */
    protected function getJsonInput()
    {
        $input = $this->request->getBody() ? json_decode($this->request->getBody(), true) : [];
        
        if (empty($input)) {
            return $this->errorResponse('Nieprawidłowe dane JSON', 400);
        }

        return $input;
    }

    /**
     * Validate required fields
     * 
     * @param array $data
     * @param array $requiredFields
     * @return array|ResponseInterface Returns empty array on success, ResponseInterface on error
     */
    protected function validateRequiredFields(array $data, array $requiredFields)
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[] = "Pole '{$field}' jest wymagane";
            }
        }

        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        return [];
    }

    /**
     * Validate field type
     * 
     * @param mixed $value
     * @param string $fieldName
     * @param string $type
     * @param bool $positive
     * @return string|null Returns error message or null if valid
     */
    protected function validateFieldType($value, string $fieldName, string $type, bool $positive = false): ?string
    {
        switch ($type) {
            case 'integer':
                if (!is_numeric($value) || (int)$value != $value) {
                    return "Pole '{$fieldName}' musi być liczbą całkowitą";
                }
                if ($positive && (int)$value <= 0) {
                    return "Pole '{$fieldName}' musi być dodatnią liczbą całkowitą";
                }
                break;
                
            case 'float':
                if (!is_numeric($value)) {
                    return "Pole '{$fieldName}' musi być liczbą";
                }
                if ($positive && (float)$value <= 0) {
                    return "Pole '{$fieldName}' musi być dodatnią liczbą";
                }
                break;
                
            case 'time':
                if (!$this->isValidTimeFormat($value)) {
                    return "Pole '{$fieldName}' musi być w formacie HH:MM";
                }
                break;
        }

        return null;
    }

    /**
     * Check if time format is valid (HH:MM)
     * 
     * @param string $time
     * @return bool
     */
    private function isValidTimeFormat(string $time): bool
    {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time) === 1;
    }
}
