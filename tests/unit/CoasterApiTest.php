<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class CoasterApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear Redis data before each test
        $redis = \Config\Services::redis();
        $redis->flushdb();
    }

    public function testCreateCoasterSuccess(): void
    {
        $data = [
            'staff_count' => 16,
            'daily_customers' => 60000,
            'track_length' => 1800,
            'opening_time' => '8:00',
            'closing_time' => '16:00'
        ];

        $result = $this->post('/api/coasters', $data);
        
        $result->assertStatus(201);
        $result->assertJSONFragment(['success' => true]);
        $result->assertJSONFragment(['staff_count' => 16]);
        $result->assertJSONFragment(['daily_customers' => 60000]);
    }

    public function testCreateCoasterValidationError(): void
    {
        $data = [
            'staff_count' => 16
            // Missing required fields
        ];

        $result = $this->post('/api/coasters', $data);
        
        $result->assertStatus(400);
        $result->assertJSONFragment(['status' => 400]);
    }

    public function testCreateCoasterInvalidTimeFormat(): void
    {
        $data = [
            'staff_count' => 16,
            'daily_customers' => 60000,
            'track_length' => 1800,
            'opening_time' => '25:00', // Invalid time
            'closing_time' => '16:00'
        ];

        $result = $this->post('/api/coasters', $data);
        
        $result->assertStatus(400);
        $result->assertJSONFragment(['status' => 400]);
    }

    public function testCreateCoasterInvalidTimeRange(): void
    {
        $data = [
            'staff_count' => 16,
            'daily_customers' => 60000,
            'track_length' => 1800,
            'opening_time' => '18:00', // After closing time
            'closing_time' => '16:00'
        ];

        $result = $this->post('/api/coasters', $data);
        
        $result->assertStatus(400);
        $result->assertJSONFragment(['status' => 400]);
    }

    public function testGetAllCoasters(): void
    {
        // First create a coaster
        $data = [
            'staff_count' => 16,
            'daily_customers' => 60000,
            'track_length' => 1800,
            'opening_time' => '8:00',
            'closing_time' => '16:00'
        ];
        
        $this->post('/api/coasters', $data);

        $result = $this->get('/api/coasters');
        
        $result->assertStatus(200);
        $result->assertJSONFragment(['success' => true]);
        $result->assertJSONFragment(['staff_count' => 16]);
    }

    public function testGetCoasterById(): void
    {
        // First create a coaster
        $data = [
            'staff_count' => 16,
            'daily_customers' => 60000,
            'track_length' => 1800,
            'opening_time' => '8:00',
            'closing_time' => '16:00'
        ];
        
        $createResult = $this->post('/api/coasters', $data);
        $coasterId = $createResult->getJSON()['data']['id'];

        $result = $this->get("/api/coasters/{$coasterId}");
        
        $result->assertStatus(200);
        $result->assertJSONFragment(['success' => true]);
        $result->assertJSONFragment(['id' => $coasterId]);
    }

    public function testGetCoasterByIdNotFound(): void
    {
        $result = $this->get('/api/coasters/nonexistent_id');
        
        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 404]);
    }

    public function testUpdateCoaster(): void
    {
        // First create a coaster
        $data = [
            'staff_count' => 16,
            'daily_customers' => 60000,
            'track_length' => 1800,
            'opening_time' => '8:00',
            'closing_time' => '16:00'
        ];
        
        $createResult = $this->post('/api/coasters', $data);
        $coasterId = $createResult->getJSON()['data']['id'];

        // Update the coaster
        $updateData = [
            'staff_count' => 20,
            'daily_customers' => 70000,
            'track_length' => 1800,
            'opening_time' => '7:00',
            'closing_time' => '17:00'
        ];

        $result = $this->put("/api/coasters/{$coasterId}", $updateData);
        
        $result->assertStatus(200);
        $result->assertJSONFragment(['success' => true]);
        $result->assertJSONFragment(['staff_count' => 20]);
        $result->assertJSONFragment(['daily_customers' => 70000]);
    }

    public function testUpdateCoasterNotFound(): void
    {
        $data = [
            'staff_count' => 20,
            'daily_customers' => 70000,
            'track_length' => 1800,
            'opening_time' => '7:00',
            'closing_time' => '17:00'
        ];

        $result = $this->put('/api/coasters/nonexistent_id', $data);
        
        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 404]);
    }
}
