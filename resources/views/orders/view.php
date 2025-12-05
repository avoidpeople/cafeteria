<?php $title = 'Заказ #' . $orderId; ?>
<div class="page-container">
<?php $backUrl = $isAdmin ? '/admin/orders' : '/orders'; ?>
<div class="actions mt-20 mb-15">
    <a class="btn btn-secondary" href="<?= $backUrl ?>">← Вернуться к заказам</a>
    <?php if (!$isAdmin): ?>
        <a class="btn btn-primary" href="/orders/reorder?id=<?= $orderId ?>">Повторить заказ</a>
    <?php endif; ?>
    <?php if (!$isAdmin && ($order->status === 'new' || $order->status === 'cooking')): ?>
        <a class="btn btn-danger" href="/orders/view?id=<?= $orderId ?>&cancel=1" onclick="return confirm('Отменить заказ?');">Отменить заказ</a>
    <?php endif; ?>
</div>

<div class="card-panel summary-card">
    <h2>Заказ №<?= $orderId ?></h2>
    <p><b>Дата:</b> <?= $order->createdAt ?></p>
    <p><b>Статус:</b>
        <span class="status status-<?= htmlspecialchars($order->status) ?>"><?= htmlspecialchars($order->status) ?></span>
    </p>
    <p><b>Сумма:</b> <?= number_format($order->totalPrice, 2, '.', ' ') ?> €</p>
    <p><b>Адрес доставки:</b> <?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></p>
    <?php if ($isAdmin): ?>
        <p><b>Клиент:</b> <?= htmlspecialchars($order->customerName ?? 'Неизвестно') ?></p>
        <p><b>Телефон:</b> <?= htmlspecialchars($order->customerPhone ?? 'Не указан') ?></p>
    <?php endif; ?>
</div>

<h3>Блюда в заказе</h3>
<table class="table">
    <tr>
        <th>Фото</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Кол-во</th>
        <th>Сумма</th>
    </tr>
    <?php foreach ($order->items as $item): ?>
        <tr>
            <td>
                <?php if (!empty($item->imageUrl)): ?>
                    <img class="thumb" src="/assets/images/<?= htmlspecialchars($item->imageUrl) ?>" alt="">
                <?php else: ?>
                    <img class="thumb" src="https://via.placeholder.com/80" alt="Нет фото">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item->title) ?></td>
            <td><?= number_format($item->price, 2, '.', ' ') ?> €</td>
            <td><?= $item->quantity ?></td>
            <td><?= number_format($item->sum(), 2, '.', ' ') ?> €</td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
