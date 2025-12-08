<?php
/** @var array $comboEntry */
$combo = $comboEntry['combo'];
$comboId = $comboEntry['id'];
$quantity = (int)($comboEntry['quantity'] ?? 1);
$priceEach = (float)($combo['price'] ?? $comboEntry['sum']);
$sum = (float)$comboEntry['sum'];
$priceFormatted = number_format($priceEach, 2, '.', ' ');
$sumFormatted = number_format($sum, 2, '.', ' ');
$hasSoup = !empty($combo['has_soup']);
$items = $combo['items'] ?? [];
$isAvailable = $comboEntry['available'] ?? true;
$missingList = $comboEntry['missing'] ?? [];
$missingIds = array_map(static fn ($item) => (int)($item['id'] ?? 0), $missingList);
$rowClass = $isAvailable ? 'row-selected' : 'row-unavailable';
$availableAttr = $isAvailable ? '1' : '0';
?>
<tr class="cart-row combo-row <?= $rowClass ?>" data-id="<?= htmlspecialchars($comboId) ?>" data-sum="<?= htmlspecialchars((string)$sum) ?>" data-qty="<?= $quantity ?>" data-available="<?= $availableAttr ?>">
    <td>
        <?php if ($isAvailable): ?>
            <label class="toggle-chip active w-100 justify-content-center">
                <span class="indicator"></span>
                <span class="chip-text">В заказе</span>
                <input type="checkbox" class="toggle-item" name="items[]" value="<?= htmlspecialchars($comboId) ?>" checked hidden>
            </label>
        <?php else: ?>
            <div class="toggle-chip disabled w-100 justify-content-center">
                <span class="indicator"></span>
                <span class="chip-text">Нет в меню сегодня</span>
            </div>
            <input type="checkbox" class="toggle-item" value="<?= htmlspecialchars($comboId) ?>" hidden disabled>
            <?php if (!empty($missingList)): ?>
                <small class="text-muted d-block mt-1">Недоступно: <?= htmlspecialchars(implode(', ', array_column($missingList, 'title'))) ?></small>
            <?php endif; ?>
        <?php endif; ?>
    </td>
    <td>
        <div class="combo-thumb text-gradient">🍱</div>
    </td>
    <td>
        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
            <?= htmlspecialchars($combo['title'] ?? 'Комплексный обед') ?>
            <span class="badge bg-dark-subtle text-uppercase small">
                <?= $hasSoup ? 'Суп включен' : 'Без супа' ?>
            </span>
        </div>
        <div class="combo-breakdown">
            <?php foreach ($items as $item): ?>
                <?php $itemId = (int)($item['id'] ?? 0); $missing = in_array($itemId, $missingIds, true); ?>
                <div class="combo-breakdown-item <?= $missing ? 'combo-breakdown-missing' : '' ?>">
                    <div class="combo-breakdown-thumb">
                        <?php if (!empty($item['image'])): ?>
                            <img src="/assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <?php else: ?>
                            <span>Нет фото</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="combo-breakdown-title">
                            <span class="text-muted text-uppercase small me-2"><?= $item['type'] === 'soup' ? 'Суп' : 'Горячее' ?></span>
                            <?= htmlspecialchars($item['title']) ?>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                            <div class="text-muted small text-truncate-2"><?= htmlspecialchars($item['description']) ?></div>
                        <?php else: ?>
                            <div class="text-muted small fst-italic">Описание недоступно</div>
                        <?php endif; ?>
                        <?php if ($missing): ?>
                            <div class="text-danger small">Позиция недоступна сегодня</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$isAvailable && empty($items)): ?>
                <div class="text-muted small">Состав комплекса недоступен</div>
            <?php endif; ?>
        </div>
    </td>
    <td><?= $priceFormatted ?>€</td>
    <td class="no-row-toggle">× <?= $quantity ?></td>
    <td><?= $sumFormatted ?>€</td>
    <td class="no-row-toggle">
        <a class="btn btn-sm btn-outline-danger" href="/cart/combo/remove?combo=<?= urlencode($combo['id']) ?>">Удалить</a>
    </td>
</tr>
