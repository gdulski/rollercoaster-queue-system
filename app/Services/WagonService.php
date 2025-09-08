<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WagonModel;
use App\Models\CoasterModel;

/**
 * Wagon Service
 * 
 * Handles business logic for wagon operations
 * 
 * @package App\Services
 */
class WagonService
{
    private WagonModel $wagonModel;
    private CoasterModel $coasterModel;

    public function __construct()
    {
        $this->wagonModel = new WagonModel();
        $this->coasterModel = new CoasterModel();
    }

    /**
     * Create a new wagon for specific coaster
     * 
     * @param string $coasterId
     * @param array $wagonData
     * @return array|null Returns wagon data on success, null on failure
     */
    public function createWagon(string $coasterId, array $wagonData): ?array
    {
        try {
            // Verify coaster exists
            if (!$this->coasterModel->exists($coasterId)) {
                throw new \InvalidArgumentException("Kolejka górska o ID '{$coasterId}' nie istnieje");
            }

            // Prepare wagon data
            $wagon = [
                'coaster_id' => $coasterId,
                'ilosc_miejsc' => (int) $wagonData['ilosc_miejsc'],
                'predkosc_wagonu' => (float) $wagonData['predkosc_wagonu'],
            ];

            // Save wagon
            $success = $this->wagonModel->save($wagon);
            
            if (!$success) {
                throw new \RuntimeException('Nie udało się zapisać wagonu');
            }

            // Return created wagon with ID
            return $wagon;

        } catch (\Exception $e) {
            log_message('error', 'WagonService::createWagon failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get wagon by ID
     * 
     * @param string $wagonId
     * @return array|null
     */
    public function getWagon(string $wagonId): ?array
    {
        try {
            return $this->wagonModel->find($wagonId);

        } catch (\Exception $e) {
            log_message('error', 'WagonService::getWagon failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete wagon by ID
     * 
     * @param string $coasterId
     * @param string $wagonId
     * @return bool
     */
    public function deleteWagon(string $coasterId, string $wagonId): bool
    {
        try {
            // Verify coaster exists
            if (!$this->coasterModel->exists($coasterId)) {
                throw new \InvalidArgumentException("Kolejka górska o ID '{$coasterId}' nie istnieje");
            }

            // Get wagon to verify it belongs to the coaster
            $wagon = $this->wagonModel->find($wagonId);
            if (!$wagon) {
                throw new \InvalidArgumentException("Wagon o ID '{$wagonId}' nie istnieje");
            }

            if ($wagon['coaster_id'] !== $coasterId) {
                throw new \InvalidArgumentException("Wagon '{$wagonId}' nie należy do kolejki '{$coasterId}'");
            }

            // Delete wagon
            return $this->wagonModel->delete($wagonId);

        } catch (\Exception $e) {
            log_message('error', 'WagonService::deleteWagon failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all wagons for specific coaster
     * 
     * @param string $coasterId
     * @return array
     */
    public function getWagonsByCoaster(string $coasterId): array
    {
        try {
            // Verify coaster exists
            if (!$this->coasterModel->exists($coasterId)) {
                return [];
            }

            return $this->wagonModel->getByCoasterId($coasterId);

        } catch (\Exception $e) {
            log_message('error', 'WagonService::getWagonsByCoaster failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count wagons for specific coaster
     * 
     * @param string $coasterId
     * @return int
     */
    public function countWagonsByCoaster(string $coasterId): int
    {
        try {
            return $this->wagonModel->countByCoasterId($coasterId);

        } catch (\Exception $e) {
            log_message('error', 'WagonService::countWagonsByCoaster failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Validate wagon data structure
     * 
     * @param array $data
     * @return bool
     */
    public function validateWagonData(array $data): bool
    {
        $required = ['ilosc_miejsc', 'predkosc_wagonu'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        // Additional business rules validation
        if ($data['ilosc_miejsc'] <= 0 || $data['predkosc_wagonu'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Calculate wagon capacity metrics
     * 
     * @param array $wagons
     * @return array
     */
    public function calculateCapacityMetrics(array $wagons): array
    {
        $totalSeats = 0;
        $averageSpeed = 0;
        $wagonCount = count($wagons);

        if ($wagonCount === 0) {
            return [
                'total_seats' => 0,
                'average_speed' => 0,
                'wagon_count' => 0,
            ];
        }

        foreach ($wagons as $wagon) {
            $totalSeats += $wagon['ilosc_miejsc'];
            $averageSpeed += $wagon['predkosc_wagonu'];
        }

        return [
            'total_seats' => $totalSeats,
            'average_speed' => round($averageSpeed / $wagonCount, 2),
            'wagon_count' => $wagonCount,
        ];
    }
}
