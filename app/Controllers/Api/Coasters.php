<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\CoasterService;
use App\Enums\ValidationEntity;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Coasters API Controller
 * 
 * Handles rollercoaster queue management operations
 * 
 * @package App\Controllers\Api
 */
class Coasters extends BaseApiController
{
    private CoasterService $coasterService;

    public function __construct()
    {
        $this->coasterService = new CoasterService();
    }

    /**
     * Create a new rollercoaster queue
     * 
     * POST /api/coasters
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

        // Use CodeIgniter validation
        if (!$this->validateWithRules($input, ValidationEntity::COASTER)) {
            return $this->validationErrorResponse($this->getValidationErrors());
        }

        // Validate time range
        if (!$this->validateTimeRange($input['opening_time'], $input['closing_time'])) {
            return $this->validationErrorResponse(['closing_time' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia']);
        }

        // Create coaster
        $coaster = $this->handleServiceOperation(
            fn() => $this->coasterService->createCoaster($input),
            'Coaster creation failed'
        );

        if (!$coaster) {
            return $this->serverErrorResponse('Nie udało się utworzyć kolejki górskiej');
        }

        return $this->createdResponse($coaster, 'Kolejka górska została pomyślnie utworzona');
    }

    /**
     * Get all rollercoaster queues
     * 
     * GET /api/coasters
     * 
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        
        $coasters = $this->handleServiceOperation(
            fn() => $this->coasterService->getAllCoasters(),
            'Failed to fetch coasters'
        );

        if ($coasters === null) {
            return $this->serverErrorResponse('Wystąpił błąd podczas pobierania kolejek górskich');
        }

        return $this->successResponse($coasters);
    }

    /**
     * Get specific rollercoaster queue
     * 
     * GET /api/coasters/{id}
     * 
     * @param string|null $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {

        $idError = $this->validateId($id);
        if ($idError) {
            return $this->errorResponse($idError, 400);
        }

        $coaster = $this->handleServiceOperation(
            fn() => $this->coasterService->getCoasterById($id),
            'Failed to fetch coaster'
        );

        if ($coaster === null) {
            return $this->serverErrorResponse('Wystąpił błąd podczas pobierania kolejki górskiej');
        }

        if (!$coaster) {
            return $this->notFoundResponse('Kolejka górska o podanym ID nie została znaleziona');
        }

        return $this->successResponse($coaster);
    }

    /**
     * Update rollercoaster queue
     * 
     * PUT /api/coasters/{id}
     * 
     * @param string|null $id
     * @return ResponseInterface
     */
    public function update($id = null): ResponseInterface
    {
        $idError = $this->validateId($id);
        if ($idError) {
            return $this->errorResponse($idError, 400);
        }

        // Get and validate JSON input
        $input = $this->getValidatedJsonInput();
        if ($input instanceof ResponseInterface) {
            return $input;
        }

        // Use CodeIgniter validation
        if (!$this->validateWithRules($input, ValidationEntity::COASTER)) {
            return $this->validationErrorResponse($this->getValidationErrors());
        }

        // Validate time range
        if (!$this->validateTimeRange($input['opening_time'], $input['closing_time'])) {
            return $this->validationErrorResponse(['closing_time' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia']);
        }

        // Update coaster
        $coaster = $this->handleServiceOperation(
            fn() => $this->coasterService->updateCoaster($id, $input),
            'Coaster update failed'
        );

        if ($coaster === null) {
            return $this->serverErrorResponse('Wystąpił błąd podczas aktualizacji kolejki górskiej');
        }

        if (!$coaster) {
            return $this->notFoundResponse('Kolejka górska o podanym ID nie została znaleziona');
        }

        return $this->updatedResponse($coaster, 'Kolejka górska została pomyślnie zaktualizowana');
    }


}
