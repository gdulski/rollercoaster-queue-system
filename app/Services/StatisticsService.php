<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CoasterModel;
use App\Models\WagonModel;

/**
 * Statistics Service
 * 
 * Handles business logic for generating system statistics and identifying problems
 * Based on personnel management rules from the documentation
 * 
 * @package App\Services
 */
class StatisticsService
{
    private readonly CoasterModel $coasterModel;
    private readonly WagonModel $wagonModel;

    public function __construct()
    {
        $this->coasterModel = new CoasterModel();
        $this->wagonModel = new WagonModel();
    }

    /**
     * Generate comprehensive system statistics
     * 
     * @return array
     */
    public function generateSystemStatistics(): array
    {
        try {
            $allCoasterIds = $this->coasterModel->getAllIds();
            $coasters = [];
            
            // Get coaster data for each ID
            foreach ($allCoasterIds as $coasterId) {
                $coaster = $this->coasterModel->findData($coasterId);
                if ($coaster) {
                    $coasters[] = $coaster;
                }
            }
            $statistics = [];

            foreach ($coasters as $coaster) {
                $coasterId = $coaster['id'];
                $wagons = $this->wagonModel->getByCoasterId($coasterId);
                
                $coasterStats = $this->generateCoasterStatistics($coaster, $wagons);
                $statistics[] = $coasterStats;
            }

            return [
                'timestamp' => date('H:i'),
                'date' => date('Y-m-d'),
                'coasters' => $statistics,
                'summary' => $this->generateSystemSummary($statistics)
            ];

        } catch (\Exception $e) {
            log_message('error', 'StatisticsService::generateSystemStatistics failed: ' . $e->getMessage());
            return [
                'timestamp' => date('H:i'),
                'date' => date('Y-m-d'),
                'coasters' => [],
                'summary' => [
                    'total_coasters' => 0,
                    'total_wagons' => 0,
                    'total_personnel' => 0,
                    'total_problems' => 0
                ],
                'error' => 'Wystąpił błąd podczas generowania statystyk'
            ];
        }
    }

    /**
     * Generate statistics for a single coaster
     * 
     * @param array $coaster
     * @param array $wagons
     * @return array
     */
    private function generateCoasterStatistics(array $coaster, array $wagons): array
    {
        $coasterId = $coaster['id'];
        $coasterName = $this->generateCoasterName($coasterId);
        $wagonCount = count($wagons);
        $availablePersonnel = $coaster['staff_count'];
        $dailyCustomers = $coaster['daily_customers'];
        
        // Calculate required personnel based on rules:
        // 1 person per coaster + 2 persons per wagon
        $requiredPersonnel = 1 + ($wagonCount * 2);
        
        // Calculate personnel difference
        $personnelDifference = $availablePersonnel - $requiredPersonnel;
        
        // Determine problems
        $problems = $this->identifyProblems($wagonCount, $availablePersonnel, $requiredPersonnel, $dailyCustomers, $wagons);
        
        return [
            'name' => $coasterName,
            'id' => $coasterId,
            'operating_hours' => $coaster['opening_time'] . ' - ' . $coaster['closing_time'],
            'wagon_count' => $wagonCount,
            'available_personnel' => $availablePersonnel,
            'required_personnel' => $requiredPersonnel,
            'daily_customers' => $dailyCustomers,
            'status' => empty($problems) ? 'OK' : 'PROBLEM',
            'problems' => $problems,
            'track_length' => $coaster['track_length'],
            'personnel_difference' => $personnelDifference,
            'wagons_details' => $this->getWagonDetails($wagons)
        ];
    }

    /**
     * Identify problems with coaster operation
     * 
     * @param int $wagonCount
     * @param int $availablePersonnel
     * @param int $requiredPersonnel
     * @param int $dailyCustomers
     * @param array $wagons
     * @return array
     */
    private function identifyProblems(int $wagonCount, int $availablePersonnel, int $requiredPersonnel, int $dailyCustomers, array $wagons): array
    {
        $problems = [];
        
        // Check personnel shortage
        if ($availablePersonnel < $requiredPersonnel) {
            $shortage = $requiredPersonnel - $availablePersonnel;
            $problems[] = "Brakuje {$shortage} pracowników";
        }
        
        // Check personnel excess
        if ($availablePersonnel > $requiredPersonnel) {
            $excess = $availablePersonnel - $requiredPersonnel;
            $problems[] = "Nadmiar {$excess} pracowników";
        }
        
        // Check wagon capacity vs customer demand
        $totalSeats = array_sum(array_column($wagons, 'seat_count'));
        $estimatedDailyCapacity = $this->calculateDailyCapacity($wagons, 10); // Assuming 10 hours operation
        
        // If capacity is less than 80% of daily customers, suggest more wagons
        if ($estimatedDailyCapacity < ($dailyCustomers * 0.8)) {
            $problems[] = "Zbyt mała przepustowość - rozważ dodanie wagonów";
        }
        
        // If capacity is more than 200% of daily customers, suggest wagon reduction
        if ($estimatedDailyCapacity > ($dailyCustomers * 2)) {
            $problems[] = "Nadmiarowa przepustowość - zbyt dużo wagonów";
        }
        
        return $problems;
    }

