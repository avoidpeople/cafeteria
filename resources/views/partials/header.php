<?php
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<nav class="navbar navbar-expand-lg theme-navbar mb-3 shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Doctor Gorilka</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link" href="/">Главная</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/menu">Меню</a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="/cart">
                Корзина
                <?php if ($cartCount > 0): ?>
                    <span class="badge bg-warning text-dark ms-2"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/orders">Мои заказы</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/profile">Профиль</a>
        </li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin/orders">Админ: Заказы</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/menu">Админ: Меню</a>
            </li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-3">
        <?php
        if (isset($_SESSION['username'])) {
            $fullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
            echo "Привет, " . htmlspecialchars($fullName ?: $_SESSION['username']);
        } else {
            echo "Гость";
        }
        ?>
      </span>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <button class="btn btn-outline-warning me-2 position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#pendingDrawer" aria-controls="pendingDrawer" title="Новые заказы">
        🧾
        <span class="badge bg-danger rounded-pill notif-badge d-none" id="pendingBadge">0</span>
      </button>
      <?php endif; ?>
      <button class="btn btn-outline-secondary me-2 position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationDrawer" aria-controls="notificationDrawer" title="Уведомления">
        🔔
        <span class="badge bg-danger rounded-pill notif-badge d-none" id="notificationsBadge">0</span>
      </button>
      <button class="btn btn-outline-secondary me-2" id="themeToggle" type="button" title="Переключить тему">🌓</button>
      <?php if (isset($_SESSION['username'])): ?>
        <a class="btn btn-outline-danger" href="/logout">Выход</a>
      <?php else: ?>
        <a class="btn btn-outline-primary me-2" href="/login">Войти</a>
        <a class="btn btn-primary" href="/register">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-end notifications-drawer" tabindex="-1" id="notificationDrawer" aria-labelledby="notificationDrawerLabel">
  <div class="offcanvas-header justify-content-between align-items-center">
    <div>
        <h5 id="notificationDrawerLabel" class="mb-1">Уведомления</h5>
        <small class="text-muted">Статусы заказов в реальном времени</small>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-sm btn-outline-secondary" id="clearNotificationsBtn" type="button">Очистить</button>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
    </div>
  </div>
  <div class="offcanvas-body">
    <div id="notificationsContainer" class="notifications-list text-muted small" data-locked="false">
        Уведомлений пока нет
    </div>
  </div>
</div>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<div class="offcanvas offcanvas-end pending-drawer" tabindex="-1" id="pendingDrawer" aria-labelledby="pendingDrawerLabel">
  <div class="offcanvas-header justify-content-between align-items-center">
    <div>
        <h5 id="pendingDrawerLabel" class="mb-1">Новые заказы</h5>
        <small class="text-muted">Подтвердите или отклоните заявки</small>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
  </div>
  <div class="offcanvas-body">
    <div id="pendingOrdersContainer" class="pending-list text-muted small">
        Новых заказов нет
    </div>
  </div>
</div>
<?php endif; ?>
