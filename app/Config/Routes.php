<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Tamu::index');
$routes->post('/tamu/submit', 'Tamu::submit');
$routes->get('/tamu/sukses', 'Tamu::sukses');

// CI4 default home route — kept for reference, overridden above.
$routes->get('/home', 'Home::index');
