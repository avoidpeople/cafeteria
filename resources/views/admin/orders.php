<?php $title = 'Все заказы'; ?>
<div class="page-container">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">Все заказы</h1>
    <a class="btn btn-secondary" href="/">← На главную</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Всего заказов</div>
            <div class="stat-value"><?= $summary['total_orders'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Новых заказов</div>
            <div class="stat-value"><?= $summary['new_orders'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Ожидают подтверждения</div>
            <div class="stat-value"><?= $pendingCount ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Сумма заказов</div>
            <div class="stat-value"><?= number_format($summary['total_sum'] ?? 0, 2, '.', ' ') ?> €</div>
        </div>
    </div>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-sm-6 col-md-3">
        <select name="status" class="form-select">
            <option value="">Все статусы</option>
            <?php foreach (['pending','new','cooking','ready','delivered','cancelled'] as $status): ?>
                <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-6 col-md-4">
        <input type="text" class="form-control" placeholder="Поиск по пользователю или ID" name="user" value="<?= htmlspecialchars($userSearch) ?>">
    </div>
    <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary">Фильтровать</button>
        <a href="/admin/orders" class="btn btn-outline-secondary">Сбросить</a>
    </div>
</form>

<?php if (empty($orders)): ?>
    <div class="empty-state">Нет заказов по текущим фильтрам.</div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Дата</th>
                        <th>Доставка</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Изменить</th>
                        <th>Просмотр</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order->id ?></td>
                            <td>
                                <?= htmlspecialchars($order->customerName ?? 'Неизвестно') ?>
                                <div class="text-muted small">ID пользователя: <?= $order->userId ?></div>
                            </td>
                            <td>
                                <?= $order->createdAt ?>
                                <?php if (!empty($order->customerPhone)): ?>
                                    <div class="text-muted small">Тел: <?= htmlspecialchars($order->customerPhone) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></td>
                            <td><?= number_format($order->totalPrice, 2, '.', ' ') ?> €</td>
                            <td>
                                <span class="status status-<?= $order->status ?>">
                                    <?= $order->status ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order->status === 'cancelled'): ?>
                                    <div class="text-muted small mb-2">Заказ отменён. Статус изменить нельзя.</div>
                                <?php else: ?>
                                    <form method="POST" action="/admin/orders/status" class="d-flex flex-column gap-2 status-form">
                                        <input type="hidden" name="id" value="<?= $order->id ?>">
                                        <select name="status" class="form-select form-select-sm" required>
                                            <?php foreach (['new','cooking','ready','delivered','cancelled'] as $status): ?>
                                                <option value="<?= $status ?>" <?= $order->status === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Применить</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="/admin/orders/delete" class="mt-2" onsubmit="return confirm('Удалить заказ?');">
                                    <input type="hidden" name="id" value="<?= $order->id ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="/orders/view?id=<?= $order->id ?>">Открыть</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<?php if (!empty($orders)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.status-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const select = form.querySelector('select[name="status"]');
            if (select && select.value === 'cancelled') {
                const confirmed = confirm('Вы уверены, что хотите отменить заказ? Изменить статус обратно будет нельзя.');
                if (!confirmed) {
                    event.preventDefault();
                }
            }
        });
    });
});
</script>
<?php endif; ?>
