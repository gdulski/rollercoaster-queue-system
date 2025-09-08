<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class Health extends ResourceController
{
    /**
     * Health check endpoint
     * GET /api/health
     */
    public function index()
    {
        $data = [
            'status' => 'OK',
            'message' => 'System kolejek górskich działa poprawnie',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'environment' => ENVIRONMENT
        ];

        return $this->respond($data, 200);
    }

    /**
     * Redis connection test
     * GET /api/health/redis
     */
    public function redis()
    {
        try {
            $redis = \Config\Services::redis();
            $redis->ping();
            
            $data = [
                'status' => 'OK',
                'message' => 'Połączenie z Redis działa poprawnie',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return $this->respond($data, 200);
        } catch (\Exception $e) {
            $data = [
                'status' => 'ERROR',
                'message' => 'Błąd połączenia z Redis: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return $this->respond($data, 500);
        }
    }
}






