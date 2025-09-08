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
    $routes->get('coasters/(:segment)', 'Api\Coasters::show/$1');    // GET /api/coasters/{id}
    $routes->put('coasters/(:segment)', 'Api\Coasters::update');  // PUT /api/coasters/{id}
    $routes->post('coasters', 'Api\Coasters::create');           // POST /api/coasters
    $routes->get('coasters', 'Api\Coasters::index');             // GET /api/coasters
});
