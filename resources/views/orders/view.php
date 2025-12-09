<?php $title = 'Doctor Gorilka — ' . translate('orders.view.title', ['id' => $orderId]); ?>
<div class="page-container">
<?php $backUrl = $isAdmin ? '/admin/orders' : '/orders'; ?>
<div class="actions mt-20 mb-15">
    <a class="btn btn-secondary" href="<?= $backUrl ?>"><?= htmlspecialchars(translate('common.back_to_orders')) ?></a>
    <?php if (!$isAdmin && ($order->status === 'new' || $order->status === 'cooking')): ?>
        <a class="btn btn-danger" href="/orders/view?id=<?= $orderId ?>&cancel=1" onclick="return confirm('<?= htmlspecialchars(translate('common.confirm_cancel')) ?>');"><?= htmlspecialchars(translate('orders.view.cancel')) ?></a>
    <?php endif; ?>
</div>

<div class="card-panel summary-card">
    <h2><?= htmlspecialchars(translate('orders.view.title', ['id' => $orderId])) ?></h2>
    <p><b><?= htmlspecialchars(translate('orders.view.date')) ?></b> <?= $order->createdAt ?></p>
    <p><b><?= htmlspecialchars(translate('orders.view.status')) ?></b>
        <span class="status status-<?= htmlspecialchars($order->status) ?>"><?= htmlspecialchars(translateStatus($order->status)) ?></span>
    </p>
    <p><b><?= htmlspecialchars(translate('orders.view.total')) ?></b> <?= number_format($order->totalPrice, 2, '.', ' ') ?> €</p>
    <p><b><?= htmlspecialchars(translate('orders.view.address')) ?></b> <?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></p>
    <?php if ($isAdmin): ?>
        <p><b><?= htmlspecialchars(translate('orders.view.customer')) ?></b> <?= htmlspecialchars($order->customerName ?? translate('orders.view.user_placeholder', ['id' => $order->userId])) ?></p>
        <p><b><?= htmlspecialchars(translate('orders.view.phone')) ?></b> <?= htmlspecialchars($order->customerPhone ?? translate('orders.view.phone_unknown')) ?></p>
    <?php endif; ?>
</div>

<h3><?= htmlspecialchars(translate('orders.view.dishes')) ?></h3>
<table class="table">
    <tr>
        <th><?= htmlspecialchars(translate('cart.table.photo')) ?></th>
        <th><?= htmlspecialchars(translate('cart.table.name')) ?></th>
        <th><?= htmlspecialchars(translate('cart.table.price')) ?></th>
        <th><?= htmlspecialchars(translate('cart.table.quantity')) ?></th>
        <th><?= htmlspecialchars(translate('cart.table.sum')) ?></th>
    </tr>
    <?php foreach ($order->items as $item): ?>
        <?php if ($item->isCombo()): ?>
            <?php $combo = $item->comboDetails; $comboItems = $combo['items'] ?? []; $soupExtraText = number_format(\App\Application\Service\ComboService::SOUP_EXTRA, 2, '.', ' '); ?>
            <tr class="combo-order-row">
                <td colspan="5">
                    <div class="combo-order-card">
                        <div class="combo-order-header">
                            <div>
                                <p class="text-uppercase text-muted small mb-1"><?= htmlspecialchars(translate('orders.view.combo_title')) ?></p>
                                <h5 class="mb-0"><?= htmlspecialchars($combo['title'] ?? $item->title) ?></h5>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">
                                    <?= number_format($item->price, 2, '.', ' ') ?> € × <?= $item->quantity ?>
                                </div>
                                <div class="text-muted"><?= htmlspecialchars(translate('orders.view.combo_sum', ['sum' => number_format($item->sum(), 2, '.', ' ')])) ?></div>
                                <div class="badge bg-dark-subtle mt-2">
                                    <?= !empty($combo['has_soup']) ? htmlspecialchars(translate('orders.view.badge_with_soup')) : htmlspecialchars(translate('orders.view.badge_without_soup')) ?>
                                </div>
                            </div>
                        </div>
                        <div class="combo-breakdown">
                            <?php foreach ($comboItems as $comboItem): ?>
                                <?php
                                $isUniqueItem = !empty($comboItem['is_unique']);
                                $itemRole = ($comboItem['type'] ?? '') === 'soup' ? translate('combo.role.soup') : translate('combo.role.main');
                                $itemPrice = $isUniqueItem ? (float)($comboItem['price'] ?? 0) : null;
                                ?>
                                <div class="combo-breakdown-item">
                                    <div class="combo-breakdown-thumb">
                                        <?php if (!empty($comboItem['image'])): ?>
                                            <img src="/assets/images/<?= htmlspecialchars($comboItem['image']) ?>" alt="<?= htmlspecialchars($comboItem['title']) ?>">
                                        <?php else: ?>
                                            <span><?= htmlspecialchars(translate('common.no_photo')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="combo-breakdown-title">
                                            <div>
                                                <span class="text-muted text-uppercase small me-2"><?= htmlspecialchars($itemRole) ?></span>
                                                <?= htmlspecialchars($comboItem['title']) ?>
                                                <?php if ($isUniqueItem): ?>
                                                    <span class="combo-unique-chip ms-2"><?= htmlspecialchars(translate('combo.unique')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($itemPrice): ?>
                                                <div class="combo-item-price"><?= number_format($itemPrice, 2, '.', ' ') ?> €</div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($comboItem['description'])): ?>
                                            <div class="text-muted small text-truncate-2"><?= htmlspecialchars($comboItem['description']) ?></div>
                                        <?php else: ?>
                                            <div class="text-muted small fst-italic"><?= htmlspecialchars(translate('combo.placeholder_desc')) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty(array_filter($comboItems, static fn ($ci) => ($ci['type'] ?? '') === 'soup'))): ?>
                                <div class="combo-breakdown-item combo-breakdown-placeholder">
                                    <div class="combo-breakdown-thumb">—</div>
                                    <div>
                                        <div class="combo-breakdown-title">
                                            <span class="text-muted text-uppercase small me-2"><?= htmlspecialchars(translate('combo.role.soup')) ?></span>
                                            <span><?= htmlspecialchars(translate('combo.badge.without_soup')) ?></span>
                                        </div>
                                        <div class="text-muted small"><?= htmlspecialchars(translate('orders.view.combo_hint', ['price' => $soupExtraText])) ?></div>
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
                    <img class="thumb" src="https://via.placeholder.com/80" alt="<?= htmlspecialchars(translate('common.no_photo')) ?>">
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
