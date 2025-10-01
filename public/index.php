<?php
declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/bootstrap.php';

use App\Core\Router;

$router = new Router();

// Web routes
$router->get('/', 'HomeController@index');
$router->get('/product/(\\d+)', 'ProductController@show');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/cart', 'CartController@view');
$router->post('/checkout', 'CheckoutController@checkout');
$router->post('/payment/webhook/(paymob|fawry|kashier|paypal)', 'PaymentWebhookController@handle');

// Admin routes (basic, to be improved later)
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@saveSettings');
$router->get('/admin/devices', 'AdminController@devices');
$router->post('/admin/devices/toggle', 'AdminController@toggleDeviceDeposit');
$router->get('/admin/zones', 'AdminController@zones');
$router->post('/admin/zones/save', 'AdminController@zoneSave');
$router->post('/admin/zones/delete', 'AdminController@zoneDelete');

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

ob_end_flush();
?>
