<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$currentLocale = currentLocale();
$locales = availableLocales();
$localeKeys = array_keys($locales);

$nextLocale = $currentLocale ?: 'ru';
if (!empty($localeKeys)) {
    $currentIndex = array_search($currentLocale, $localeKeys, true);
    $nextLocale = $localeKeys[($currentIndex === false ? 0 : $currentIndex + 1) % count($localeKeys)];
}

$localeFlags = [
    'ru' => '🇷🇺',
    'lv' => '🇱🇻',
];
$nextLocaleFlag = $localeFlags[$nextLocale] ?? '🌐';
$nextLocaleShort = $locales[$nextLocale]['short'] ?? (is_string($nextLocale) && $nextLocale !== '' ? strtoupper($nextLocale) : 'LANG');
?>
<nav class="navbar navbar-expand-lg theme-navbar shadow-sm site-header">
  <div class="container-fluid header-shell">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/">
        <span class="brand-mark" aria-hidden="true"></span>
        <span class="brand-text">Doctor Gorilka</span>
    </a>
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse header-content" id="mainNav">
      <ul class="navbar-nav header-nav mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item">
            <a class="nav-link <?= $currentPath === '/' ? 'active' : '' ?>" href="/"><?= htmlspecialchars(translate('nav.home')) ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, '/menu') === 0 ? 'active' : '' ?>" href="/menu"><?= htmlspecialchars(translate('nav.menu')) ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, '/orders') === 0 ? 'active' : '' ?>" href="/orders"><?= htmlspecialchars(translate('nav.orders')) ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, '/profile') === 0 ? 'active' : '' ?>" href="/profile"><?= htmlspecialchars(translate('nav.profile')) ?></a>
        </li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPath, '/admin/orders') === 0 ? 'active' : '' ?>" href="/admin/orders"><?= htmlspecialchars(translate('nav.admin_orders')) ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPath, '/admin/menu') === 0 ? 'active' : '' ?>" href="/admin/menu"><?= htmlspecialchars(translate('nav.admin_menu')) ?></a>
            </li>
        <?php endif; ?>
      </ul>
      <div class="header-actions d-flex align-items-center flex-wrap mt-3 mt-lg-0 justify-content-lg-end">
        <span class="navbar-text text-body-secondary small flex-shrink-0 header-greeting">
          <?php
          if (isset($_SESSION['username'])) {
              $fullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
              echo htmlspecialchars(translate('nav.greeting', ['name' => $fullName ?: $_SESSION['username']]));
          } else {
              echo htmlspecialchars(translate('nav.guest'));
          }
          ?>
        </span>
        <a class="action-btn icon-btn cart-action-btn" href="/cart" title="<?= htmlspecialchars(translate('nav.cart')) ?>">
          <span aria-hidden="true">🛒</span>
          <span class="cart-action-label"><?= htmlspecialchars(translate('nav.cart')) ?></span>
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <button class="action-btn icon-btn position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#pendingDrawer" aria-controls="pendingDrawer" title="<?= htmlspecialchars(translate('nav.pending_title')) ?>">
          <span aria-hidden="true">🛎</span>
          <span class="visually-hidden"><?= htmlspecialchars(translate('nav.pending_title')) ?></span>
          <span class="badge bg-danger rounded-pill notif-badge d-none" id="pendingBadge">0</span>
        </button>
        <?php endif; ?>
        <button class="action-btn icon-btn position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationDrawer" aria-controls="notificationDrawer" title="<?= htmlspecialchars(translate('nav.notifications')) ?>">
          <span aria-hidden="true">🔔</span>
          <span class="visually-hidden"><?= htmlspecialchars(translate('nav.notifications')) ?></span>
          <span class="badge bg-danger rounded-pill notif-badge d-none" id="notificationsBadge">0</span>
        </button>
        <form action="/language/switch" method="post" class="language-switcher d-flex align-items-center gap-2" aria-label="<?= htmlspecialchars(translate('nav.language_label')) ?>">
          <input type="hidden" name="lang" value="<?= htmlspecialchars($nextLocale) ?>">
          <button type="submit" class="action-btn language-toggle" aria-label="<?= htmlspecialchars(translate('nav.language_label')) ?> (<?= htmlspecialchars($nextLocaleShort) ?>)">
              <span class="language-flag" aria-hidden="true"><?= htmlspecialchars($nextLocaleFlag) ?></span>
              <span class="language-code"><?= htmlspecialchars($nextLocaleShort) ?></span>
              <span class="visually-hidden"><?= htmlspecialchars(translate('nav.language_label')) ?> <?= htmlspecialchars($nextLocaleShort) ?></span>
          </button>
        </form>
        <button class="action-btn icon-btn" id="themeToggle" type="button" title="<?= htmlspecialchars(translate('nav.theme_toggle')) ?>">
          <span aria-hidden="true">🌓</span>
          <span class="visually-hidden"><?= htmlspecialchars(translate('nav.theme_toggle')) ?></span>
        </button>
        <?php if (isset($_SESSION['username'])): ?>
          <a class="action-btn action-btn-danger" href="/logout"><?= htmlspecialchars(translate('nav.logout')) ?></a>
        <?php else: ?>
          <a class="action-btn action-btn-primary" href="/login"><?= htmlspecialchars(translate('nav.login')) ?></a>
          <a class="action-btn action-btn-primary" href="/register"><?= htmlspecialchars(translate('nav.register')) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-end notifications-drawer" tabindex="-1" id="notificationDrawer" aria-labelledby="notificationDrawerLabel">
  <div class="offcanvas-header justify-content-between align-items-center">
    <div>
        <h5 id="notificationDrawerLabel" class="mb-1"><?= htmlspecialchars(translate('nav.notifications')) ?></h5>
        <small class="text-muted"><?= htmlspecialchars(translate('nav.notifications_subtitle')) ?></small>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-sm btn-outline-secondary" id="clearNotificationsBtn" type="button"><?= htmlspecialchars(translate('nav.notifications_clear')) ?></button>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
  </div>
  <div class="offcanvas-body">
    <div
        id="notificationsContainer"
        class="notifications-list text-muted small"
        data-locked="false"
        data-empty-text="<?= htmlspecialchars(translate('nav.notifications_empty')) ?>"
        data-login-text="<?= htmlspecialchars(translate('nav.notifications_login')) ?>"
        data-none-text="<?= htmlspecialchars(translate('nav.notifications_none')) ?>"
        data-cleared-text="<?= htmlspecialchars(translate('nav.notifications_empty')) ?>"
        data-amount-label="<?= htmlspecialchars(translate('notifications.amount_label')) ?>"
    >
        <?= htmlspecialchars(translate('nav.notifications_empty')) ?>
    </div>
  </div>
