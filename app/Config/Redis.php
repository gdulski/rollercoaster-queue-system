<?php

namespace Config;

class Redis
{
    /**
     * Redis configuration
     */
    public $host;
    public $port;
    public $password;
    public $database;
    public $timeout = 0;
    public $persistent = false;

    public function __construct()
    {
        $this->host = $_ENV['redis.host'] ?? 'redis';
        $this->port = (int) ($_ENV['redis.port'] ?? 6379);
        $this->password = $_ENV['redis.password'] ?? null;
        $this->database = (int) ($_ENV['redis.database'] ?? 0);
    }
}




