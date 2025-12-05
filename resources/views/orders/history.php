<?php $title = 'Мои заказы'; ?>
<div class="page-container">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">Мои заказы</h1>
    <a class="btn btn-secondary" href="/menu">← Вернуться в меню</a>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state">У вас ещё нет заказов. Как только оформите первый заказ, он появится здесь.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Номер</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Сумма</th>
                    <th>Адрес доставки</th>
                    <th>Подробнее</th>
                    <th>Повторить</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order->id ?></td>
                        <td><?= $order->createdAt ?></td>
                        <td>
                            <span class="status status-<?= htmlspecialchars($order->status) ?>">
                                <?= htmlspecialchars($order->status) ?>
                            </span>
                        </td>
                        <td><?= number_format($order->totalPrice, 2, '.', ' ') ?> €</td>
                        <td><?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></td>
                        <td><a class="btn btn-primary btn-sm" href="/orders/view?id=<?= $order->id ?>">Открыть</a></td>
                        <td><a class="btn btn-outline-primary btn-sm" href="/orders/reorder?id=<?= $order->id ?>">Повторить</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
