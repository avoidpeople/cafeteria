<?php
use Carbon\Carbon;

$title = 'Doctor Gorilka — ' . translate('admin.orders.details.title', ['id' => $order->orderCode ?? $order->id]);
$timezone = appTimezone();
$locale = currentLocale() === 'lv' ? 'lv' : 'ru';
$createdAt = Carbon::parse($order->createdAt, 'UTC')->setTimezone($timezone)->locale($locale)->isoFormat('D MMMM YYYY, HH:mm');
?>
<div class="page-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="mb-0"><?= htmlspecialchars(translate('admin.orders.details.heading', ['id' => $order->orderCode ?? $order->id])) ?></h1>
        <a class="btn btn-secondary" href="/admin/orders"><?= htmlspecialchars(translate('common.back_to_orders')) ?></a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="fw-semibold text-muted small mb-1"><?= htmlspecialchars(translate('admin.orders.details.code')) ?></div>
                    <div class="fs-5 fw-bold">#<?= htmlspecialchars($order->orderCode ?? $order->id) ?></div>
                    <div class="text-muted small mt-2"><?= htmlspecialchars($createdAt) ?></div>
                    <div class="mt-2">
                        <span class="status status-<?= htmlspecialchars($order->status) ?>">
                            <?= htmlspecialchars(translateStatus($order->status)) ?>
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="fw-semibold text-muted small mb-1"><?= htmlspecialchars(translate('admin.orders.details.total')) ?></div>
                        <div class="fs-5 fw-bold"><?= number_format($order->totalPrice, 2, '.', ' ') ?> €</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-muted small mb-1"><?= htmlspecialchars(translate('admin.orders.details.customer')) ?></div>
                    <div class="fw-bold"><?= htmlspecialchars($order->customerName ?? translate('orders.view.user_placeholder', ['id' => $order->userId])) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars(translate('admin.orders.details.user_id')) ?>: <?= $order->userId ?></div>
                    <div class="mt-2">
                        <div class="fw-semibold text-muted small mb-1"><?= htmlspecialchars(translate('admin.orders.details.phone')) ?></div>
                        <div><?= htmlspecialchars($order->customerPhone ?? translate('orders.view.phone_unknown')) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-muted small mb-1"><?= htmlspecialchars(translate('admin.orders.details.address')) ?></div>
                    <div><?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></div>
                    <?php if (!empty($order->comment)): ?>
                        <div class="mt-3">
                            <div class="fw-semibold text-muted small mb-1"><?= htmlspecialchars(translate('admin.orders.details.comment')) ?></div>
                            <div class="comment-box"><?= nl2br(htmlspecialchars($order->comment)) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars(translate('cart.table.photo')) ?></th>
                            <th><?= htmlspecialchars(translate('cart.table.name')) ?></th>
                            <th><?= htmlspecialchars(translate('cart.table.price')) ?></th>
                            <th><?= htmlspecialchars(translate('cart.table.quantity')) ?></th>
                            <th><?= htmlspecialchars(translate('cart.table.sum')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->items as $item): ?>
                            <?php if ($item->isCombo()): ?>
                                <?php $combo = $item->comboDetails; $comboItems = $combo['items'] ?? []; $selection = $combo['selection'] ?? []; ?>
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
                                                        <?= !empty($selection['soup']) ? htmlspecialchars(translate('orders.view.badge_with_soup')) : htmlspecialchars(translate('orders.view.badge_without_soup')) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="combo-breakdown">
                                                <?php foreach ($comboItems as $comboItem): ?>
                                                    <?php
                                                    $itemLabel = $comboItem['category'] ?? translate('combo.category.extra');
                                                    $itemPrice = isset($comboItem['price']) ? (float)$comboItem['price'] : 0.0;
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
                                                                    <span class="text-muted text-uppercase small me-2"><?= htmlspecialchars($itemLabel) ?></span>
                                                                    <?= htmlspecialchars($comboItem['title']) ?>
                                                                </div>
                                                                <?php if ($itemPrice > 0): ?>
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3"><?= htmlspecialchars(translate('admin.orders.details.history_title')) ?></h4>
            <?php if (empty($order->statusHistory)): ?>
                <div class="text-muted small"><?= htmlspecialchars(translate('admin.orders.details.history_empty')) ?></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th><?= htmlspecialchars(translate('admin.orders.details.history_date')) ?></th>
                            <th><?= htmlspecialchars(translate('admin.orders.details.history_old')) ?></th>
                            <th><?= htmlspecialchars(translate('admin.orders.details.history_new')) ?></th>
                            <th><?= htmlspecialchars(translate('admin.orders.details.history_user')) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($order->statusHistory as $history): ?>
                            <tr>
                                <td><?= htmlspecialchars(Carbon::parse($history->changedAt, 'UTC')->setTimezone($timezone)->locale($locale)->isoFormat('YYYY-MM-DD HH:mm:ss')) ?></td>
                                <td><?= htmlspecialchars($history->oldStatus ? translateStatus($history->oldStatus) : '—') ?></td>
                                <td><?= htmlspecialchars(translateStatus($history->newStatus)) ?></td>
                                <td><?= htmlspecialchars($history->changedByName ?? translate('admin.orders.details.history_system')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
