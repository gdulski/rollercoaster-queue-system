<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Log\Logger;

/**
 * Problem Log Service
 * 
 * Handles logging of coaster problems and notifications
 * Based on requirements from documentation - logs problems as notification simulation
 * 
 * @package App\Services
 */
class ProblemLogService
{
    private readonly Logger $logger;
    private readonly string $logPath;

    public function __construct()
    {
        $this->logger = service('logger');
        $this->logPath = WRITEPATH . 'logs/coaster_problems.log';
    }

    /**
     * Log detected problems for a coaster
     * 
     * @param string $coasterName
     * @param array $problems
     * @param string $coasterId
     * @return bool
     */
    public function logCoasterProblems(string $coasterName, array $problems, string $coasterId = ''): bool
    {
        if (empty($problems)) {
            return true; // No problems to log
        }

        try {
            $timestamp = date('Y-m-d H:i:s');
            $problemText = implode(', ', $problems);
            
            // Format log entry according to documentation requirements
            $logEntry = "[{$timestamp}] {$coasterName} - Problem: {$problemText}";
            
            // Add coaster ID if provided
            if (!empty($coasterId)) {
                $logEntry .= " [ID: {$coasterId}]";
            }

            // Write to custom log file
            $this->writeToCustomLog($logEntry);
            
            // Also log to standard CodeIgniter log
            $this->logger->warning("Coaster Problem Detected: {$coasterName} - {$problemText}", [
                'coaster_id' => $coasterId,
                'problems' => $problems,
                'timestamp' => $timestamp
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('ProblemLogService::logCoasterProblems failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log system-wide problems
     * 
     * @param array $systemProblems
     * @return bool
     */
    public function logSystemProblems(array $systemProblems): bool
    {
        if (empty($systemProblems)) {
            return true;
        }

        try {
            $timestamp = date('Y-m-d H:i:s');
            
            foreach ($systemProblems as $problem) {
                $logEntry = "[{$timestamp}] System - Problem: {$problem}";
                $this->writeToCustomLog($logEntry);
            }

            $this->logger->warning('System Problems Detected', [
                'problems' => $systemProblems,
                'timestamp' => $timestamp
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('ProblemLogService::logSystemProblems failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log personnel shortage problems
     * 
     * @param string $coasterName
     * @param int $shortage
     * @param string $coasterId
     * @return bool
     */
    public function logPersonnelShortage(string $coasterName, int $shortage, string $coasterId = ''): bool
    {
        $problems = ["Brakuje {$shortage} pracowników"];
        return $this->logCoasterProblems($coasterName, $problems, $coasterId);
    }

    /**
     * Log wagon shortage problems
     * 
     * @param string $coasterName
     * @param int $shortage
     * @param string $coasterId
     * @return bool
     */
    public function logWagonShortage(string $coasterName, int $shortage, string $coasterId = ''): bool
    {
        $problems = ["Brak {$shortage} wagonów"];
        return $this->logCoasterProblems($coasterName, $problems, $coasterId);
    }

    /**
     * Log capacity problems
     * 
     * @param string $coasterName
     * @param string $capacityIssue
     * @param string $coasterId
     * @return bool
     */
    public function logCapacityProblem(string $coasterName, string $capacityIssue, string $coasterId = ''): bool
    {
        $problems = [$capacityIssue];
        return $this->logCoasterProblems($coasterName, $problems, $coasterId);
    }

    /**
     * Write log entry to custom coaster problems log file
     * 
     * @param string $logEntry
     * @return bool
     */
    private function writeToCustomLog(string $logEntry): bool
    {
        try {
            // Ensure log directory exists
            $logDir = dirname($this->logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Append to log file
            $result = file_put_contents($this->logPath, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
            
            if ($result === false) {
                $this->logger->error("Failed to write to coaster problems log: {$this->logPath}");
                return false;
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error('ProblemLogService::writeToCustomLog failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent problems from log file
     * 
     * @param int $lines Number of recent lines to retrieve
     * @return array
     */
    public function getRecentProblems(int $lines = 50): array
    {
        try {
            if (!file_exists($this->logPath)) {
                return [];
            }

            $logContent = file_get_contents($this->logPath);
            if ($logContent === false) {
                return [];
            }

            $logLines = explode(PHP_EOL, trim($logContent));
            $logLines = array_filter($logLines); // Remove empty lines
            
            // Return last N lines
            return array_slice($logLines, -$lines);

        } catch (\Exception $e) {
            $this->logger->error('ProblemLogService::getRecentProblems failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear problem log file
     * 
     * @return bool
     */
    public function clearProblemLog(): bool
    {
        try {
            if (file_exists($this->logPath)) {
                return unlink($this->logPath);
            }
            return true;

        } catch (\Exception $e) {
            $this->logger->error('ProblemLogService::clearProblemLog failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get log file path
     * 
     * @return string
     */
    public function getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * Check if log file exists and is writable
     * 
     * @return bool
     */
    public function isLogWritable(): bool
    {
        $logDir = dirname($this->logPath);
        
        if (!is_dir($logDir)) {
            return mkdir($logDir, 0755, true);
        }

        if (file_exists($this->logPath)) {
            return is_writable($this->logPath);
        }

        return is_writable($logDir);
    }
}