</div>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<div class="offcanvas offcanvas-end pending-drawer" tabindex="-1" id="pendingDrawer" aria-labelledby="pendingDrawerLabel">
  <div class="offcanvas-header justify-content-between align-items-center">
    <div>
        <h5 id="pendingDrawerLabel" class="mb-1"><?= htmlspecialchars(translate('nav.pending_title')) ?></h5>
        <small class="text-muted"><?= htmlspecialchars(translate('nav.pending_subtitle')) ?></small>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div
        id="pendingOrdersContainer"
        class="pending-list text-muted small"
        data-empty-text="<?= htmlspecialchars(translate('admin.pending.empty')) ?>"
        data-order-label="<?= htmlspecialchars(translate('admin.pending.order_title')) ?>"
        data-customer-label="<?= htmlspecialchars(translate('admin.pending.customer')) ?>"
        data-customer-placeholder="<?= htmlspecialchars(translate('admin.pending.customer_placeholder')) ?>"
        data-address-label="<?= htmlspecialchars(translate('admin.pending.address')) ?>"
        data-address-placeholder="<?= htmlspecialchars(translate('admin.pending.address_placeholder')) ?>"
        data-items-label="<?= htmlspecialchars(translate('admin.pending.items_label')) ?>"
        data-items-placeholder="<?= htmlspecialchars(translate('admin.pending.items_placeholder')) ?>"
        data-sum-label="<?= htmlspecialchars(translate('admin.pending.sum')) ?>"
        data-comment-label="<?= htmlspecialchars(translate('admin.pending.comment_label')) ?>"
        data-comment-placeholder="<?= htmlspecialchars(translate('admin.pending.comment_placeholder')) ?>"
        data-accept-label="<?= htmlspecialchars(translate('admin.pending.accept')) ?>"
        data-decline-label="<?= htmlspecialchars(translate('admin.pending.decline')) ?>"
        data-confirm-decline="<?= htmlspecialchars(translate('admin.pending.confirm_decline')) ?>"
    >
        <?= htmlspecialchars(translate('admin.pending.empty')) ?>
    </div>
  </div>
</div>
<?php endif; ?>
