<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CoasterModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Coaster Service
 * 
 * Handles business logic for rollercoaster queue operations
 * 
 * @package App\Services
 */
class CoasterService
{
    private readonly CoasterModel $coasterModel;

    public function __construct()
    {
        $this->coasterModel = new CoasterModel();
    }

    /**
     * Create a new coaster
     * 
     * @param array $data
     * @return array|null
     */
    public function createCoaster(array $data): ?array
    {
        try {
            // Generate unique coaster ID
            $coasterId = $this->generateCoasterId();
            
            // Prepare coaster data
            $coasterData = [
                'id' => $coasterId,
                'staff_count' => (int) $data['staff_count'],
                'daily_customers' => (int) $data['daily_customers'],
                'track_length' => (float) $data['track_length'],
                'opening_time' => $data['opening_time'],
                'closing_time' => $data['closing_time'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Save to Redis
            $saved = $this->coasterModel->saveData($coasterData);
            
            if (!$saved) {
                error_log('Failed to save coaster to Redis: ' . json_encode($coasterData));
                return null;
            }

            // Add coaster to index
            $this->coasterModel->addToIndex($coasterId);

            error_log('Coaster created successfully: ' . $coasterId);
            
            return $coasterData;

        } catch (\Exception $e) {
            error_log('CoasterService::createCoaster failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all coasters
     * 
     * @return array
     */
    public function getAllCoasters(): array
    {
        try {
            $coasterIds = $this->coasterModel->getAllIds();
            $coasters = [];

            foreach ($coasterIds as $id) {
                $coaster = $this->coasterModel->findData($id);
                if ($coaster) {
                    $coasters[] = $coaster;
                }
            }

            return $coasters;

        } catch (\Exception $e) {
            error_log('CoasterService::getAllCoasters failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get coaster by ID
     * 
     * @param string $id
     * @return array|null
     */
    public function getCoasterById(string $id): ?array
    {
        try {
            return $this->coasterModel->findData($id);
        } catch (\Exception $e) {
            error_log('CoasterService::getCoasterById failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update coaster
     * 
     * @param string $id
     * @param array $data
     * @return array|null
     */
    public function updateCoaster(string $id, array $data): ?array
    {
        try {
            // Check if coaster exists
            $existingCoaster = $this->coasterModel->findData($id);
            if (!$existingCoaster) {
                return null;
            }

            // Prepare update data (track_length cannot be changed)
            $updateData = [
                'id' => $id,
                'staff_count' => (int) $data['staff_count'],
                'daily_customers' => (int) $data['daily_customers'],
                'track_length' => $existingCoaster['track_length'], // Keep original value
                'opening_time' => $data['opening_time'],
                'closing_time' => $data['closing_time'],
                'created_at' => $existingCoaster['created_at'], // Keep original value
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update in Redis
            $updated = $this->coasterModel->saveData($updateData);
            
            if (!$updated) {
                error_log('Failed to update coaster in Redis: ' . json_encode($updateData));
                return null;
            }

            error_log('Coaster updated successfully: ' . $id);
            
            return $updateData;

        } catch (\Exception $e) {
            error_log('CoasterService::updateCoaster failed: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Generate unique coaster ID
     * 
     * @return string
     */
    private function generateCoasterId(): string
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return "coaster_{$timestamp}_{$random}";
    }

    /**
     * Get coaster statistics
     * 
     * @return array
     */
    public function getCoasterStatistics(): array
    {
        try {
            $coasters = $this->getAllCoasters();
            
            $totalCoasters = count($coasters);
            $totalPersonnel = array_sum(array_column($coasters, 'staff_count'));
            $totalClients = array_sum(array_column($coasters, 'daily_customers'));
            $totalTrackLength = array_sum(array_column($coasters, 'track_length'));

            return [
                'total_coasters' => $totalCoasters,
                'total_personnel' => $totalPersonnel,
                'total_clients' => $totalClients,
                'total_track_length' => $totalTrackLength,
                'average_personnel_per_coaster' => $totalCoasters > 0 ? round($totalPersonnel / $totalCoasters, 2) : 0,
                'average_clients_per_coaster' => $totalCoasters > 0 ? round($totalClients / $totalCoasters, 2) : 0
            ];

        } catch (\Exception $e) {
            error_log('CoasterService::getCoasterStatistics failed: ' . $e->getMessage());
            return [];
        }
    }
}
