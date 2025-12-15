<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Application\App;
use App\Application\Controller\HomeController;
use App\Application\Controller\MenuController;
use App\Application\Controller\AuthController;
use App\Application\Controller\OrderController;
use App\Application\Controller\CartController;
use App\Application\Controller\CartApiController;
use App\Application\Controller\ProfileController;
use App\Application\Controller\LanguageController;
use App\Application\Controller\NotificationController;
use App\Application\Controller\Admin\MenuController as AdminMenuController;
use App\Application\Controller\Admin\OrderController as AdminOrderController;
use App\Infrastructure\ViewRenderer;
use App\Infrastructure\Router;

$view = new ViewRenderer(__DIR__ . '/../resources/views', __DIR__ . '/../resources/views/layout.php');
$app = App::create($view, new Router());

$homeController = new HomeController($menuService, $view);
$menuController = new MenuController($menuService, $comboService, $view);
$authController = new AuthController($authService, $view, $sessionManager, $loginRateLimiter, $cartService);
$cartController = new CartController($cartService, $view, $sessionManager);
$orderController = new OrderController($authService, $orderService, $cartService, $menuRepository, $view, $sessionManager);
$profileController = new ProfileController($authService, $userRepository, $orderService, $view, $sessionManager);
$languageController = new LanguageController();
$cartApiController = new CartApiController($cartService, $comboService);
$adminMenuController = new AdminMenuController($authService, $adminMenuService, $view, $sessionManager);
$adminOrderController = new AdminOrderController($authService, $orderService, $view, $sessionManager);
$notificationController = new NotificationController($authService, $notificationService, $sessionManager);

$router = $app->router();
$router->get('/', [$homeController, 'index']);
$router->get('/menu', [$menuController, 'index']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/logout', [$authController, 'logout']);
$router->get('/cart', [$cartController, 'index']);
$router->post('/cart/add', [$cartController, 'add']);
$router->post('/cart/minus', [$cartController, 'minus']);
$router->post('/cart/remove', [$cartController, 'remove']);
$router->post('/cart/combo/remove', [$cartController, 'removeCombo']);
$router->post('/cart/clear', [$cartController, 'clear']);
$router->get('/orders', [$orderController, 'history']);
$router->get('/orders/view', [$orderController, 'view']);
$router->post('/orders/cancel', [$orderController, 'cancel']);
$router->post('/orders/place', [$orderController, 'place']);
$router->get('/profile', [$profileController, 'index']);
$router->post('/profile/password', [$profileController, 'updatePassword']);
$router->post('/api/cart/add', [$cartApiController, 'add']);
$router->post('/api/cart/combo', [$cartApiController, 'addCombo']);
$router->get('/api/notifications', [$notificationController, 'latest']);
$router->post('/api/notifications/clear', [$notificationController, 'clear']);
$router->get('/admin/menu', [$adminMenuController, 'index']);
$router->post('/admin/menu', [$adminMenuController, 'save']);
$router->post('/admin/menu/today', [$adminMenuController, 'today']);
$router->post('/admin/menu/delete', [$adminMenuController, 'delete']);
$router->get('/admin/orders', [$adminOrderController, 'index']);
$router->get('/admin/orders/show', [$adminOrderController, 'show']);
$router->post('/admin/orders/status', [$adminOrderController, 'updateStatus']);
$router->post('/admin/orders/bulk-status', [$adminOrderController, 'bulkStatus']);
$router->post('/admin/orders/delete', [$adminOrderController, 'delete']);
$router->get('/api/admin/pending-orders', [$adminOrderController, 'pendingFeed']);
$router->post('/api/admin/pending-orders/action', [$adminOrderController, 'handlePending']);
$router->post('/language/switch', [$languageController, 'switch']);

$response = $app->run();
if (is_string($response)) {
    echo $response;
}
