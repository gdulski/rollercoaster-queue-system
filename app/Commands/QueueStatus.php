<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\HTTP\CURLRequest;

/**
 * Komenda CLI do wyświetlania aktualnego stanu kolejek górskich
 * 
 * Użycie: php spark coaster:status
 */
class QueueStatus extends BaseCommand
{
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

        // Na razie wyświetlamy prosty tekst jak zostało poproszone
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
     * Pobiera aktualny status kolejek z API endpoint /api/statistics/display
     * 
     * @return array
     */
    private function getQueueStatus(): array
    {
        try {
            // Pobierz dane z API endpoint
            $apiUrl = 'http://localhost:8080/api/statistics/display';
            
            if ($this->isRefreshRequested()) {
                CLI::write('🔄 Pobieranie danych z API...', 'blue');
            }
            
            $client = \Config\Services::curlrequest();
            $response = $client->get($apiUrl, [
                'timeout' => 10,
                'http_errors' => false
            ]);
            
            if ($response->getStatusCode() !== 200) {
                CLI::write('❌ Błąd połączenia z API: ' . $response->getStatusCode(), 'red');
                return $this->getFallbackData();
            }
            
            $responseData = json_decode($response->getBody(), true);
            
            if (!$responseData || !$responseData['success']) {
                CLI::write('❌ Błąd odpowiedzi API', 'red');
                return $this->getFallbackData();
            }
            
            return $this->transformApiData($responseData['data']);
            
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
     * Transformuje dane z API do formatu używanego przez komendę
     * 
     * @param array $apiData
     * @return array
     */
    private function transformApiData(array $apiData): array
    {
        $transformed = [];
        
        foreach ($apiData['coasters'] as $coaster) {
            // Wyciągnij nazwę z formatu "[Kolejka XYZ]"
            $name = trim($coaster['name'], '[]');
            
            // Znajdź status z details
            $statusDetail = '';
            $personnelInfo = '';
            $wagonCount = 0;
            
            foreach ($coaster['details'] as $detail) {
                if (str_contains($detail, '5. Status:')) {
                    $statusDetail = str_replace('5. Status: ', '', $detail);
                } elseif (str_contains($detail, '5. Problem:')) {
                    $statusDetail = str_replace('5. Problem: ', '', $detail);
                } elseif (str_contains($detail, '3. Dostępny personel:')) {
                    $personnelInfo = str_replace('3. Dostępny personel: ', '', $detail);
                } elseif (str_contains($detail, '2. Liczba wagonów:')) {
                    $wagonCount = (int) str_replace('2. Liczba wagonów: ', '', $detail);
                }
            }
            
            // Określ status na podstawie problemów
            $status = str_contains($statusDetail, 'OK') ? 'Aktywna' : 'Problem';
            
            $transformed[] = [
                'name' => $name,
                'status' => $status,
                'personnel' => $personnelInfo,
                'wagons' => $wagonCount,
                'problems' => $status === 'Problem' ? $statusDetail : 'Brak problemów'
            ];
        }
        
        // Dodaj podsumowanie systemu
        $transformed['summary'] = [
            'timestamp' => $apiData['header'],
            'date' => $apiData['date'],
            'total_coasters' => $apiData['summary']['total_coasters'],
            'total_wagons' => $apiData['summary']['total_wagons'],
            'total_personnel' => $apiData['summary']['total_personnel'],
            'system_status' => $apiData['summary']['system_status'],
            'problematic_coasters' => $apiData['summary']['problematic_coasters']
        ];
        
        return $transformed;
    }
    
    /**
     * Zwraca dane awaryjne gdy API jest niedostępne
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
                'problems' => 'Brak połączenia z API'
            ],
            'summary' => [
                'timestamp' => '[' . date('H:i') . ']',
                'date' => date('Y-m-d'),
                'total_coasters' => 0,
                'total_wagons' => 0,
                'total_personnel' => 'N/A',
                'system_status' => 'BŁĄD POŁĄCZENIA',
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
        CLI::write(str_repeat('=', 120), 'yellow');
        CLI::write(sprintf(
            '| %-20s | %-12s | %-15s | %-8s | %-50s |',
            'Nazwa Kolejki',
            'Status',
            'Personel',
            'Wagony',
            'Problemy'
        ), 'cyan');
        CLI::write(str_repeat('=', 120), 'yellow');

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
                '| %-20s | %-20s | %-15s | %-8s | %-50s |',
                $coaster['name'] ?? 'N/A',
                $status,
                $coaster['personnel'] ?? 'N/A',
                $coaster['wagons'] ?? 0,
                $problems
            ));
        }
        
        CLI::write(str_repeat('=', 120), 'yellow');
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
