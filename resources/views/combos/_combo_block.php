<?php
/** @var array $comboEntry */
$combo = $comboEntry['combo'];
$comboId = $comboEntry['id'];
$quantity = (int)($comboEntry['quantity'] ?? 1);
$priceEach = (float)($combo['price'] ?? $comboEntry['sum']);
$sum = (float)$comboEntry['sum'];
$priceFormatted = number_format($priceEach, 2, '.', ' ');
$sumFormatted = number_format($sum, 2, '.', ' ');
$selection = $combo['selection'] ?? [];
$hasSoup = !empty($selection['soup']);
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
                <span class="chip-text"><?= htmlspecialchars(translate('cart.toggle.included')) ?></span>
                <input type="checkbox" class="toggle-item" name="items[]" value="<?= htmlspecialchars($comboId) ?>" checked hidden>
            </label>
        <?php else: ?>
            <div class="toggle-chip disabled w-100 justify-content-center">
                <span class="indicator"></span>
                <span class="chip-text"><?= htmlspecialchars(translate('cart.toggle.unavailable')) ?></span>
            </div>
            <input type="checkbox" class="toggle-item" value="<?= htmlspecialchars($comboId) ?>" hidden disabled>
            <?php if (!empty($missingList)): ?>
                <small class="text-muted d-block mt-1"><?= htmlspecialchars(translate('combo.missing')) ?>: <?= htmlspecialchars(implode(', ', array_column($missingList, 'title'))) ?></small>
            <?php endif; ?>
        <?php endif; ?>
    </td>
    <td>
        <div class="combo-thumb text-gradient">🍱</div>
    </td>
    <td>
        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
            <?= htmlspecialchars($combo['title'] ?? translate('combo.title')) ?>
            <span class="badge bg-dark-subtle text-uppercase small">
                <?= $hasSoup ? htmlspecialchars(translate('combo.badge.with_soup')) : htmlspecialchars(translate('combo.badge.without_soup')) ?>
            </span>
        </div>
        <div class="combo-breakdown">
            <?php foreach ($items as $item): ?>
                <?php
                $itemId = (int)($item['id'] ?? 0);
                $missing = in_array($itemId, $missingIds, true);
                $itemLabel = $item['category'] ?? translate('combo.category.extra');
                $itemPrice = isset($item['price']) ? (float)$item['price'] : 0.0;
                ?>
                <div class="combo-breakdown-item <?= $missing ? 'combo-breakdown-missing' : '' ?>">
                    <div class="combo-breakdown-thumb">
                        <?php if (!empty($item['image'])): ?>
                            <img src="/assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <?php else: ?>
                            <span><?= htmlspecialchars(translate('common.no_photo')) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="combo-breakdown-title">
                            <div>
                                <span class="text-muted text-uppercase small me-2"><?= htmlspecialchars($itemLabel) ?></span>
                                <?= htmlspecialchars($item['title']) ?>
                            </div>
                            <?php if ($itemPrice > 0): ?>
                                <div class="combo-item-price"><?= number_format($itemPrice, 2, '.', ' ') ?> €</div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                            <div class="text-muted small text-truncate-2"><?= htmlspecialchars($item['description']) ?></div>
                        <?php else: ?>
                            <div class="text-muted small fst-italic"><?= htmlspecialchars(translate('combo.placeholder_desc')) ?></div>
                        <?php endif; ?>
                        <?php if ($missing): ?>
                            <div class="text-danger small"><?= htmlspecialchars(translate('combo.missing')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$isAvailable && empty($items)): ?>
                <div class="text-muted small"><?= htmlspecialchars(translate('combo.unavailable')) ?></div>
            <?php endif; ?>
        </div>
    </td>
    <td><?= $priceFormatted ?>€</td>
    <td class="no-row-toggle"><?= htmlspecialchars(translate('cart.qty.times', ['qty' => $quantity])) ?></td>
    <td><?= $sumFormatted ?>€</td>
    <td class="no-row-toggle">
        <a class="btn btn-sm btn-outline-danger" href="/cart/combo/remove?combo=<?= urlencode($combo['id']) ?>"><?= htmlspecialchars(translate('combo.remove')) ?></a>
    </td>
</tr>
