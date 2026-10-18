<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Tamu::index');
$routes->post('/tamu/submit', 'Tamu::submit');
$routes->get('/tamu/sukses', 'Tamu::sukses');

// Admin auth routes (public access)
$routes->get('/admin/login', 'Admin\\Auth::login');
$routes->post('/admin/login', 'Admin\\Auth::doLogin');
$routes->get('/admin/logout', 'Admin\\Auth::logout');

// CI4 default home route — kept for reference, overridden above.
$routes->get('/home', 'Home::index');
