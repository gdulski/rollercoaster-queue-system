<?php

declare(strict_types=1);

namespace App\Controllers\Api;

// use App\Services\WagonService; // TODO: Implement when needed
use App\Enums\ValidationEntity;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Wagons API Controller
 * 
 * Handles wagon management operations
 * 
 * @package App\Controllers\Api
 */
class Wagons extends BaseApiController
{
    // private WagonService $wagonService; // TODO: Implement when needed

    // public function __construct()
    // {
    //     $this->wagonService = new WagonService();
    // }

    /**
     * Create a new wagon
     * 
     * POST /api/coasters/{coasterId}/wagons
     * 
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        // Get and validate JSON input
        $input = $this->getValidatedJsonInput();
        if ($input instanceof ResponseInterface) {
            return $input;
        }

        // Use CodeIgniter validation with enum
        if (!$this->validateWithRules($input, ValidationEntity::WAGON)) {
            return $this->validationErrorResponse($this->getValidationErrors());
        }

        // TODO: Implement wagon creation logic
        return $this->createdResponse(['message' => 'Wagon validation passed'], 'Wagon został pomyślnie utworzony');
    }
}
