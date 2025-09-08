<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\StatisticsService;
use React\EventLoop\Loop;
use React\Socket\Connector;
use Clue\React\Redis\Io\Factory as RedisFactory;
use Clue\React\Redis\RedisClient;
use Clue\React\Redis\Io\StreamingClient;

/**
 * Komenda CLI do monitorowania kolejek górskich w czasie rzeczywistym
 * 
 * Użycie: php spark coaster:monitor
 */
class RealtimeMonitor extends BaseCommand
{
    /**
     * Statistics Service
     */
    private readonly StatisticsService $statisticsService;

    /**
     * Redis Client
     */
    private ?StreamingClient $redisClient = null;

    /**
     * Event Loop
     */
    private $loop;

    /**
     * Grupa komendy
     */
    protected $group = 'Rollercoaster';

    /**
     * Nazwa komendy
     */
    protected $name = 'coaster:monitor';

    /**
     * Opis komendy
     */
    protected $description = 'Monitoruje kolejki górskie w czasie rzeczywistym z automatycznym odświeżaniem';

    /**
     * Użycie komendy
     */
    protected $usage = 'coaster:monitor [options]';

    /**
     * Argumenty komendy
     */
    protected $arguments = [];

    /**
     * Opcje komendy
     */
    protected $options = [
        '--interval' => 'Interwał odświeżania w sekundach (domyślnie: 5)',
        '--redis'    => 'Adres Redis (domyślnie: redis://redis:6379)',
        '--clear'    => 'Czyści ekran przed każdym odświeżeniem'
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
        CLI::write('🎢 System Kolejek Górskich - Monitor Czasu Rzeczywistego', 'green');
        CLI::write('====================================================', 'yellow');
        CLI::newLine();

        // Pobierz opcje
        $interval = (int) (CLI::getOption('interval') ?? 5);
        $redisUrl = CLI::getOption('redis') ?? 'redis://redis:6379';
        $clearScreen = CLI::getOption('clear') !== null;

        CLI::write("⚙️  Konfiguracja:", 'cyan');
        CLI::write("   • Interwał odświeżania: {$interval}s", 'white');
        CLI::write("   • Redis URL: {$redisUrl}", 'white');
        CLI::write("   • Czyszczenie ekranu: " . ($clearScreen ? 'Tak' : 'Nie'), 'white');
        CLI::newLine();

        // Inicjalizuj event loop
        $this->loop = Loop::get();

        // Uruchom monitor (Redis jest opcjonalny)
        $this->startMonitor($interval, $clearScreen, $redisUrl);

        CLI::write('🛑 Monitor zatrzymany', 'red');
    }

    /**
     * Łączy się z Redis
     * 
     * @param string $redisUrl
     */
    private function connectToRedis(string $redisUrl): void
    {
        try {
            CLI::write('🔗 Łączenie z Redis...', 'blue');
            
            $connector = new Connector($this->loop);
            $factory = new RedisFactory($connector);
            
            $factory->createClient($redisUrl)->then(
                function (StreamingClient $client) {
                    $this->redisClient = $client;
                    CLI::write('✅ Połączono z Redis', 'green');
                },
                function (\Exception $e) {
                    CLI::write('❌ Błąd połączenia z Redis: ' . $e->getMessage(), 'red');
                    CLI::write('🔄 Kontynuuję bez Redis...', 'yellow');
                    $this->redisClient = null;
                }
            );

        } catch (\Exception $e) {
            CLI::write('❌ Błąd inicjalizacji Redis: ' . $e->getMessage(), 'red');
            CLI::write('🔄 Kontynuuję bez Redis...', 'yellow');
            $this->redisClient = null;
        }
    }

    /**
     * Uruchamia monitor w czasie rzeczywistym
     * 
     * @param int $interval
     * @param bool $clearScreen
     * @param string $redisUrl
     */
    private function startMonitor(int $interval, bool $clearScreen, string $redisUrl): void
    {
        CLI::write('🚀 Uruchamianie monitora...', 'green');
        CLI::write('💡 Naciśnij Ctrl+C aby zatrzymać', 'yellow');
        CLI::newLine();

        // Spróbuj połączyć z Redis (opcjonalnie)
        $this->connectToRedis($redisUrl);

        // Obsługa sygnału przerwania
        $this->loop->addSignal(SIGINT, function () {
            CLI::newLine();
            CLI::write('🛑 Otrzymano sygnał przerwania...', 'yellow');
            $this->loop->stop();
        });

        // Timer do odświeżania danych
        $this->loop->addPeriodicTimer($interval, function () use ($clearScreen) {
            $this->refreshDisplay($clearScreen);
        });

        // Pierwsze wyświetlenie
        $this->refreshDisplay($clearScreen);

        // Uruchom event loop
        $this->loop->run();
    }

