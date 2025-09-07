<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Validation Entity Enum
 * 
 * Defines available validation entities
 * 
 * @package App\Enums
 */
enum ValidationEntity: string
{
    case COASTER = 'coaster';
    case WAGON = 'wagon';

    /**
     * Get all entity values
     * 
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


    /**
     * Get validation class name for entity
     * 
     * @param ValidationEntity $entity
     * @return string
     */
    public static function getValidationClass(ValidationEntity $entity): string
    {
        $entityName = ucfirst($entity->value) . 'Validation';
        return "App\\Validation\\{$entityName}";
    }
}
