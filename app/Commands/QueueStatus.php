<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\StatisticsService;

/**
 * Komenda CLI do wyświetlania aktualnego stanu kolejek górskich
 * 
 * Użycie: php spark coaster:status
 */
class QueueStatus extends BaseCommand
{
    /**
     * Statistics Service
     */
    private readonly StatisticsService $statisticsService;

    /**
     * Grupa komendy
     */
    protected $group = 'Rollercoaster';

    /**
     * Nazwa komendy
     */
    protected $name = 'coaster:status';

    /**
     * Opis komendy
     */
    protected $description = 'Wyświetla aktualny stan wszystkich kolejek górskich';

    /**
     * Użycie komendy
     */
    protected $usage = 'coaster:status [options]';

    /**
     * Argumenty komendy
     */
    protected $arguments = [];

    /**
     * Opcje komendy
     */
    protected $options = [
        '--refresh' => 'Odświeża dane przed wyświetleniem',
        '--json'    => 'Wyświetla wynik w formacie JSON'
    ];

    /**
     * Konstruktor - inicjalizuje StatisticsService
     */
    public function __construct()
    {
        $this->statisticsService = new StatisticsService();
    }

    /**
     * Główna metoda wykonująca komendę
     */
    public function run(array $params): void
    {
        CLI::write('🎢 System Kolejek Górskich - Status', 'green');
        CLI::write('====================================', 'yellow');
        CLI::newLine();

        // Sprawdź opcje
        $refresh = CLI::getOption('refresh');
        $jsonOutput = CLI::getOption('json');

        if ($refresh) {
            CLI::write('🔄 Odświeżanie danych...', 'blue');
            CLI::newLine();
        }

        // Pobierz dane bezpośrednio z StatisticsService
        $statusData = $this->getQueueStatus();

        if ($jsonOutput) {
            $this->displayJsonOutput($statusData);
        } else {
            $this->displayTableOutput($statusData);
        }

        CLI::newLine();
        CLI::write('✅ Status został wyświetlony pomyślnie', 'green');
    }

    /**
     * Pobiera aktualny status kolejek bezpośrednio z StatisticsService
     * 
     * @return array
     */
    private function getQueueStatus(): array
    {
        try {
            if ($this->isRefreshRequested()) {
                CLI::write('🔄 Generowanie statystyk systemu...', 'blue');
            }
            
            // Pobierz dane bezpośrednio z StatisticsService
            $statistics = $this->statisticsService->generateSystemStatistics();
            
            if (!$statistics) {
                CLI::write('❌ Błąd podczas generowania statystyk', 'red');
                return $this->getFallbackData();
            }
            
            return $this->transformStatisticsData($statistics);
            
        } catch (\Exception $e) {
            CLI::write('❌ Błąd podczas pobierania danych: ' . $e->getMessage(), 'red');
            CLI::write('🔄 Używam danych awaryjnych...', 'yellow');
            return $this->getFallbackData();
        }
    }
    
    /**
     * Sprawdza czy została żądana opcja refresh
     * 
     * @return bool
     */
    private function isRefreshRequested(): bool
    {
        return CLI::getOption('refresh') !== null;
    }
    
    /**
     * Transformuje dane z StatisticsService do formatu używanego przez komendę
     * 
     * @param array $statistics
     * @return array
     */
    private function transformStatisticsData(array $statistics): array
    {
        $transformed = [];
        
        foreach ($statistics['coasters'] as $coaster) {
            $transformed[] = [
                'name' => $coaster['name'],
                'redis_id' => $coaster['redis_id'] ?? $coaster['id'],
                'status' => $coaster['status'] === 'OK' ? 'Aktywna' : 'Problem',
                'personnel' => $coaster['available_personnel'] . '/' . $coaster['required_personnel'],
                'wagons' => $coaster['wagon_count'],
                'problems' => empty($coaster['problems']) ? 'Brak problemów' : implode(', ', $coaster['problems']),
                'operating_hours' => $coaster['operating_hours'],
                'daily_customers' => $coaster['daily_customers']
            ];
        }
        
        // Dodaj podsumowanie systemu
        $transformed['summary'] = [
            'timestamp' => '[' . $statistics['timestamp'] . ']',
            'date' => $statistics['date'],
            'total_coasters' => $statistics['summary']['total_coasters'],
            'total_wagons' => $statistics['summary']['total_wagons'],
            'total_personnel' => $statistics['summary']['total_available_personnel'] . '/' . $statistics['summary']['total_required_personnel'],
            'system_status' => $statistics['summary']['system_status'],
            'problematic_coasters' => $statistics['summary']['coasters_with_problems']
        ];
        
        return $transformed;
    }
    
