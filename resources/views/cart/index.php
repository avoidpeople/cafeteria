<?php $title = 'Doctor Gorilka — ' . translate('cart.title'); ?>
<div class="page-container page-container-wide">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0"><?= htmlspecialchars(translate('cart.title')) ?></h1>
    <a class="btn btn-secondary" href="/menu"><?= htmlspecialchars(translate('common.back_to_menu')) ?></a>
</div>

<?php if (empty($cartItems)): ?>
    <div class="empty-state mt-20"><?= htmlspecialchars(translate('cart.empty')) ?></div>
<?php else: ?>
<form method="POST" action="/orders/place" id="cartForm">
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th><?= htmlspecialchars(translate('cart.table.in_order')) ?></th>
                        <th><?= htmlspecialchars(translate('cart.table.photo')) ?></th>
                        <th><?= htmlspecialchars(translate('cart.table.name')) ?></th>
                        <th><?= htmlspecialchars(translate('cart.table.price')) ?></th>
                        <th><?= htmlspecialchars(translate('cart.table.quantity')) ?></th>
                        <th><?= htmlspecialchars(translate('cart.table.sum')) ?></th>
                        <th><?= htmlspecialchars(translate('cart.table.actions')) ?></th>
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
                                        <span class="chip-text"><?= htmlspecialchars(translate('cart.toggle.included')) ?></span>
                                        <input type="checkbox" class="toggle-item" name="items[]" value="<?= $item->id ?>" checked hidden>
                                    </label>
                                <?php else: ?>
                                    <div class="toggle-chip disabled w-100 justify-content-center">
                                        <span class="indicator"></span>
                                        <span class="chip-text"><?= htmlspecialchars(translate('cart.toggle.unavailable')) ?></span>
                                    </div>
                                    <input type="checkbox" class="toggle-item" value="<?= $item->id ?>" hidden disabled>
                                    <small class="text-muted d-block mt-1"><?= htmlspecialchars(translate('cart.toggle.missing_tip')) ?></small>
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
                                    <div class="text-muted fw-semibold"><?= htmlspecialchars(translate('cart.qty.times', ['qty' => $entry['quantity']])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($entry['sum'], 2, '.', ' ') ?>€</td>
                            <td class="no-row-toggle">
                                <a class="btn btn-sm btn-outline-danger" href="/cart/remove?id=<?= $item->id ?>"><?= htmlspecialchars(translate('cart.delete')) ?></a>
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
                <p class="text-muted mb-1"><?= htmlspecialchars(translate('cart.summary.pay_label')) ?></p>
                <p class="fs-4 fw-bold mb-0" id="selectedTotal" data-cart-total="<?= $totalPrice ?>">0 €</p>
                <small class="text-muted"><?= htmlspecialchars(translate('cart.summary.items_label')) ?> <span id="selectedCount">0</span></small><br>
                <small class="text-muted"><?= htmlspecialchars(translate('cart.summary.total_cart_label')) ?> <?= number_format($totalPrice, 2, '.', ' ') ?> €</small>
            </div>
            <div class="flex-grow-1 w-100">
                <label class="form-label"><?= htmlspecialchars(translate('cart.address.label')) ?></label>
                <textarea class="form-control" name="delivery_address" rows="3" placeholder="<?= htmlspecialchars(translate('cart.address.placeholder')) ?>" required><?= htmlspecialchars($deliveryDraft) ?></textarea>
                <small class="text-muted"><?= htmlspecialchars(translate('cart.address.hint')) ?></small>
            </div>
            <div class="d-flex flex-column gap-2 align-self-stretch">
                <a class="btn btn-outline-danger" href="/cart/clear" onclick="return confirm('<?= htmlspecialchars(translate('cart.actions.confirm_clear')) ?>');"><?= htmlspecialchars(translate('cart.actions.clear')) ?></a>
                <button type="submit" class="btn btn-success" id="submitOrder"><?= htmlspecialchars(translate('cart.actions.submit')) ?></button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>
</div>

<?php if (!empty($cartItems)): ?>
<script type="application/json" id="cartTranslations"><?= json_encode([
    'in_order' => translate('cart.toggle.included'),
    'not_selected' => translate('cart.toggle.not_selected'),
    'not_in_menu' => translate('cart.toggle.unavailable'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
<?php include __DIR__ . '/../partials/cart_scripts.php'; ?>
