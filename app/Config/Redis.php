<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    /**
     * Redis configuration
     */
    public $host = 'redis';
    public $port = 6379;
    public $password = null;
    public $database = 0;
    public $timeout = 0;
    public $persistent = false;
}




