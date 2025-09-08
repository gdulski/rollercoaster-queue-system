<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\StatisticsService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Statistics API Controller
 * 
 * Handles rollercoaster system statistics and monitoring endpoints
 * Provides detailed statistics including personnel management analysis
 * 
 * @package App\Controllers\Api
 */
class Statistics extends BaseApiController
{
    private readonly StatisticsService $statisticsService;

    public function __construct()
    {
        $this->statisticsService = new StatisticsService();
    }

    /**
     * Get comprehensive system statistics
     * 
     * GET /api/statistics
     * 
     * Returns statistics for all coasters including:
     * - Operating hours
     * - Wagon counts
     * - Personnel availability vs requirements
     * - Daily customer counts
     * - Problem identification based on personnel management rules
     * 
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $statistics = $this->handleServiceOperation(
            fn() => $this->statisticsServi(),
            'Failed to generate system statistics'
        );

        if (!$statistics) {
            return $this->serverErrorResponse('Nie udało się wygenerować statystyk systemu');
        }

        return $this->successResponse($statistics, 'Statystyki systemu wygenerowane pomyślnie');
    }

    /**
     * Get statistics for specific coaster
     * 
     * GET /api/statistics/coaster/{id}
     * 
     * @param string|null $coasterId
     * @return ResponseInterface
     */
    public function coaster($coasterId = null): ResponseInterface
    {
        $idError = $this->validateId($coasterId);
        if ($idError) {
            return $this->errorResponse($idError, 400);
        }

        $statistics = $this->handleServiceOperation(
            fn() => $this->statisticsService->getCoasterStatistics($coasterId),
            'Failed to fetch coaster statistics'
        );

        if ($statistics === null) {
            return $this->serverErrorResponse('Wystąpił błąd podczas pobierania statystyk kolejki górskiej');
        }

        if (!$statistics) {
            return $this->notFoundResponse('Kolejka górska o podanym ID nie została znaleziona');
        }

        return $this->successResponse($statistics, 'Statystyki kolejki górskiej pobrane pomyślnie');
    }

    /**
     * Get system health check with critical issues
     * 
     * GET /api/statistics/health
     * 
     * @return ResponseInterface
     */
    public function health(): ResponseInterface
    {
        $healthData = $this->handleServiceOperation(
            fn() => $this->statisticsService->getSystemHealth(),
            'Failed to check system health'
        );

        if (!$healthData) {
            return $this->serverErrorResponse('Nie udało się sprawdzić stanu systemu');
        }

        // Return appropriate HTTP status based on system health
        $statusCode = match($healthData['overall_status']) {
            'HEALTHY' => 200,
            'CRITICAL' => 207, // Multi-Status - partial success with issues
            'ERROR' => 500,
            default => 200
        };

        return $this->respond($healthData, $statusCode);
    }

    /**
     * Get formatted statistics display (similar to console output format)
     * 
     * GET /api/statistics/display
     * 
     * Returns statistics in a format similar to the console monitoring output
     * specified in the documentation
     * 
     * @return ResponseInterface
     */
    public function display(): ResponseInterface
    {
        $statistics = $this->handleServiceOperation(
            fn() => $this->statisticsService->generateSystemStatistics(),
            'Failed to generate display statistics'
        );

        if (!$statistics) {
            return $this->serverErrorResponse('Nie udało się wygenerować statystyk do wyświetlenia');
        }

        // Format for display similar to console output
        $displayData = $this->formatForDisplay($statistics);

        return $this->successResponse($displayData, 'Statystyki sformatowane do wyświetlenia');
    }

    /**
     * Format statistics for display output
     * 
     * @param array $statistics
     * @return array
     */
    private function formatForDisplay(array $statistics): array
    {
        $formatted = [
            'header' => '[Godzina ' . $statistics['timestamp'] . ']',
            'date' => $statistics['date'],
            'coasters' => []
        ];

        foreach ($statistics['coasters'] as $coaster) {
            $formattedCoaster = [
                'name' => '[' . $coaster['name'] . ']',
                'details' => [
                    '1. Godziny działania: ' . $coaster['operating_hours'],
                    '2. Liczba wagonów: ' . $coaster['wagon_count'],
                    '3. Dostępny personel: ' . $coaster['available_personnel'] . '/' . $coaster['required_personnel'],
                    '4. Klienci dziennie: ' . $coaster['daily_customers'],
                ]
            ];

            // Add status or problems
            if ($coaster['status'] === 'OK') {
                $formattedCoaster['details'][] = '5. Status: OK';
            } else {
                $problemText = implode(', ', $coaster['problems']);
                $formattedCoaster['details'][] = '5. Problem: ' . $problemText;
            }

            $formatted['coasters'][] = $formattedCoaster;
        }

        // Add summary
        $summary = $statistics['summary'];
        $formatted['summary'] = [
            'total_coasters' => $summary['total_coasters'],
            'total_wagons' => $summary['total_wagons'],
            'total_personnel' => $summary['total_available_personnel'] . '/' . $summary['total_required_personnel'],
            'system_status' => $summary['system_status'],
            'problematic_coasters' => $summary['coasters_with_problems']
        ];

        return $formatted;
    }

    /**
     * Get real-time monitoring data (for CLI service)
     * 
     * GET /api/statistics/monitor
     * 
     * @return ResponseInterface
     */
    public function monitor(): ResponseInterface
    {
        $statistics = $this->handleServiceOperation(
            fn() => $this->statisticsService->generateSystemStatistics(),
            'Failed to generate monitoring data'
        );

        if (!$statistics) {
            return $this->serverErrorResponse('Nie udało się wygenerować danych monitorowania');
        }

        // Add monitoring-specific metadata
        $monitoringData = [
            'monitoring' => true,
            'real_time' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $statistics
        ];

        return $this->successResponse($monitoringData, 'Dane monitorowania pobrane pomyślnie');
    }
}
