<?php $title = 'Doctor Gorilka — ' . translate('admin.menu.title'); ?>
<div class="page-container">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0"><?= htmlspecialchars(translate('admin.menu.title')) ?></h1>
    <a class="btn btn-secondary" href="/menu"><?= htmlspecialchars(translate('admin.menu.back')) ?></a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php $selectedToday = $todayIds ?? []; ?>

<div class="admin-menu-layout mt-1">
    <div class="admin-column admin-form-block">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h4 class="card-title mb-3"><?= htmlspecialchars(translate('admin.menu.form.title')) ?></h4>
                <form method="POST" action="/admin/menu" enctype="multipart/form-data" class="d-flex flex-column gap-3" id="menuForm">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="existing_gallery" id="existing_gallery">
                    <input type="hidden" name="current_image" id="current_image">

                    <div>
                        <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.name')) ?></label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>

                    <div>
                        <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.description')) ?></label>
                        <textarea name="description" class="form-control" id="edit_desc"></textarea>
                    </div>

                    <div>
                        <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.ingredients')) ?></label>
                        <input type="text" name="ingredients" class="form-control" id="edit_ingr">
                    </div>

                    <div>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <label class="form-label mb-0"><?= htmlspecialchars(translate('admin.menu.form.price_label')) ?></label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="edit_use_manual_price" name="use_manual_price" value="1">
                                <label class="form-check-label" for="edit_use_manual_price"><?= htmlspecialchars(translate('admin.menu.form.manual_price')) ?></label>
                            </div>
                        </div>
                        <input type="number" step="0.01" class="form-control mt-2" name="price" id="edit_price" placeholder="<?= htmlspecialchars(translate('admin.menu.form.price_placeholder')) ?>" disabled>
                        <small class="text-muted" id="priceManualHint"><?= htmlspecialchars(translate('admin.menu.form.price_hint')) ?></small>
                    </div>

                    <div>
                        <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.category')) ?></label>
                        <input type="text" class="form-control" name="category" id="edit_category">
                    </div>

                    <div>
                        <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.photos')) ?></label>
                        <input type="file" class="form-control" name="image[]" accept="image/*" multiple>
                        <small class="text-muted d-block mt-1"><?= htmlspecialchars(translate('admin.menu.form.photos_hint')) ?></small>
                    </div>
                    <div id="galleryPreview" class="d-flex flex-wrap gap-2"></div>

                    <button type="submit" class="btn btn-success"><?= htmlspecialchars(translate('admin.menu.form.submit')) ?></button>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-column admin-table-block">
        <form method="POST" action="/admin/menu/today" class="h-100 d-flex flex-column">
            <div class="card border-0 shadow-sm flex-grow-1">
                <div class="card-body">
                    <h4 class="card-title mb-3"><?= htmlspecialchars(translate('admin.menu.table.title')) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars(translate('admin.menu.table.subtitle')) ?></p>
                    <div class="table-responsive">
                        <table class="table table-menu">
                            <thead>
                                <tr>
                                    <th class="today-column"><?= htmlspecialchars(translate('admin.menu.table.menu')) ?></th>
                                    <th>ID</th>
                                    <th><?= htmlspecialchars(translate('admin.menu.table.photo')) ?></th>
                                    <th><?= htmlspecialchars(translate('admin.menu.table.name')) ?></th>
                                    <th><?= htmlspecialchars(translate('admin.menu.table.price')) ?></th>
                                    <th><?= htmlspecialchars(translate('admin.menu.table.category')) ?></th>
                                    <th><?= htmlspecialchars(translate('admin.menu.table.actions')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <?php $gallery = $item->galleryImages(); ?>
                                    <tr>
                                        <td class="today-cell">
                                            <label class="today-flag">
                                                <input type="checkbox" class="today-checkbox" name="today_ids[]" value="<?= $item->id ?>" <?= in_array($item->id, $selectedToday, true) ? 'checked' : '' ?>>
                                                <span class="today-indicator"></span>
                                                <span class="today-label"><?= htmlspecialchars(translate('admin.menu.today.flag')) ?></span>
                                            </label>
                                        </td>
                                        <td><?= $item->id ?></td>
                                        <td>
                                            <?php if ($item->primaryImage()): ?>
                                                <img src="/assets/images/<?= htmlspecialchars($item->primaryImage()) ?>" alt="" class="menu-thumb">
                                            <?php else: ?>
                                                <span class="text-muted"><?= htmlspecialchars(translate('common.no_photo')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item->title) ?></td>
                                        <td>
                                            <?php if ($item->isUnique()): ?>
                                                <span class="fw-semibold text-info"><?= number_format($item->price, 2, '.', ' ') ?> €</span>
                                                <span class="badge bg-info-subtle text-info-emphasis ms-2"><?= htmlspecialchars(translate('admin.menu.price.unique')) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= htmlspecialchars(translate('admin.menu.price.standard')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item->category ?? '') ?></td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <button class="btn btn-sm btn-primary" type="button" onclick='fillForm(<?= json_encode([
                                                    'id' => $item->id,
                                                    'title' => $item->title,
                                                    'description' => $item->description,
                                                    'ingredients' => $item->ingredients,
                                                    'price' => $item->price,
                                                    'use_manual_price' => $item->isUnique(),
                                                    'category' => $item->category,
                                                    'image' => $item->primaryImage(),
                                                    'gallery' => $gallery,
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><?= htmlspecialchars(translate('admin.menu.actions.edit')) ?></button>
                                                <a class="btn btn-sm btn-outline-danger" href="/admin/menu/delete?id=<?= $item->id ?>" onclick="return confirm('<?= htmlspecialchars(translate('admin.menu.actions.confirm_delete')) ?>');"><?= htmlspecialchars(translate('combo.remove')) ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-outline-primary"><?= htmlspecialchars(translate('admin.menu.today.save')) ?></button>
            </div>
        </form>
    </div>
    <div class="admin-column admin-today-block">
        <div class="card border-0 shadow-sm admin-today-card h-100">
            <div class="card-body">
                <h4 class="card-title mb-3"><?= htmlspecialchars(translate('admin.menu.today.title', ['count' => count($todayItems ?? [])])) ?></h4>
                <?php if (empty($todayItems)): ?>
                    <div class="text-muted"><?= htmlspecialchars(translate('admin.menu.today.empty')) ?></div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($todayItems as $item): ?>
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <?php $img = $item->primaryImage(); ?>
                                    <?php if ($img): ?>
                                        <img src="/assets/images/<?= htmlspecialchars($img) ?>" alt="" style="width:60px;height:60px;object-fit:cover;" class="rounded">
                                    <?php else: ?>
                                        <span class="text-muted small"><?= htmlspecialchars(translate('common.no_photo')) ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($item->title) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($item->category ?? translate('menu.card.no_category')) ?></div>
                                        <div class="text-success fw-semibold">
                                            <?php if ($item->isUnique()): ?>
                                                <?= number_format($item->price, 2, '.', ' ') ?> €
                                            <?php else: ?>
                                                <span class="text-muted"><?= htmlspecialchars(translate('admin.menu.price.standard')) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div>

<script>
const manualPriceToggle = document.getElementById('edit_use_manual_price');
const manualPriceInput = document.getElementById('edit_price');
const priceManualHint = document.getElementById('priceManualHint');
const manualHintManual = <?= json_encode(translate('admin.menu.form.price_hint_manual'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const manualHintAuto = <?= json_encode(translate('admin.menu.form.price_hint'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function syncManualPriceField(forceValue = null) {
    if (!manualPriceToggle || !manualPriceInput) {
        return;
    }
    const enabled = forceValue !== null ? Boolean(forceValue) : manualPriceToggle.checked;
    manualPriceToggle.checked = enabled;
    manualPriceInput.disabled = !enabled;
    if (!enabled) {
        manualPriceInput.value = '';
    }
    if (priceManualHint) {
        priceManualHint.textContent = enabled ? manualHintManual : manualHintAuto;
    }
}

manualPriceToggle?.addEventListener('change', () => syncManualPriceField());
syncManualPriceField(false);

function fillForm(data) {
    document.getElementById('edit_id').value = data.id || '';
    document.getElementById('edit_title').value = data.title || '';
    document.getElementById('edit_desc').value = data.description || '';
    document.getElementById('edit_ingr').value = data.ingredients || '';
    document.getElementById('edit_category').value = data.category || '';
    document.getElementById('current_image').value = data.image || '';
    document.getElementById('existing_gallery').value = JSON.stringify(data.gallery || []);
    syncManualPriceField(Boolean(data.use_manual_price));
    if (manualPriceInput && data.use_manual_price) {
        manualPriceInput.value = data.price || '';
    }
    renderGalleryPreview(data.gallery || []);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function renderGalleryPreview(images) {
    const container = document.getElementById('galleryPreview');
    container.innerHTML = '';
    images.forEach(img => {
        const el = document.createElement('img');
        el.src = '/assets/images/' + img;
        el.style.width = '60px';
        el.className = 'rounded';
        container.appendChild(el);
    });
}
</script>
