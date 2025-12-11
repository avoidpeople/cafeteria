<?php
use Carbon\Carbon;

$title = 'Doctor Gorilka — ' . translate('admin.orders.title');
$timezone = appTimezone();
$locale = currentLocale() === 'lv' ? 'lv' : 'ru';
$currentDate = Carbon::now($timezone)->locale($locale);
$ordersByDay = [];

foreach ($orders as $order) {
    $orderMoment = Carbon::parse($order->createdAt, 'UTC')->setTimezone($timezone)->locale($locale);
    $dayKey = $orderMoment->toDateString();
    $ordersByDay[$dayKey][] = ['order' => $order, 'moment' => $orderMoment];
}

foreach ($ordersByDay as &$dayOrders) {
    usort($dayOrders, static fn ($a, $b) => $b['moment']->getTimestamp() <=> $a['moment']->getTimestamp());
}
unset($dayOrders);

$dateKeys = array_keys($ordersByDay);
$dateKeys[] = $currentDate->toDateString();
$maxDateKey = max($dateKeys);
$minDateKey = min($dateKeys);
$dateRange = [];
$cursor = Carbon::createFromFormat('Y-m-d', $maxDateKey, $timezone);
$endDate = Carbon::createFromFormat('Y-m-d', $minDateKey, $timezone);
while ($cursor->greaterThanOrEqualTo($endDate)) {
    $dateRange[] = $cursor->copy()->locale($locale);
    $cursor = $cursor->subDay();
}
?>
<div class="page-container">
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <h1 class="mb-0"><?= htmlspecialchars(translate('admin.orders.title')) ?></h1>
        <button class="btn btn-outline-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#ordersHelp" aria-expanded="false" aria-controls="ordersHelp">
            <?= htmlspecialchars(translate('admin.orders.help.button')) ?>
        </button>
    </div>
    <a class="btn btn-secondary" href="/"><?= htmlspecialchars(translate('common.back_to_home')) ?></a>
</div>

