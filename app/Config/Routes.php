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

// Admin dashboard
$routes->get('/admin', 'Admin\\Dashboard::index');

// Admin guest management routes
$routes->get('admin/tamu', 'Admin\\TamuAdmin::index');
$routes->get('admin/tamu/export', 'Admin\\TamuAdmin::exportCsv');
$routes->get('admin/tamu/(:num)', 'Admin\\TamuAdmin::detail/$1');
$routes->post('admin/tamu/(:num)/baca', 'Admin\\TamuAdmin::tandaiBaca/$1');
$routes->post('admin/tamu/baca-semua', 'Admin\\TamuAdmin::bacaSemua');

// Admin user management routes (CRUD)
$routes->get('admin/admin-user', 'Admin\\AdminUser::index');
$routes->get('admin/admin-user/buat', 'Admin\\AdminUser::buat');
$routes->post('admin/admin-user/buat', 'Admin\\AdminUser::simpan');
$routes->get('admin/admin-user/(:num)/edit', 'Admin\\AdminUser::edit/$1');
$routes->post('admin/admin-user/(:num)/edit', 'Admin\\AdminUser::update/$1');
$routes->get('admin/admin-user/(:num)/hapus', 'Admin\\AdminUser::konfirmasi/$1');
$routes->post('admin/admin-user/(:num)/hapus', 'Admin\\AdminUser::hapus/$1');

// CI4 default home route — kept for reference, overridden above.
$routes->get('/home', 'Home::index');
