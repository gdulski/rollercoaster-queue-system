<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Base Redis Model
 * 
 * Provides common Redis operations that can be used by all models
 * 
 * @package App\Models
 */
abstract class BaseRedisModel extends Model
{
    /**
     * Redis connection
     */
    protected $redis;

    /**
     * Key prefix for this model
     */
    protected string $keyPrefix;

    /**
     * Index key for this model
     */
    public string $indexKey;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->redis = \Config\Services::redis();
    }

    /**
     * Save data to Redis
     * 
     * @param array $data
     * @return bool
     */
    public function saveData(array $data): bool
    {
        try {
            $key = $this->keyPrefix . $data['id'];
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            $result = $this->redis->set($key, $jsonData);
            
            return $result === 'OK';

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::save failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find data by ID
     * 
     * @param string $id
     * @return array|null
     */
    public function findData(string $id): ?array
    {
        try {
            $key = $this->keyPrefix . $id;
            $jsonData = $this->redis->get($key);
            
            if ($jsonData === false) {
                return null;
            }
            
            return json_decode($jsonData, true);

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::find failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete data by ID
     * 
     * @param string $id
     * @return bool
     */
    public function deleteData(string $id): bool
    {
        try {
            $key = $this->keyPrefix . $id;
            $result = $this->redis->del($key);
            
            return $result > 0;

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all IDs for this model
     * 
     * @return array
     */
    public function getAllIds(): array
    {
        try {
            // Check if we have an index key (Redis Set approach)
            if (isset($this->indexKey) && $this->indexKey) {
                $ids = $this->redis->smembers($this->indexKey);
                return is_array($ids) ? $ids : [];
            }
            
            // Fallback to keys pattern approach
            $pattern = $this->keyPrefix . '*';
            $keys = $this->redis->keys($pattern);
            
            return array_map(function($key) {
                return str_replace($this->keyPrefix, '', $key);
            }, $keys);

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::getAllIds failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all data for this model
     * 
     * @return array
     */
    public function getAll(): array
    {
        try {
            $ids = $this->getAllIds();
            $data = [];
            
            foreach ($ids as $id) {
                $item = $this->find($id);
                if ($item) {
                    $data[] = $item;
                }
            }
            
            return $data;

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::getAll failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total records
     * 
     * @return int
     */
    public function count(): int
    {
        try {
            // Check if we have an index key (Redis Set approach)
            if (isset($this->indexKey) && $this->indexKey) {
                return $this->redis->scard($this->indexKey);
            }
            
            // Fallback to keys pattern approach
            $pattern = $this->keyPrefix . '*';
            $keys = $this->redis->keys($pattern);
            
            return count($keys);

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::count failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if record exists
     * 
     * @param string $id
     * @return bool
     */
    public function exists(string $id): bool
    {
        try {
            $key = $this->keyPrefix . $id;
            return $this->redis->exists($key) > 0;

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::exists failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all data for this model
     * 
     * @return bool
     */
    public function clearAll(): bool
    {
        try {
            // Check if we have an index key (Redis Set approach)
            if (isset($this->indexKey) && $this->indexKey) {
                // Get all IDs from index
                $ids = $this->getAllIds();
                
                // Delete all records
                foreach ($ids as $id) {
                    $key = $this->keyPrefix . $id;
                    $this->redis->del($key);
                }
                
                // Clear index
                $this->redis->del($this->indexKey);
                
                return true;
            }
            
            // Fallback to keys pattern approach
            $pattern = $this->keyPrefix . '*';
            $keys = $this->redis->keys($pattern);
            
            if (empty($keys)) {
                return true;
            }
            
            $result = $this->redis->del($keys);
            
            return $result > 0;

        } catch (\Exception $e) {
            log_message('error', get_class($this) . '::clearAll failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Redis connection status
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        try {
            $this->redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
