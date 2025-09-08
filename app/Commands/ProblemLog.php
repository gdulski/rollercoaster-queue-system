<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\ProblemLogService;

/**
 * Komenda CLI do wyświetlania i zarządzania logami problemów kolejek górskich
 * 
 * Użycie: php spark coaster:problems
 */
class ProblemLog extends BaseCommand
{
    /**
     * Problem Log Service
     */
    private readonly ProblemLogService $problemLogService;

    /**
     * Grupa komendy
     */
    protected $group = 'Rollercoaster';

    /**
     * Nazwa komendy
     */
    protected $name = 'coaster:problems';

    /**
     * Opis komendy
     */
    protected $description = 'Wyświetla i zarządza logami problemów kolejek górskich';

    /**
     * Użycie komendy
     */
    protected $usage = 'coaster:problems [options]';

    /**
     * Argumenty komendy
     */
    protected $arguments = [];

    /**
     * Opcje komendy
     */
    protected $options = [
        '--lines' => 'Liczba ostatnich linii do wyświetlenia (domyślnie: 20)',
        '--clear' => 'Czyści plik log problemów',
        '--path'  => 'Wyświetla ścieżkę do pliku log',
        '--watch' => 'Monitoruje plik log w czasie rzeczywistym'
    ];

    /**
     * Konstruktor - inicjalizuje ProblemLogService
     */
    public function __construct()
    {
        $this->problemLogService = new ProblemLogService();
    }

    /**
     * Główna metoda wykonująca komendę
     */
    public function run(array $params): void
    {
        CLI::write('🎢 System Kolejek Górskich - Log Problemów', 'green');
        CLI::write('==========================================', 'yellow');
        CLI::newLine();

        // Sprawdź opcje
        $lines = (int) (CLI::getOption('lines') ?? 20);
        $clear = CLI::getOption('clear') !== null;
        $showPath = CLI::getOption('path') !== null;
        $watch = CLI::getOption('watch') !== null;

        if ($showPath) {
            $this->showLogPath();
            return;
        }

        if ($clear) {
            $this->clearLog();
            return;
        }

        if ($watch) {
            $this->watchLog($lines);
            return;
        }

        // Wyświetl logi problemów
        $this->displayProblemLog($lines);
    }

    /**
     * Wyświetla ścieżkę do pliku log
     */
    private function showLogPath(): void
    {
        $logPath = $this->problemLogService->getLogPath();
        $isWritable = $this->problemLogService->isLogWritable();
        
        CLI::write("📁 Ścieżka do pliku log:", 'cyan');
        CLI::write("   {$logPath}", 'white');
        CLI::write("   Status: " . ($isWritable ? CLI::color('Zapisywalny', 'green') : CLI::color('Niezapisywalny', 'red')), 'white');
        CLI::newLine();
    }

    /**
     * Czyści plik log problemów
     */
    private function clearLog(): void
    {
        CLI::write('🗑️  Czyszczenie pliku log problemów...', 'yellow');
        
        if ($this->problemLogService->clearProblemLog()) {
            CLI::write('✅ Plik log został wyczyszczony pomyślnie', 'green');
        } else {
            CLI::write('❌ Błąd podczas czyszczenia pliku log', 'red');
        }
        
        CLI::newLine();
    }

    /**
     * Monitoruje plik log w czasie rzeczywistym
     * 
     * @param int $lines
     */
    private function watchLog(int $lines): void
    {
        CLI::write('👀 Monitorowanie pliku log w czasie rzeczywistym...', 'blue');
        CLI::write('💡 Naciśnij Ctrl+C aby zatrzymać', 'yellow');
        CLI::newLine();

        $lastSize = 0;
        $logPath = $this->problemLogService->getLogPath();

        while (true) {
            if (file_exists($logPath)) {
                $currentSize = filesize($logPath);
                
                if ($currentSize > $lastSize) {
                    // Wyczyść ekran i wyświetl ostatnie linie
                    CLI::write("\033[2J\033[H");
                    CLI::write('🎢 System Kolejek Górskich - Monitor Log Problemów', 'green');
                    CLI::write('================================================', 'yellow');
                    CLI::write('🕐 ' . date('Y-m-d H:i:s'), 'cyan');
                    CLI::newLine();
                    
                    $this->displayProblemLog($lines);
                    
                    $lastSize = $currentSize;
                }
            }
            
            sleep(2); // Sprawdzaj co 2 sekundy
        }
    }

    /**
     * Wyświetla log problemów
     * 
     * @param int $lines
     */
    private function displayProblemLog(int $lines): void
    {
        $problems = $this->problemLogService->getRecentProblems($lines);
        
        if (empty($problems)) {
            CLI::write('✅ Brak problemów w systemie', 'green');
            CLI::write('   Plik log jest pusty lub nie istnieje', 'white');
            CLI::newLine();
            return;
        }

        CLI::write("📋 Ostatnie {$lines} problemów w systemie:", 'cyan');
        CLI::write(str_repeat('=', 80), 'yellow');
        CLI::newLine();

        foreach ($problems as $index => $problem) {
            $lineNumber = count($problems) - $index;
            CLI::write(sprintf('%3d. %s', $lineNumber, $problem), 'white');
        }

        CLI::newLine();
        CLI::write(str_repeat('=', 80), 'yellow');
        CLI::write("📊 Łącznie wyświetlono: " . count($problems) . " problemów", 'cyan');
        CLI::newLine();
    }
}
