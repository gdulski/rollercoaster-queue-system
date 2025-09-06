<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', function($routes) {
    $routes->get('health', 'Api\Health::index');
    $routes->get('health/redis', 'Api\Health::redis');
});
