<?php $title = 'Doctor Gorilka — Заказ оформлен'; ?>
<div class="page-container">
    <div class="card-panel summary-card">
        <h2>Спасибо за заказ!</h2>
        <p>Ваш заказ № <strong><?= $orderId ?></strong> успешно оформлен.</p>
        <p><strong>Сумма:</strong> <?= number_format($totalPrice, 2, '.', ' ') ?> €</p>
        <p><strong>Адрес доставки:</strong> <?= nl2br(htmlspecialchars($orderAddress)) ?></p>
        <div class="actions">
            <a href="/orders" class="btn btn-primary">Мои заказы</a>
            <a href="/menu" class="btn btn-outline-secondary">Вернуться к меню</a>
        </div>
    </div>

    <h3 class="mt-4">Состав заказа</h3>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>Блюдо</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
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
