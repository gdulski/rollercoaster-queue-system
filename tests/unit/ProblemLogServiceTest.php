<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\ProblemLogService;

/**
 * Test dla ProblemLogService
 * 
 * @package Tests\Unit
 */
class ProblemLogServiceTest extends CIUnitTestCase
{
    private ProblemLogService $problemLogService;
    private string $testLogPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->problemLogService = new ProblemLogService();
        $this->testLogPath = WRITEPATH . 'logs/coaster_problems.log';
        
        // Wyczyść plik log przed każdym testem
        if (file_exists($this->testLogPath)) {
            unlink($this->testLogPath);
        }
    }

    protected function tearDown(): void
    {
        // Wyczyść plik log po każdym teście
        if (file_exists($this->testLogPath)) {
            unlink($this->testLogPath);
        }
        
        parent::tearDown();
    }

    /**
     * Test logowania problemów kolejki
     */
    public function testLogCoasterProblems(): void
    {
        $coasterName = 'Kolejka A1';
        $problems = ['Brakuje 2 pracowników', 'Brak 1 wagonu'];
        $coasterId = 'coaster_123';

        $result = $this->problemLogService->logCoasterProblems($coasterName, $problems, $coasterId);

        $this->assertTrue($result);
        $this->assertFileExists($this->testLogPath);

        $logContent = file_get_contents($this->testLogPath);
        $this->assertStringContainsString($coasterName, $logContent);
        $this->assertStringContainsString('Brakuje 2 pracowników', $logContent);
        $this->assertStringContainsString('Brak 1 wagonu', $logContent);
        $this->assertStringContainsString($coasterId, $logContent);
    }

    /**
     * Test logowania problemów systemowych
     */
    public function testLogSystemProblems(): void
    {
        $systemProblems = [
            'Brak połączenia z Redis',
            'Błąd autoryzacji API'
        ];

        $result = $this->problemLogService->logSystemProblems($systemProblems);

        $this->assertTrue($result);
        $this->assertFileExists($this->testLogPath);

        $logContent = file_get_contents($this->testLogPath);
        $this->assertStringContainsString('System - Problem: Brak połączenia z Redis', $logContent);
        $this->assertStringContainsString('System - Problem: Błąd autoryzacji API', $logContent);
    }

    /**
     * Test logowania problemów z personelem
     */
    public function testLogPersonnelShortage(): void
    {
        $coasterName = 'Kolejka B2';
        $shortage = 3;
        $coasterId = 'coaster_456';

        $result = $this->problemLogService->logPersonnelShortage($coasterName, $shortage, $coasterId);

        $this->assertTrue($result);
        $this->assertFileExists($this->testLogPath);

        $logContent = file_get_contents($this->testLogPath);
        $this->assertStringContainsString($coasterName, $logContent);
        $this->assertStringContainsString('Brakuje 3 pracowników', $logContent);
    }

    /**
     * Test logowania problemów z wagonami
     */
    public function testLogWagonShortage(): void
    {
        $coasterName = 'Kolejka C3';
        $shortage = 2;
        $coasterId = 'coaster_789';

        $result = $this->problemLogService->logWagonShortage($coasterName, $shortage, $coasterId);

        $this->assertTrue($result);
        $this->assertFileExists($this->testLogPath);

        $logContent = file_get_contents($this->testLogPath);
        $this->assertStringContainsString($coasterName, $logContent);
        $this->assertStringContainsString('Brak 2 wagonów', $logContent);
    }

    /**
     * Test pobierania ostatnich problemów
     */
    public function testGetRecentProblems(): void
    {
        // Dodaj kilka problemów
        $this->problemLogService->logCoasterProblems('Kolejka A1', ['Problem 1'], 'id1');
        $this->problemLogService->logCoasterProblems('Kolejka A2', ['Problem 2'], 'id2');
        $this->problemLogService->logCoasterProblems('Kolejka A3', ['Problem 3'], 'id3');

        $recentProblems = $this->problemLogService->getRecentProblems(2);

        $this->assertCount(2, $recentProblems);
        // Ostatnie problemy powinny być na końcu tablicy
        $this->assertStringContainsString('Problem 3', $recentProblems[1]);
        $this->assertStringContainsString('Problem 2', $recentProblems[0]);
    }

    /**
     * Test czyszczenia pliku log
     */
    public function testClearProblemLog(): void
    {
        // Dodaj problem
        $this->problemLogService->logCoasterProblems('Kolejka A1', ['Problem testowy'], 'id1');
        $this->assertFileExists($this->testLogPath);

        // Wyczyść log
        $result = $this->problemLogService->clearProblemLog();
        $this->assertTrue($result);
        $this->assertFalse(file_exists($this->testLogPath));
    }

    /**
     * Test sprawdzania czy plik log jest zapisywalny
     */
    public function testIsLogWritable(): void
    {
        $isWritable = $this->problemLogService->isLogWritable();
        $this->assertTrue($isWritable);
    }

    /**
     * Test pobierania ścieżki do pliku log
     */
    public function testGetLogPath(): void
    {
        $logPath = $this->problemLogService->getLogPath();
        $this->assertStringEndsWith('coaster_problems.log', $logPath);
    }

    /**
     * Test logowania pustych problemów
     */
    public function testLogEmptyProblems(): void
    {
        $result = $this->problemLogService->logCoasterProblems('Kolejka A1', [], 'id1');
        $this->assertTrue($result);
        $this->assertFalse(file_exists($this->testLogPath));
    }

    /**
     * Test formatowania logów zgodnie z dokumentacją
     */
    public function testLogFormatMatchesDocumentation(): void
    {
        $coasterName = 'Kolejka A1';
        $problems = ['Brakuje 2 pracowników', 'brak 2 wagonów'];
        $coasterId = 'coaster_123';

        $this->problemLogService->logCoasterProblems($coasterName, $problems, $coasterId);

        $logContent = file_get_contents($this->testLogPath);
        
        // Sprawdź format zgodny z dokumentacją: [2024-11-29 00:12:30] Kolejka A1 - Problem: ...
        $this->assertMatchesRegularExpression(
            '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] Kolejka A1 - Problem: .*/',
            $logContent
        );
    }
}
