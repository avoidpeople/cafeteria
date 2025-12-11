<?php $title = 'Doctor Gorilka — ' . translate('orders.placed.title'); ?>
<div class="page-container">
    <div class="card-panel summary-card">
        <h2><?= htmlspecialchars(translate('orders.placed.thank_you')) ?></h2>
        <p><?= htmlspecialchars(translate('orders.placed.success', ['id' => $orderCode ?? $orderId])) ?></p>
        <p><strong><?= htmlspecialchars(translate('orders.placed.total')) ?></strong> <?= number_format($totalPrice, 2, '.', ' ') ?> €</p>
        <p><strong><?= htmlspecialchars(translate('orders.placed.address')) ?></strong> <?= nl2br(htmlspecialchars($orderAddress)) ?></p>
        <?php if (!empty($comment)): ?>
            <p><strong><?= htmlspecialchars(translate('orders.view.comment')) ?></strong> <?= nl2br(htmlspecialchars($comment)) ?></p>
        <?php endif; ?>
        <div class="actions">
            <a href="/orders" class="btn btn-primary"><?= htmlspecialchars(translate('orders.placed.my_orders')) ?></a>
            <a href="/menu" class="btn btn-outline-secondary"><?= htmlspecialchars(translate('orders.placed.back_to_menu')) ?></a>
        </div>
    </div>

    <h3 class="mt-4"><?= htmlspecialchars(translate('orders.placed.items_title')) ?></h3>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th><?= htmlspecialchars(translate('orders.placed.table.dish')) ?></th>
                <th><?= htmlspecialchars(translate('orders.placed.table.price')) ?></th>
                <th><?= htmlspecialchars(translate('orders.placed.table.quantity')) ?></th>
                <th><?= htmlspecialchars(translate('orders.placed.table.sum')) ?></th>
            </tr>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= number_format($item['price'], 2, '.', ' ') ?> €</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['sum'], 2, '.', ' ') ?> €</td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
