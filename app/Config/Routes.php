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
    $routes->put('coasters/(:segment)', 'Api\Coasters::update/$1');  // PUT /api/coasters/{id}
    $routes->post('coasters', 'Api\Coasters::create');           // POST /api/coasters
    $routes->get('coasters', 'Api\Coasters::index');             // GET /api/coasters
    
    // Wagons endpoints - nested under coasters
    $routes->post('coasters/(:segment)/wagons', 'Api\Wagons::createWagon/$1');           // POST /api/coasters/{coasterId}/wagons
    $routes->get('coasters/(:segment)/wagons', 'Api\Wagons::getWagons/$1');             // GET /api/coasters/{coasterId}/wagons
    $routes->get('coasters/(:segment)/wagons/(:segment)', 'Api\Wagons::getWagon/$1/$2'); // GET /api/coasters/{coasterId}/wagons/{wagonId}
    $routes->delete('coasters/(:segment)/wagons/(:segment)', 'Api\Wagons::deleteWagon/$1/$2'); // DELETE /api/coasters/{coasterId}/wagons/{wagonId}
    
    // Statistics endpoints
    $routes->get('statistics', 'Api\Statistics::index');                               // GET /api/statistics
    $routes->get('statistics/health', 'Api\Statistics::health');                       // GET /api/statistics/health
    $routes->get('statistics/display', 'Api\Statistics::display');                     // GET /api/statistics/display
    $routes->get('statistics/monitor', 'Api\Statistics::monitor');                     // GET /api/statistics/monitor
    $routes->get('statistics/coaster/(:segment)', 'Api\Statistics::coaster/$1');       // GET /api/statistics/coaster/{id}
});