<div class="collapse" id="ordersHelp">
    <div class="alert alert-info small mb-3">
        <div class="fw-semibold mb-2"><?= htmlspecialchars(translate('admin.orders.help.title')) ?></div>
        <ul class="mb-0 ps-3">
            <li><?= htmlspecialchars(translate('admin.orders.help.purpose')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.filters')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.row')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.status')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.open')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.delete')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.cancelled')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.colors')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.summary')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.flow')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.contact')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.history')) ?></li>
            <li><?= htmlspecialchars(translate('admin.orders.help.nocancel')) ?></li>
        </ul>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><?= htmlspecialchars(translate('admin.orders.stats.total')) ?></div>
            <div class="stat-value"><?= $summary['total_orders'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><?= htmlspecialchars(translate('admin.orders.stats.new')) ?></div>
            <div class="stat-value"><?= $summary['new_orders'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><?= htmlspecialchars(translate('admin.orders.stats.pending')) ?></div>
            <div class="stat-value"><?= $pendingCount ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><?= htmlspecialchars(translate('admin.orders.stats.sum')) ?></div>
            <div class="stat-value"><?= number_format($summary['total_sum'] ?? 0, 2, '.', ' ') ?> €</div>
        </div>
    </div>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-sm-6 col-md-3">
        <select name="status" class="form-select">
            <option value=""><?= htmlspecialchars(translate('admin.orders.filter.status')) ?></option>
            <?php foreach (['pending','new','cooking','ready','delivered','cancelled'] as $status): ?>
                <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= htmlspecialchars(translateStatus($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-6 col-md-4">
        <input type="text" class="form-control" placeholder="<?= htmlspecialchars(translate('admin.orders.filter.search')) ?>" name="user" value="<?= htmlspecialchars($userSearch) ?>">
    </div>
    <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= htmlspecialchars(translate('admin.orders.filter.submit')) ?></button>
        <a href="/admin/orders" class="btn btn-outline-secondary"><?= htmlspecialchars(translate('admin.orders.filter.reset')) ?></a>
    </div>
</form>

<form method="POST" action="/admin/orders/bulk-status" id="bulkStatusForm" class="d-flex align-items-end gap-2 flex-wrap mb-3">
    <?= csrf_field() ?>
    <div>
        <label class="form-label mb-1 small"><?= htmlspecialchars(translate('admin.orders.bulk.label')) ?></label>
        <select name="status" class="form-select">
            <?php foreach (['new','cooking','ready','delivered'] as $status): ?>
                <option value="<?= $status ?>"><?= htmlspecialchars(translateStatus($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary"><?= htmlspecialchars(translate('admin.orders.bulk.submit')) ?></button>
</form>

<?php foreach ($dateRange as $day): ?>
    <?php
    $dayKey = $day->toDateString();
    $dayOrders = $ordersByDay[$dayKey] ?? [];
    ?>
    <h2 class="orders-date-header"><?= htmlspecialchars($day->isoFormat('D MMMM YYYY, dddd')) ?></h2>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th><?= htmlspecialchars(translate('admin.orders.table.select')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.number')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.user')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.date')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.delivery')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.amount')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.status')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.update')) ?></th>
                        <th><?= htmlspecialchars(translate('admin.orders.table.view')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($dayOrders)): ?>
                        <?php foreach ($dayOrders as $entry): ?>
                            <?php
                            $order = $entry['order'];
                            $orderMoment = $entry['moment'];
                            ?>
                            <tr>
                                <td class="align-middle text-center">
                                    <?php if ($order->status === 'cancelled'): ?>
                                        <input type="checkbox" class="form-check-input" disabled title="<?= htmlspecialchars(translate('admin.orders.bulk.disabled_cancelled')) ?>">
                                    <?php else: ?>
                                        <input type="checkbox" class="form-check-input" name="orders[]" value="<?= $order->id ?>" form="bulkStatusForm" aria-label="<?= htmlspecialchars(translate('admin.orders.bulk.checkbox_label', ['id' => $order->id])) ?>">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold">#<?= htmlspecialchars($order->orderCode ?? $order->id) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars(translate('admin.orders.table.id_internal')) ?>: <?= $order->id ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($order->customerName ?? translate('orders.view.user_placeholder', ['id' => $order->userId])) ?>
                                    <div class="text-muted small"><?= htmlspecialchars(translate('admin.orders.table.user_id')) ?>: <?= $order->userId ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($orderMoment->isoFormat('D MMMM YYYY, dddd HH:mm')) ?>
                                    <?php if (!empty($order->customerPhone)): ?>
                                        <div class="text-muted small"><?= htmlspecialchars(translate('admin.orders.table.phone')) ?> <?= htmlspecialchars($order->customerPhone) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= nl2br(htmlspecialchars($order->deliveryAddress ?? '—')) ?></td>
                                <td><?= number_format($order->totalPrice, 2, '.', ' ') ?> €</td>
                                <td>
                                    <span class="status status-<?= htmlspecialchars($order->status) ?>">
                                        <?= htmlspecialchars(translateStatus($order->status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($order->status === 'cancelled'): ?>
                                        <div class="text-muted small mb-2"><?= htmlspecialchars(translate('admin.orders.cancelled_notice')) ?></div>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/orders/status" class="d-flex flex-column gap-2 status-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $order->id ?>">
                                            <select name="status" class="form-select form-select-sm" required>
                                                <?php foreach (['new','cooking','ready','delivered','cancelled'] as $status): ?>
                                                    <option value="<?= $status ?>" <?= $order->status === $status ? 'selected' : '' ?>><?= htmlspecialchars(translateStatus($status)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-outline-primary btn-sm"><?= htmlspecialchars(translate('admin.orders.update.submit')) ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/orders/delete" class="mt-2" onsubmit="return confirm('<?= htmlspecialchars(translate('admin.orders.delete')) ?>');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $order->id ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><?= htmlspecialchars(translate('combo.remove')) ?></button>
                                    </form>
                                </td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="/admin/orders/show?id=<?= $order->id ?>"><?= htmlspecialchars(translate('admin.orders.open')) ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php if (!empty($orders)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.status-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const select = form.querySelector('select[name="status"]');
            if (select && select.value === 'cancelled') {
                const confirmed = confirm('<?= htmlspecialchars(translate('common.confirm_cancel')) ?>');
                if (!confirmed) {
                    event.preventDefault();
                }
            }
        });
    });
});
</script>
<?php endif; ?>
