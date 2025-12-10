<?php $title = 'Doctor Gorilka — ' . translate('orders.history.title'); ?>
<div class="page-container">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0"><?= htmlspecialchars(translate('orders.history.title')) ?></h1>
    <a class="btn btn-secondary" href="/menu"><?= htmlspecialchars(translate('common.back_to_menu')) ?></a>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state"><?= htmlspecialchars(translate('orders.history.empty')) ?></div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th><?= htmlspecialchars(translate('orders.history.number')) ?></th>
                    <th><?= htmlspecialchars(translate('orders.history.date')) ?></th>
                    <th><?= htmlspecialchars(translate('orders.history.status')) ?></th>
                    <th><?= htmlspecialchars(translate('orders.history.total')) ?></th>
                    <th><?= htmlspecialchars(translate('orders.history.address')) ?></th>
                    <th><?= htmlspecialchars(translate('orders.history.more')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($order->orderCode ?? $order->id) ?></td>
                        <td><?= $order->createdAt ?></td>
                        <td>
                            <span class="status status-<?= htmlspecialchars($order->status) ?>">
                                <?= htmlspecialchars(translateStatus($order->status)) ?>
                            </span>
                        </td>
                        <td><?= number_format($order->totalPrice, 2, '.', ' ') ?> €</td>
                        <td><?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></td>
                        <td><a class="btn btn-primary btn-sm" href="/orders/view?code=<?= urlencode($order->orderCode ?? '') ?>"><?= htmlspecialchars(translate('orders.history.open')) ?></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
