<?php
require __DIR__ . '/autoload.php';
require __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/helpers.php';

use App\Infrastructure\SessionManager;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Repository\MenuRepository;
use App\Infrastructure\Repository\OrderRepository;
use App\Application\Service\AuthService;
use App\Application\Service\MenuService;
use App\Application\Service\CartService;
use App\Application\Service\ComboService;
use App\Application\Service\OrderService;
use App\Application\Service\AdminMenuService;
use App\Application\Service\NotificationService;
use App\Infrastructure\Repository\NotificationRepository;

$sessionManager = new SessionManager();
$userRepository = new UserRepository($conn);
$authService = new AuthService($userRepository, $sessionManager);

$menuRepository = new MenuRepository($conn);
$menuService = new MenuService($menuRepository);
$adminMenuService = new AdminMenuService($menuRepository);
$comboService = new ComboService($menuRepository);
$cartService = new CartService($sessionManager, $menuRepository);
$orderRepository = new OrderRepository($conn);
$notificationRepository = new NotificationRepository($conn);
$notificationService = new NotificationService($notificationRepository);
$orderService = new OrderService($orderRepository, $notificationService);
