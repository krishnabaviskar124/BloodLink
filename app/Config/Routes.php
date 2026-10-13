<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home Page & Public Smart Search
$routes->get('/', 'Home::index');
$routes->get('find-blood', 'BloodRequest::search');

// Authentication Routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::processLogin');
$routes->get('logout', 'Auth::logout');

$routes->get('register/donor', 'Auth::registerDonor');
$routes->post('register/donor', 'Auth::processRegisterDonor');

$routes->get('register/hospital', 'Auth::registerHospital');
$routes->post('register/hospital', 'Auth::processRegisterHospital');

// Protected Routes (Require Authentication)
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Notification Routes
    $routes->get('notifications', 'Notification::index');
    $routes->get('notifications/(:num)/read', 'Notification::markRead/$1');
    $routes->get('notifications/mark-all-read', 'Notification::markAllRead');

    // Donor Portal Routes (Donor & Admin)
    $routes->group('donor', ['filter' => 'role:Donor,Admin'], static function ($routes) {
        $routes->get('dashboard', 'Donor::dashboard');
        $routes->get('profile', 'Donor::profile');
        $routes->get('profile/edit', 'Donor::editProfile');
        $routes->post('profile/update', 'Donor::updateProfile');
        $routes->post('availability/update', 'Donor::updateAvailability');
        $routes->get('donations', 'Donor::donations');
    });

    // Hospital Portal Routes (Hospital & Admin)
    $routes->group('hospital', ['filter' => 'role:Hospital,Admin'], static function ($routes) {
        $routes->get('dashboard', 'Hospital::dashboard');
    });

    // Blood Bank Manager Portal Routes (Blood Bank Manager & Admin)
    $routes->group('bloodbank', ['filter' => 'role:Blood Bank Manager,Admin'], static function ($routes) {
        $routes->get('dashboard', 'BloodBank::dashboard');
    });

    // Admin Overview Dashboard (Admin only)
    $routes->group('admin', ['filter' => 'role:Admin'], static function ($routes) {
        $routes->get('dashboard', 'Admin::dashboard');
    });

    // Blood Request & Donor Contact Routes
    $routes->get('requests', 'BloodRequest::index');
    $routes->post('requests', 'BloodRequest::store');
    $routes->get('requests/create', 'BloodRequest::create');
    $routes->post('requests/store', 'BloodRequest::store');
    $routes->get('requests/(:num)', 'BloodRequest::show/$1');
    $routes->post('requests/(:num)/status', 'BloodRequest::updateStatus/$1');
    $routes->get('requests/(:num)/cancel', 'BloodRequest::cancel/$1');
    $routes->post('requests/contact-donor/(:num)', 'ContactRequest::store/$1');
    $routes->post('contact-requests', 'ContactRequest::store');
    $routes->post('contact-requests/(:num)/respond', 'ContactRequest::respond/$1');
    $routes->get('contact-requests/(:num)/cancel', 'ContactRequest::cancel/$1');

    // Inventory Routes
    $routes->get('inventory', 'Inventory::index');
    $routes->get('inventory/create', 'Inventory::create', ['filter' => 'role:Blood Bank Manager,Admin']);
    $routes->post('inventory/store', 'Inventory::store', ['filter' => 'role:Blood Bank Manager,Admin']);
    $routes->get('inventory/(:num)/edit', 'Inventory::edit/$1', ['filter' => 'role:Blood Bank Manager,Admin']);
    $routes->post('inventory/(:num)/update', 'Inventory::update/$1', ['filter' => 'role:Blood Bank Manager,Admin']);

    // Donation Records Routes
    $routes->get('donations', 'Donation::index');
    $routes->get('donations/create', 'Donation::create', ['filter' => 'role:Blood Bank Manager,Admin']);
    $routes->post('donations/store', 'Donation::store', ['filter' => 'role:Blood Bank Manager,Admin']);
    $routes->get('donations/(:num)', 'Donation::show/$1');

});
