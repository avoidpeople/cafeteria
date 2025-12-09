<?php $title = 'Doctor Gorilka — Корзина'; ?>
<div class="page-container page-container-wide">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">Корзина</h1>
    <a class="btn btn-secondary" href="/menu">← Вернуться в меню</a>
</div>

<?php if (empty($cartItems)): ?>
    <div class="empty-state mt-20">Корзина пуста. Добавьте блюда из меню.</div>
<?php else: ?>
<form method="POST" action="/orders/place" id="cartForm">
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>В заказе</th>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody id="cartTableBody">
                    <?php foreach ($cartItems as $entry): ?>
                        <?php if (($entry['type'] ?? 'item') === 'combo'): ?>
                            <?php $comboEntry = $entry; include __DIR__ . '/../combos/_combo_block.php'; ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <?php $item = $entry['item']; $isAvailable = $item->isToday; ?>
                        <tr class="cart-row <?= $isAvailable ? 'row-selected' : 'row-unavailable' ?>" data-id="<?= $entry['id'] ?? $item->id ?>" data-sum="<?= $entry['sum'] ?>" data-qty="<?= $entry['quantity'] ?>" data-available="<?= $isAvailable ? '1' : '0' ?>">
                            <td>
                                <?php if ($isAvailable): ?>
                                    <label class="toggle-chip active w-100 justify-content-center">
                                        <span class="indicator"></span>
                                        <span class="chip-text">В заказе</span>
                                        <input type="checkbox" class="toggle-item" name="items[]" value="<?= $item->id ?>" checked hidden>
                                    </label>
                                <?php else: ?>
                                    <div class="toggle-chip disabled w-100 justify-content-center">
                                        <span class="indicator"></span>
                                        <span class="chip-text">Нет в меню сегодня</span>
                                    </div>
                                    <input type="checkbox" class="toggle-item" value="<?= $item->id ?>" hidden disabled>
                                    <small class="text-muted d-block mt-1">Можно только удалить позицию или дождаться возвращения в меню.</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item->primaryImage()): ?>
                                    <img class="thumb" src="/assets/images/<?= htmlspecialchars($item->primaryImage()) ?>" alt="">
                                <?php else: ?>
                                    <img class="thumb" src="https://via.placeholder.com/80" alt="no image">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item->title) ?></td>
                            <td><?= number_format($item->price, 2, '.', ' ') ?>€</td>
                            <td class="no-row-toggle">
                                <?php if ($isAvailable): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <a class="btn btn-outline-secondary btn-sm" href="/cart/minus?id=<?= $item->id ?>">−</a>
                                        <strong><?= $entry['quantity'] ?></strong>
                                        <a class="btn btn-outline-secondary btn-sm" href="/cart/add?id=<?= $item->id ?>">+</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted fw-semibold">× <?= $entry['quantity'] ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($entry['sum'], 2, '.', ' ') ?>€</td>
                            <td class="no-row-toggle">
                                <a class="btn btn-sm btn-outline-danger" href="/cart/remove?id=<?= $item->id ?>">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card-panel summary-card mt-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div class="flex-grow-1 w-100">
                <p class="text-muted mb-1">К оплате (выбрано)</p>
                <p class="fs-4 fw-bold mb-0" id="selectedTotal" data-cart-total="<?= $totalPrice ?>">0 €</p>
                <small class="text-muted">Позиции в заказе: <span id="selectedCount">0</span></small><br>
                <small class="text-muted">Всего в корзине: <?= number_format($totalPrice, 2, '.', ' ') ?> €</small>
            </div>
            <div class="flex-grow-1 w-100">
                <label class="form-label">Адрес доставки</label>
                <textarea class="form-control" name="delivery_address" rows="3" placeholder="Например: ул. Образцовая, 10" required><?= htmlspecialchars($deliveryDraft) ?></textarea>
                <small class="text-muted">Укажите адрес, по которому нужно доставить готовый заказ.</small>
            </div>
            <div class="d-flex flex-column gap-2 align-self-stretch">
                <a class="btn btn-outline-danger" href="/cart/clear" onclick="return confirm('Очистить корзину?');">Очистить корзину</a>
                <button type="submit" class="btn btn-success" id="submitOrder">Оформить выбранное</button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/cart_scripts.php'; ?>