    /**
     * Zwraca dane awaryjne gdy StatisticsService nie działa
     * 
     * @return array
     */
    private function getFallbackData(): array
    {
        return [
            [
                'name' => 'Kolejka Awaryjna',
                'status' => 'Niedostępna',
                'personnel' => 'N/A',
                'wagons' => 0,
                'problems' => 'Błąd StatisticsService'
            ],
            'summary' => [
                'timestamp' => '[' . date('H:i') . ']',
                'date' => date('Y-m-d'),
                'total_coasters' => 0,
                'total_wagons' => 0,
                'total_personnel' => 'N/A',
                'system_status' => 'BŁĄD SYSTEMU',
                'problematic_coasters' => 0
            ]
        ];
    }

    /**
     * Wyświetla dane w formacie tabeli
     * 
     * @param array $data
     */
    private function displayTableOutput(array $data): void
    {
        // Wyciągnij podsumowanie z danych
        $summary = $data['summary'] ?? null;
        unset($data['summary']); // Usuń summary z danych kolejek
        
        if (empty($data)) {
            CLI::write('❌ Brak dostępnych kolejek górskich', 'red');
            return;
        }

        // Wyświetl nagłówek z timestampem
        if ($summary) {
            CLI::write($summary['timestamp'] . ' - ' . $summary['date'], 'cyan');
            CLI::newLine();
        }

        // Wyświetl nagłówek tabeli
        CLI::write(str_repeat('=', 150), 'yellow');
        CLI::write(sprintf(
            '| %-20s | %-25s | %-12s | %-15s | %-8s | %-50s |',
            'Nazwa Kolejki',
            'ID Redis',
            'Status',
            'Personel',
            'Wagony',
            'Problemy'
        ), 'cyan');
        CLI::write(str_repeat('=', 150), 'yellow');

        // Wyświetl dane kolejek
        foreach ($data as $coaster) {
            if (!is_array($coaster)) continue; // Pomiń nieprawidłowe dane
            
            $statusColor = $coaster['status'] === 'Aktywna' ? 'green' : 'red';
            $status = CLI::color($coaster['status'], $statusColor);
            
            // Skróć tekst problemów jeśli jest za długi
            $problems = $coaster['problems'] ?? '';
            if (strlen($problems) > 48) {
                $problems = substr($problems, 0, 45) . '...';
            }
            
            CLI::write(sprintf(
                '| %-20s | %-25s | %-20s | %-15s | %-8s | %-50s |',
                $coaster['name'] ?? 'N/A',
                $coaster['redis_id'] ?? 'N/A',
                $status,
                $coaster['personnel'] ?? 'N/A',
                $coaster['wagons'] ?? 0,
                $problems
            ));
        }
        
        CLI::write(str_repeat('=', 150), 'yellow');
        CLI::newLine();

        // Wyświetl podsumowanie systemu
        if ($summary) {
            CLI::write("📊 Podsumowanie Systemu:", 'cyan');
            CLI::write("   • Status systemu: " . CLI::color($summary['system_status'], 
                $summary['system_status'] === 'OK' ? 'green' : 'red'), 'white');
            CLI::write("   • Łączna liczba kolejek: {$summary['total_coasters']}", 'white');
            CLI::write("   • Łączna liczba wagonów: {$summary['total_wagons']}", 'white');
            CLI::write("   • Łączny personel: {$summary['total_personnel']}", 'white');
            CLI::write("   • Kolejki z problemami: {$summary['problematic_coasters']}", 
                $summary['problematic_coasters'] > 0 ? 'red' : 'green');
        }
    }

    /**
     * Wyświetla dane w formacie JSON
     * 
     * @param array $data
     */
    private function displayJsonOutput(array $data): void
    {
        // Wyciągnij podsumowanie z danych
        $summary = $data['summary'] ?? null;
        unset($data['summary']); // Usuń summary z danych kolejek
        
        $output = [
            'command_timestamp' => date('Y-m-d H:i:s'),
            'api_data' => [
                'timestamp' => $summary['timestamp'] ?? null,
                'date' => $summary['date'] ?? null,
                'coasters' => array_values($data), // Przekonwertuj na indexed array
                'summary' => $summary
            ]
        ];

        CLI::write(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
