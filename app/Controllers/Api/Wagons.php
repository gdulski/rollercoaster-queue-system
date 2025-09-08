<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\WagonService;
use App\Enums\ValidationEntity;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Wagons API Controller
 * 
 * Handles wagon management operations for specific coasters
 * 
 * @package App\Controllers\Api
 */
class Wagons extends BaseApiController
{
    private WagonService $wagonService;

    public function __construct()
    {
        $this->wagonService = new WagonService();
    }

    /**
     * Create a new wagon for specific coaster
     * 
     * POST /api/coasters/{coasterId}/wagons
     * 
     * @param string $coasterId
     * @return ResponseInterface
     */
    public function createWagon(string $coasterId): ResponseInterface
    {
        // Validate coaster ID parameter
        $idError = $this->validateId($coasterId);
        if ($idError) {
            return $this->errorResponse($idError, 400);
        }

        // Get and validate JSON input
        $input = $this->getValidatedJsonInput();
        if ($input instanceof ResponseInterface) {
            return $input;
        }

        // Use CodeIgniter validation with enum
        if (!$this->validateWithRules($input, ValidationEntity::WAGON)) {
            return $this->validationErrorResponse($this->getValidationErrors());
        }

        // Create wagon using service
        $wagon = $this->handleServiceOperation(
            fn() => $this->wagonService->createWagon($coasterId, $input),
            'Nie udało się utworzyć wagonu'
        );

        if ($wagon === null) {
            return $this->serverErrorResponse('Wystąpił błąd podczas tworzenia wagonu');
        }

        return $this->createdResponse($wagon, 'Wagon został pomyślnie utworzony');
    }

    /**
     * Delete wagon from specific coaster
     * 
     * DELETE /api/coasters/{coasterId}/wagons/{wagonId}
     * 
     * @param string $coasterId
     * @param string $wagonId
     * @return ResponseInterface
     */
    public function deleteWagon(string $coasterId, string $wagonId): ResponseInterface
    {
        // Validate parameters
        $coastIdError = $this->validateId($coasterId);
        if ($coastIdError) {
            return $this->errorResponse($coastIdError, 400);
        }

        $wagonIdError = $this->validateId($wagonId);
        if ($wagonIdError) {
            return $this->errorResponse('ID wagonu jest wymagane', 400);
        }

        // Delete wagon using service
        $success = $this->handleServiceOperation(
            fn() => $this->wagonService->deleteWagon($coasterId, $wagonId),
            'Nie udało się usunąć wagonu'
        );

        if ($success === null || !$success) {
            return $this->notFoundResponse('Wagon nie został znaleziony lub nie można go usunąć');
        }

        return $this->successResponse([], 'Wagon został pomyślnie usunięty');
    }

    /**
     * Get all wagons for specific coaster
     * 
     * GET /api/coasters/{coasterId}/wagons
     * 
     * @param string $coasterId
     * @return ResponseInterface
     */
    public function getWagons(string $coasterId): ResponseInterface
    {
        // Validate coaster ID parameter
        $idError = $this->validateId($coasterId);
        if ($idError) {
            return $this->errorResponse($idError, 400);
        }

        // Get wagons using service
        $wagons = $this->handleServiceOperation(
            fn() => $this->wagonService->getWagonsByCoaster($coasterId),
            'Nie udało się pobrać listy wagonów'
        );

        if ($wagons === null) {
            return $this->serverErrorResponse('Wystąpił błąd podczas pobierania wagonów');
        }

        // Calculate additional metrics
        $metrics = $this->wagonService->calculateCapacityMetrics($wagons);

        return $this->successResponse([
            'wagons' => $wagons,
            'summary' => $metrics,
        ], 'Lista wagonów została pomyślnie pobrana');
    }

    /**
     * Get specific wagon details
     * 
     * GET /api/coasters/{coasterId}/wagons/{wagonId}
     * 
     * @param string $coasterId
     * @param string $wagonId
     * @return ResponseInterface
     */
    public function getWagon(string $coasterId, string $wagonId): ResponseInterface
    {
        // Validate parameters
        $coastIdError = $this->validateId($coasterId);
        if ($coastIdError) {
            return $this->errorResponse($coastIdError, 400);
        }

        $wagonIdError = $this->validateId($wagonId);
        if ($wagonIdError) {
            return $this->errorResponse('ID wagonu jest wymagane', 400);
        }

        // Get wagon using service
        $wagon = $this->handleServiceOperation(
            fn() => $this->wagonService->getWagon($wagonId),
            'Nie udało się pobrać danych wagonu'
        );

        if ($wagon === null) {
            return $this->notFoundResponse('Wagon nie został znaleziony');
        }

        // Verify wagon belongs to the coaster
        if ($wagon['coaster_id'] !== $coasterId) {
            return $this->notFoundResponse('Wagon nie należy do określonej kolejki górskiej');
        }

        return $this->successResponse($wagon, 'Dane wagonu zostały pomyślnie pobrane');
    }
}