    /**
     * Odświeża wyświetlanie danych
     * 
     * @param bool $clearScreen
     */
    private function refreshDisplay(bool $clearScreen): void
    {
        try {
            if ($clearScreen) {
                // Wyczyść ekran (działa w większości terminali)
                CLI::write("\033[2J\033[H");
            }

            // Pobierz aktualne dane
            $statistics = $this->statisticsService->generateSystemStatistics();
            
            if (!$statistics) {
                CLI::write('❌ Błąd podczas pobierania danych', 'red');
                return;
            }

            // Wyświetl dane
            $this->displayRealtimeData($statistics);

            // Opcjonalnie opublikuj dane do Redis
            if ($this->redisClient) {
                $this->publishToRedis($statistics);
            }

        } catch (\Exception $e) {
            CLI::write('❌ Błąd podczas odświeżania: ' . $e->getMessage(), 'red');
        }
    }

    /**
     * Wyświetla dane w czasie rzeczywistym
     * 
     * @param array $statistics
     */
    private function displayRealtimeData(array $statistics): void
    {
        $timestamp = date('Y-m-d H:i:s');
        
        CLI::write("🕐 {$timestamp} - Status Systemu Kolejek Górskich", 'cyan');
        CLI::write(str_repeat('=', 80), 'yellow');
        CLI::newLine();

        // Wyświetl każdą kolejkę
        foreach ($statistics['coasters'] as $coaster) {
            $statusColor = $coaster['status'] === 'OK' ? 'green' : 'red';
            $statusIcon = $coaster['status'] === 'OK' ? '✅' : '⚠️';
            
            CLI::write("🎢 {$coaster['name']} {$statusIcon}", 'white');
            CLI::write("   • ID Redis: " . CLI::color($coaster['redis_id'], 'cyan'), 'white');
            CLI::write("   • Godziny: {$coaster['operating_hours']}", 'white');
            CLI::write("   • Wagony: {$coaster['wagon_count']}", 'white');
            CLI::write("   • Personel: {$coaster['available_personnel']}/{$coaster['required_personnel']}", 'white');
            CLI::write("   • Klienci dziennie: {$coaster['daily_customers']}", 'white');
            
            if (!empty($coaster['problems'])) {
                CLI::write("   • Problemy: " . implode(', ', $coaster['problems']), 'red');
            } else {
                CLI::write("   • Status: " . CLI::color('OK', 'green'), 'white');
            }
            
            CLI::newLine();
        }

        // Podsumowanie systemu
        $summary = $statistics['summary'];
        CLI::write("📊 Podsumowanie Systemu:", 'cyan');
        CLI::write("   • Łączna liczba kolejek: {$summary['total_coasters']}", 'white');
        CLI::write("   • Łączna liczba wagonów: {$summary['total_wagons']}", 'white');
        CLI::write("   • Łączny personel: {$summary['total_available_personnel']}/{$summary['total_required_personnel']}", 'white');
        CLI::write("   • Kolejki z problemami: {$summary['coasters_with_problems']}", 
            $summary['coasters_with_problems'] > 0 ? 'red' : 'green');
        CLI::write("   • Status systemu: " . CLI::color($summary['system_status'], 
            $summary['system_status'] === 'OK' ? 'green' : 'red'), 'white');
        
        CLI::newLine();
        CLI::write(str_repeat('=', 80), 'yellow');
        CLI::write("💡 Naciśnij Ctrl+C aby zatrzymać monitor", 'yellow');
        CLI::newLine();
    }

    /**
     * Publikuje dane do Redis (opcjonalnie)
     * 
     * @param array $statistics
     */
    private function publishToRedis(array $statistics): void
    {
        // Na razie wyłączone - StreamingClient ma ograniczone metody
        // W przyszłości można dodać pełny RedisClient z metodami publish/set
        return;
        
        if (!$this->redisClient) {
            return;
        }

        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'coasters' => $statistics['coasters'],
            'summary' => $statistics['summary']
        ];

        // TODO: Implementacja publikowania do Redis
        // Wymaga pełnego RedisClient zamiast StreamingClient
    }
}
