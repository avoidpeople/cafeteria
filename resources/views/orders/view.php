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
        <?php if ($item->isCombo()): ?>
            <?php $combo = $item->comboDetails; $comboItems = $combo['items'] ?? []; $soupExtraText = number_format(\App\Application\Service\ComboService::SOUP_EXTRA, 2, '.', ' '); ?>
            <tr class="combo-order-row">
                <td colspan="5">
                    <div class="combo-order-card">
                        <div class="combo-order-header">
                            <div>
                                <p class="text-uppercase text-muted small mb-1">Комплексный обед</p>
                                <h5 class="mb-0"><?= htmlspecialchars($combo['title'] ?? $item->title) ?></h5>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">
                                    <?= number_format($item->price, 2, '.', ' ') ?> € × <?= $item->quantity ?>
                                </div>
                                <div class="text-muted">Сумма: <?= number_format($item->sum(), 2, '.', ' ') ?> €</div>
                                <div class="badge bg-dark-subtle mt-2">
                                    <?= !empty($combo['has_soup']) ? 'Суп включен' : 'Без супа' ?>
                                </div>
                            </div>
                        </div>
                        <div class="combo-breakdown">
                            <?php foreach ($comboItems as $comboItem): ?>
                                <?php
                                $isUniqueItem = !empty($comboItem['is_unique']);
                                $itemRole = ($comboItem['type'] ?? '') === 'soup' ? 'Суп' : 'Основное';
                                $itemPrice = $isUniqueItem ? (float)($comboItem['price'] ?? 0) : null;
                                ?>
                                <div class="combo-breakdown-item">
                                    <div class="combo-breakdown-thumb">
                                        <?php if (!empty($comboItem['image'])): ?>
                                            <img src="/assets/images/<?= htmlspecialchars($comboItem['image']) ?>" alt="<?= htmlspecialchars($comboItem['title']) ?>">
                                        <?php else: ?>
                                            <span>Нет фото</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="combo-breakdown-title">
                                            <div>
                                                <span class="text-muted text-uppercase small me-2"><?= $itemRole ?></span>
                                                <?= htmlspecialchars($comboItem['title']) ?>
                                                <?php if ($isUniqueItem): ?>
                                                    <span class="combo-unique-chip ms-2">★ Уникальное</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($itemPrice): ?>
                                                <div class="combo-item-price"><?= number_format($itemPrice, 2, '.', ' ') ?> €</div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($comboItem['description'])): ?>
                                            <div class="text-muted small text-truncate-2"><?= htmlspecialchars($comboItem['description']) ?></div>
                                        <?php else: ?>
                                            <div class="text-muted small fst-italic">Описание недоступно</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty(array_filter($comboItems, static fn ($ci) => ($ci['type'] ?? '') === 'soup'))): ?>
                                <div class="combo-breakdown-item combo-breakdown-placeholder">
                                    <div class="combo-breakdown-thumb">—</div>
                                    <div>
                                        <div class="combo-breakdown-title">
                                            <span class="text-muted text-uppercase small me-2">Суп</span>
                                            <span>Без супа</span>
                                        </div>
                                        <div class="text-muted small">Добавление обычного супа увеличивает стоимость на <?= $soupExtraText ?> €.</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php continue; ?>
        <?php endif; ?>
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
