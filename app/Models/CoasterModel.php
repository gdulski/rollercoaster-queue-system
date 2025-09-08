<?php

declare(strict_types=1);

namespace App\Models;

use Predis\Client;

/**
 * Coaster Model
 * 
 * Handles data persistence for rollercoaster queues using Redis
 * 
 * @package App\Models
 */
class CoasterModel extends BaseRedisModel
{
    public string $indexKey;

    public function __construct()
    {
        parent::__construct();
        $this->keyPrefix = 'coaster:';
        $this->indexKey = 'coasters:index';
    }




    /**
     * Add coaster ID to index
     * 
     * @param string $id
     * @return bool
     */
    public function addToIndex(string $id): bool
    {
        try {
            $result = $this->redis->sadd($this->indexKey, $id);
            return $result > 0;

        } catch (\Exception $e) {
            error_log('CoasterModel::addToIndex failed: ' . $e->getMessage());
            return false;
        }
    }




    /**
     * Get coasters with pagination
     * 
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function paginateCoasters(int $offset = 0, int $limit = 10): array
    {
        try {
            $ids = $this->redis->sscan($this->indexKey, $offset, ['COUNT' => $limit]);
            $coasters = [];

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $coaster = $this->findData($id);
                    if ($coaster) {
                        $coasters[] = $coaster;
                    }
                }
            }

            return $coasters;

        } catch (\Exception $e) {
            error_log('CoasterModel::paginate failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search coasters by criteria
     * 
     * @param array $criteria
     * @return array
     */
    public function search(array $criteria): array
    {
        try {
            $allIds = $this->getAllIds();
            $results = [];

            foreach ($allIds as $id) {
                $coaster = $this->findData($id);
                if (!$coaster) {
                    continue;
                }

                $matches = true;
                foreach ($criteria as $field => $value) {
                    if (!isset($coaster[$field]) || $coaster[$field] != $value) {
                        $matches = false;
                        break;
                    }
                }

                if ($matches) {
                    $results[] = $coaster;
                }
            }

            return $results;

        } catch (\Exception $e) {
            error_log('CoasterModel::search failed: ' . $e->getMessage());
            return [];
        }
    }


}