    /**
     * Calculate estimated daily capacity based on wagons
     * 
     * @param array $wagons
     * @param int $operatingHours
     * @return int
     */
    private function calculateDailyCapacity(array $wagons, int $operatingHours): int
    {
        if (empty($wagons)) {
            return 0;
        }
        
        $totalSeats = array_sum(array_column($wagons, 'seat_count'));
        $averageSpeed = array_sum(array_column($wagons, 'wagon_speed')) / count($wagons);
        
        // Simplified calculation: assume 15-minute rides including loading/unloading
        $ridesPerHour = 4;
        $dailyRides = $ridesPerHour * $operatingHours;
        
        return $totalSeats * $dailyRides;
    }

    /**
     * Generate coaster display name from ID
     * 
     * @param string $coasterId
     * @return string
     */
    private function generateCoasterName(string $coasterId): string
    {
        // Generate friendly names like "Kolejka A1", "Kolejka A2", etc.
        // Extract unique part from coaster ID for consistent naming
        $hash = crc32($coasterId);
        $letter = chr(65 + (abs($hash) % 26)); // A, B, C...
        $number = (abs($hash) % 99) + 1;
        
        return "Kolejka {$letter}{$number}";
    }

    /**
     * Get detailed wagon information
     * 
     * @param array $wagons
     * @return array
     */
    private function getWagonDetails(array $wagons): array
    {
        $details = [];
        
        foreach ($wagons as $wagon) {
            $details[] = [
                'id' => $wagon['id'],
                'seat_count' => $wagon['seat_count'],
                'speed' => $wagon['wagon_speed'],
                'created_at' => $wagon['created_at']
            ];
        }
        
        return $details;
    }

    /**
     * Generate system summary statistics
     * 
     * @param array $coasterStatistics
     * @return array
     */
    private function generateSystemSummary(array $coasterStatistics): array
    {
        $totalCoasters = count($coasterStatistics);
        $totalWagons = array_sum(array_column($coasterStatistics, 'wagon_count'));
        $totalPersonnel = array_sum(array_column($coasterStatistics, 'available_personnel'));
        $totalCustomers = array_sum(array_column($coasterStatistics, 'daily_customers'));
        $totalProblems = count(array_filter($coasterStatistics, fn($c) => $c['status'] === 'PROBLEM'));
        
        // Calculate required personnel for all coasters
        $totalRequiredPersonnel = array_sum(array_column($coasterStatistics, 'required_personnel'));
        $personnelDifference = $totalPersonnel - $totalRequiredPersonnel;
        
        return [
            'total_coasters' => $totalCoasters,
            'total_wagons' => $totalWagons,
            'total_available_personnel' => $totalPersonnel,
            'total_required_personnel' => $totalRequiredPersonnel,
            'personnel_difference' => $personnelDifference,
            'total_daily_customers' => $totalCustomers,
            'coasters_with_problems' => $totalProblems,
            'system_status' => $totalProblems === 0 ? 'OK' : 'UWAGA - wykryto problemy',
            'average_customers_per_coaster' => $totalCoasters > 0 ? round($totalCustomers / $totalCoasters) : 0,
            'average_wagons_per_coaster' => $totalCoasters > 0 ? round($totalWagons / $totalCoasters, 1) : 0
        ];
    }

    /**
     * Get statistics for a specific coaster
     * 
     * @param string $coasterId
     * @return array|null
     */
    public function getCoasterStatistics(string $coasterId): ?array
    {
        try {
            $coaster = $this->coasterModel->findData($coasterId);
            if (!$coaster) {
                return null;
            }
            
            $wagons = $this->wagonModel->getByCoasterId($coasterId);
            return $this->generateCoasterStatistics($coaster, $wagons);

        } catch (\Exception $e) {
            log_message('error', 'StatisticsService::getCoasterStatistics failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check system health and identify critical issues
     * 
     * @return array
     */
    public function getSystemHealth(): array
    {
        try {
            $statistics = $this->generateSystemStatistics();
            $criticalIssues = [];
            
            foreach ($statistics['coasters'] as $coaster) {
                if ($coaster['status'] === 'PROBLEM') {
                    $criticalIssues[] = [
                        'coaster_name' => $coaster['name'],
                        'coaster_id' => $coaster['id'],
                        'problems' => $coaster['problems']
                    ];
                }
            }
            
            return [
                'overall_status' => empty($criticalIssues) ? 'HEALTHY' : 'CRITICAL',
                'total_coasters' => $statistics['summary']['total_coasters'],
                'problematic_coasters' => count($criticalIssues),
                'critical_issues' => $criticalIssues,
                'last_check' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            log_message('error', 'StatisticsService::getSystemHealth failed: ' . $e->getMessage());
            return [
                'overall_status' => 'ERROR',
                'error' => 'Nie udało się sprawdzić stanu systemu',
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }
}
