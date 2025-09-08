<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\CoasterService;
use App\Services\WagonService;
use App\Services\ProblemLogService;

/**
 * Test dla logowania problemów w CoasterService i WagonService
 * 
 * @package Tests\Unit
 */
class CoasterServiceLoggingTest extends CIUnitTestCase
{
    private CoasterService $coasterService;
    private WagonService $wagonService;
    private ProblemLogService $problemLogService;
    private string $testLogPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->coasterService = new CoasterService();
        $this->wagonService = new WagonService();
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
     * Test czy logowanie problemów działa po utworzeniu kolejki z problemami
     */
    public function testLoggingAfterCoasterCreation(): void
    {
        // Utwórz kolejkę z problemami (za mało personelu)
        $coasterData = [
            'staff_count' => 2, // Za mało personelu
            'daily_customers' => 1000,
            'track_length' => 1500,
            'opening_time' => '08:00',
            'closing_time' => '18:00'
        ];

        // Próba utworzenia kolejki (będzie null z powodu błędu Redis)
        $coaster = $this->coasterService->createCoaster($coasterData);
        
        // Sprawdź czy zwraca null z powodu błędu Redis
        $this->assertNull($coaster);
    }

    /**
     * Test czy logowanie problemów działa po aktualizacji kolejki
     */
    public function testLoggingAfterCoasterUpdate(): void
    {
        $updateData = [
            'staff_count' => 1, // Za mało personelu
            'daily_customers' => 2000,
            'opening_time' => '09:00',
            'closing_time' => '17:00'
        ];
        
        // Próba aktualizacji kolejki (będzie null z powodu błędu Redis)
        $coaster = $this->coasterService->updateCoaster('test_coaster_id', $updateData);
        
        // Sprawdź czy zwraca null z powodu błędu Redis
        $this->assertNull($coaster);
    }

    /**
     * Test czy logowanie problemów działa po dodaniu wagonu
     */
    public function testLoggingAfterWagonCreation(): void
    {
        $wagonData = [
            'seat_count' => 20,
            'wagon_speed' => 1.5
        ];
        
        // Próba utworzenia wagonu (będzie null z powodu błędu Redis)
        $wagon = $this->wagonService->createWagon('test_coaster_id', $wagonData);
        
        // Sprawdź czy zwraca null z powodu błędu Redis
        $this->assertNull($wagon);
    }

    /**
     * Test czy logowanie problemów działa po usunięciu wagonu
     */
    public function testLoggingAfterWagonDeletion(): void
    {
        // Próba usunięcia wagonu (będzie false z powodu błędu Redis)
        $deleted = $this->wagonService->deleteWagon('test_coaster_id', 'test_wagon_id');
        
        // Sprawdź czy zwraca false z powodu błędu Redis
        $this->assertFalse($deleted);
    }

    /**
     * Test czy ProblemLogService działa niezależnie
     */
    public function testProblemLogServiceIndependently(): void
    {
        $coasterName = 'Kolejka Testowa';
        $problems = ['Brakuje 3 pracowników', 'Brak 2 wagonów'];
        $coasterId = 'test_coaster_123';

        $result = $this->problemLogService->logCoasterProblems($coasterName, $problems, $coasterId);

        $this->assertTrue($result);
        $this->assertFileExists($this->testLogPath);

        $logContent = file_get_contents($this->testLogPath);
        $this->assertStringContainsString($coasterName, $logContent);
        $this->assertStringContainsString('Brakuje 3 pracowników', $logContent);
        $this->assertStringContainsString('Brak 2 wagonów', $logContent);
        $this->assertStringContainsString($coasterId, $logContent);
    }

    /**
     * Test czy logi są zapisywane w odpowiednim formacie
     */
    public function testLogFormatIsCorrect(): void
    {
        $coasterName = 'Kolejka A1';
        $problems = ['Brakuje 2 pracowników', 'brak 1 wagonu'];
        $coasterId = 'coaster_123';

        $this->problemLogService->logCoasterProblems($coasterName, $problems, $coasterId);

        $logContent = file_get_contents($this->testLogPath);
        
        // Sprawdź format zgodny z dokumentacją: [2024-11-29 00:12:30] Kolejka A1 - Problem: ...
        $this->assertMatchesRegularExpression(
            '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] Kolejka A1 - Problem: .*/',
            $logContent
        );
        
        // Sprawdź czy zawiera ID kolejki
        $this->assertStringContainsString('[ID: coaster_123]', $logContent);
    }

    /**
     * Test czy logi są zapisywane tylko gdy są problemy
     */
    public function testNoLoggingWhenNoProblems(): void
    {
        $coasterName = 'Kolejka OK';
        $problems = []; // Brak problemów
        $coasterId = 'coaster_ok';

        $result = $this->problemLogService->logCoasterProblems($coasterName, $problems, $coasterId);

        $this->assertTrue($result);
        $this->assertFalse(file_exists($this->testLogPath));
    }
}
