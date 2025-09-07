<?php

declare(strict_types=1);

namespace App\Validation;

use App\Enums\ValidationEntity;

/**
 * Validation Factory
 * 
 * Creates validation rules for different entities
 * 
 * @package App\Validation
 */
class ValidationFactory
{
    /**
     * Get validation rules for entity
     * 
     * @param ValidationEntity $entity
     * @return array
     */
    public static function getRules(ValidationEntity $entity): array
    {
        $className = ValidationEntity::getValidationClass($entity);
        
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Validation class for entity '{$entity->value}' not found");
        }
        
        $validator = new $className();
        return $validator->rules;
    }

    /**
     * Get validation messages for entity
     * 
     * @param ValidationEntity $entity
     * @return array
     */
    public static function getMessages(ValidationEntity $entity): array
    {
        $className = ValidationEntity::getValidationClass($entity);
        
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Validation class for entity '{$entity->value}' not found");
        }
        
        $validator = new $className();
        return $validator->messages;
    }
}
