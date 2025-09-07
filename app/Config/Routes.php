<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', function($routes) {
    // Health check endpoints
    $routes->get('health', 'Api\Health::index');
    $routes->get('health/redis', 'Api\Health::redis');
    
    // Coasters endpoints
    $routes->group('coasters', function($routes) {
        $routes->post('/', 'Api\Coasters::create');           // POST /api/coasters
        $routes->get('/', 'Api\Coasters::index');             // GET /api/coasters
        $routes->get('/(:segment)', 'Api\Coasters::show');    // GET /api/coasters/{id}
        $routes->put('/(:segment)', 'Api\Coasters::update');  // PUT /api/coasters/{id}
    });
});
