<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Wagon Model
 * 
 * Handles data persistence for wagons using Redis
 * Wagons are associated with specific coaster IDs
 * 
 * @package App\Models
 */
class WagonModel extends BaseRedisModel
{
    public string $indexKey;

    public function __construct()
    {
        parent::__construct();
        $this->keyPrefix = 'wagon:';
        $this->indexKey = 'wagons:index';
    }

    /**
     * Save wagon data with coaster association
     * 
     * @param array $data
     * @return bool
     */
    public function save(array $data): bool
    {
        try {
            // Generate wagon ID if not provided
            if (!isset($data['id'])) {
                $data['id'] = $this->generateWagonId($data['coaster_id']);
            }

            // Add created timestamp
            $data['created_at'] = date('Y-m-d H:i:s');

            // Save wagon data
            $result = $this->saveData($data);
            
            if ($result) {
                // Add to global wagon index
                $this->addToIndex($data['id']);
                
                // Add to coaster-specific wagon index
                $this->addToCoasterIndex($data['coaster_id'], $data['id']);
            }

            return $result;

        } catch (\Exception $e) {
            error_log('WagonModel::save failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find wagon by ID
     * 
     * @param string $id
     * @return array|null
     */
    public function find(string $id): ?array
    {
        return $this->findData($id);
    }

    /**
     * Delete wagon by ID
     * 
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            // Get wagon data before deletion to remove from coaster index
            $wagon = $this->find($id);
            if (!$wagon) {
                return false;
            }

            // Remove from global index
            $this->removeFromIndex($id);
            
            // Remove from coaster-specific index
            $this->removeFromCoasterIndex($wagon['coaster_id'], $id);
            
            // Delete wagon data
            return $this->deleteData($id);

        } catch (\Exception $e) {
            error_log('WagonModel::delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all wagons for specific coaster
     * 
     * @param string $coasterId
     * @return array
     */
    public function getByCoasterId(string $coasterId): array
    {
        try {
            $coasterIndexKey = "coaster:{$coasterId}:wagons";
            $wagonIds = $this->redis->smembers($coasterIndexKey);
            
            $wagons = [];
            foreach ($wagonIds as $wagonId) {
                $wagon = $this->find($wagonId);
                if ($wagon) {
                    $wagons[] = $wagon;
                }
            }

            return $wagons;

        } catch (\Exception $e) {
            error_log('WagonModel::getByCoasterId failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count wagons for specific coaster
     * 
     * @param string $coasterId
     * @return int
     */
    public function countByCoasterId(string $coasterId): int
    {
        try {
            $coasterIndexKey = "coaster:{$coasterId}:wagons";
            return $this->redis->scard($coasterIndexKey);

        } catch (\Exception $e) {
            error_log('WagonModel::countByCoasterId failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Add wagon ID to global index
     * 
     * @param string $id
     * @return bool
     */
    private function addToIndex(string $id): bool
    {
        try {
            $result = $this->redis->sadd($this->indexKey, $id);
            return $result > 0;

        } catch (\Exception $e) {
            error_log('WagonModel::addToIndex failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove wagon ID from global index
     * 
     * @param string $id
     * @return bool
     */
    private function removeFromIndex(string $id): bool
    {
        try {
            $result = $this->redis->srem($this->indexKey, $id);
            return $result > 0;

        } catch (\Exception $e) {
            error_log('WagonModel::removeFromIndex failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add wagon to coaster-specific index
     * 
     * @param string $coasterId
     * @param string $wagonId
     * @return bool
     */
    private function addToCoasterIndex(string $coasterId, string $wagonId): bool
    {
        try {
            $coasterIndexKey = "coaster:{$coasterId}:wagons";
            $result = $this->redis->sadd($coasterIndexKey, $wagonId);
            return $result > 0;

        } catch (\Exception $e) {
            error_log('WagonModel::addToCoasterIndex failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove wagon from coaster-specific index
     * 
     * @param string $coasterId
     * @param string $wagonId
     * @return bool
     */
    private function removeFromCoasterIndex(string $coasterId, string $wagonId): bool
    {
        try {
            $coasterIndexKey = "coaster:{$coasterId}:wagons";
            $result = $this->redis->srem($coasterIndexKey, $wagonId);
            return $result > 0;

        } catch (\Exception $e) {
            error_log('WagonModel::removeFromCoasterIndex failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique wagon ID
     * 
     * @param string $coasterId
     * @return string
     */
    private function generateWagonId(string $coasterId): string
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return "wagon_{$coasterId}_{$timestamp}_{$random}";
    }
}
