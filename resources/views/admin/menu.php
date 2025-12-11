<?php
use Carbon\Carbon;

$title = 'Doctor Gorilka — ' . translate('admin.menu.title');
$selectedToday = $todayIds ?? [];
$locale = currentLocale() === 'lv' ? 'lv' : 'ru';
$todayDateLabel = Carbon::now('Europe/Riga')->locale($locale)->isoFormat('D MMMM YYYY, dddd');
$todayCount = count($selectedToday);
?>
<div class="page-container">
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="mb-1"><?= htmlspecialchars(translate('admin.menu.title')) ?></h1>
            <button class="btn btn-outline-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#menuHelp" aria-expanded="false" aria-controls="menuHelp">
                <?= htmlspecialchars(translate('admin.menu.help.button')) ?>
            </button>
        </div>
        <div class="text-muted fw-semibold small d-flex align-items-center gap-2 flex-wrap">
            <span><?= htmlspecialchars($todayDateLabel) ?></span>
        </div>
    </div>
    <a class="btn btn-secondary" href="/menu"><?= htmlspecialchars(translate('admin.menu.back')) ?></a>
</div>

<div class="collapse" id="menuHelp">
    <div class="alert alert-info small mb-3">
        <div class="fw-semibold mb-2"><?= htmlspecialchars(translate('admin.menu.help.title')) ?></div>
        <ul class="mb-0 ps-3">
            <li><?= htmlspecialchars(translate('admin.menu.help.manage')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.locale')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.autofill')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.details')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.price')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.category')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.photos')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.today')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.actions')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.save')) ?></li>
            <li><?= htmlspecialchars(translate('admin.menu.help.preview')) ?></li>
        </ul>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
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
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0"><?= htmlspecialchars(translate('admin.menu.form.name')) ?></label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggleLocalizedNames">
                                <label class="form-check-label small" for="toggleLocalizedNames"><?= htmlspecialchars(translate('admin.menu.form.name_locale_toggle')) ?></label>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 helper-hint" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars(translate('admin.menu.form.name_locale_tooltip')) ?>">
                                    <?= htmlspecialchars(translate('admin.menu.form.locale_help_label')) ?>
                                </button>
                            </div>
                        </div>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                        <div id="localizedNames" class="row g-2 mt-2 d-none">
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><?= htmlspecialchars(translate('admin.menu.form.name_ru')) ?></label>
                                <input type="text" class="form-control form-control-sm" name="name_ru" id="edit_name_ru" placeholder="<?= htmlspecialchars(translate('admin.menu.form.name_placeholder_ru')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><?= htmlspecialchars(translate('admin.menu.form.name_lv')) ?></label>
                                <input type="text" class="form-control form-control-sm" name="name_lv" id="edit_name_lv" placeholder="<?= htmlspecialchars(translate('admin.menu.form.name_placeholder_lv')) ?>">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0"><?= htmlspecialchars(translate('admin.menu.form.description')) ?></label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggleLocalizedDesc">
                                <label class="form-check-label small" for="toggleLocalizedDesc"><?= htmlspecialchars(translate('admin.menu.form.desc_locale_toggle')) ?></label>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 helper-hint" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars(translate('admin.menu.form.desc_locale_tooltip')) ?>">
                                    <?= htmlspecialchars(translate('admin.menu.form.locale_help_label')) ?>
                                </button>
                            </div>
                        </div>
                        <textarea name="description" class="form-control" id="edit_desc"></textarea>
                        <div id="localizedDescriptions" class="row g-2 mt-2 d-none">
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><?= htmlspecialchars(translate('admin.menu.form.description_ru')) ?></label>
                                <textarea class="form-control form-control-sm" name="description_ru" id="edit_desc_ru" rows="2" placeholder="<?= htmlspecialchars(translate('admin.menu.form.desc_placeholder_ru')) ?>"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><?= htmlspecialchars(translate('admin.menu.form.description_lv')) ?></label>
                                <textarea class="form-control form-control-sm" name="description_lv" id="edit_desc_lv" rows="2" placeholder="<?= htmlspecialchars(translate('admin.menu.form.desc_placeholder_lv')) ?>"></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.ingredients')) ?></label>
                        <input type="text" name="ingredients" class="form-control" id="edit_ingr">
                    </div>

                    <div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_show_allergens">
                            <label class="form-check-label" for="edit_show_allergens"><?= htmlspecialchars(translate('admin.menu.form.allergens_toggle')) ?></label>
                        </div>
                        <div id="allergensWrapper" class="d-none">
                            <label class="form-label"><?= htmlspecialchars(translate('admin.menu.form.allergens')) ?></label>
                            <textarea name="allergens" class="form-control" id="edit_allergens" rows="3"></textarea>
                        </div>
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
                        <select class="form-select form-select-dark" name="category_type" id="edit_category_type">
                            <option value="main"><?= htmlspecialchars(translate('category.hot')) ?></option>
                            <option value="garnish"><?= htmlspecialchars(translate('category.garnish')) ?></option>
                            <option value="soup"><?= htmlspecialchars(translate('category.soup')) ?></option>
                            <option value="custom"><?= htmlspecialchars(translate('category.custom')) ?></option>
                        </select>
                        <div id="categoryCustomWrapper" class="mt-2 d-none">
                            <input type="text" class="form-control form-control-dark" name="category_custom" id="edit_category_custom" placeholder="<?= htmlspecialchars(translate('admin.menu.form.category_custom_placeholder')) ?>">
                            <small class="text-muted"><?= htmlspecialchars(translate('admin.menu.form.category_custom_hint')) ?></small>
                        </div>
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
                                    <th><?= htmlspecialchars(translate('admin.menu.table.allergens')) ?></th>
                                    <th><?= htmlspecialchars(translate('admin.menu.table.actions')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <?php $gallery = $item->galleryImages(); ?>
                                    <?php $isSystemCombo = ($item->category ?? '') === '_combo'; ?>
                                    <tr>
                                        <?php if ($isSystemCombo): ?>
                                            <td class="today-cell text-muted small"><?= htmlspecialchars(translate('admin.menu.errors.system_locked')) ?></td>
                                        <?php else: ?>
                                            <td class="today-cell">
                                                <label class="today-flag">
                                                    <input type="checkbox" class="today-checkbox" name="today_ids[]" value="<?= $item->id ?>" <?= in_array($item->id, $selectedToday, true) ? 'checked' : '' ?>>
                                                    <span class="today-indicator"></span>
                                                    <span class="today-label"><?= htmlspecialchars(translate('admin.menu.today.flag')) ?></span>
                                                </label>
                                            </td>
                                        <?php endif; ?>
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
                                        <td style="max-width: 180px;">
                                            <?php if ($item->allergens): ?>
                                                <span class="allergens-badge"><?= htmlspecialchars($item->allergens) ?></span>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isSystemCombo): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars(translate('admin.menu.price.standard')) ?></span>
                                                <div class="text-muted small mt-1"><?= htmlspecialchars(translate('admin.menu.errors.system_locked')) ?></div>
                                            <?php else: ?>
                                                <div class="d-flex flex-column gap-2">
                                                <button class="btn btn-sm btn-primary" type="button" onclick='fillForm(<?= json_encode([
                                                    'id' => $item->id,
                                                    'title' => $item->title,
                                                    'name_original' => $item->nameOriginal ?? $item->title,
                                                    'description' => $item->description,
                                                    'description_original' => $item->descriptionOriginal ?? $item->description,
                                                    'description_ru' => $item->descriptionRu ?? null,
                                                    'description_lv' => $item->descriptionLv ?? null,
                                                    'ingredients' => $item->ingredients,
                                                    'ingredients_original' => $item->ingredientsOriginal ?? $item->ingredients,
                                                    'name_ru' => $item->nameRu ?? null,
                                                    'name_lv' => $item->nameLv ?? null,
                                                    'allergens' => $item->allergens,
                                                    'price' => $item->price,
                                                    'use_manual_price' => $item->isUnique(),
                                                    'category' => $item->category,
                                                    'category_original' => $item->categoryOriginal ?? $item->category,
                                                    'category_role' => $item->categoryRole ?? 'main',
                                                    'image' => $item->primaryImage(),
                                                    'gallery' => $gallery,
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><?= htmlspecialchars(translate('admin.menu.actions.edit')) ?></button>
                                                    <a class="btn btn-sm btn-outline-danger" href="/admin/menu/delete?id=<?= $item->id ?>" onclick="return confirm('<?= htmlspecialchars(translate('admin.menu.actions.confirm_delete')) ?>');"><?= htmlspecialchars(translate('combo.remove')) ?></a>
                                                </div>
                                            <?php endif; ?>
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
                <h4 class="card-title mb-1"><?= htmlspecialchars(translate('admin.menu.today.title', ['count' => count($todayItems ?? [])])) ?></h4>
                <div class="text-muted small mb-3 d-flex align-items-center gap-2">
                    <span aria-hidden="true">📅</span>
                    <span><?= htmlspecialchars($todayDateLabel) ?></span>
                </div>
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
const categorySelect = document.getElementById('edit_category_type');
const categoryCustomWrapper = document.getElementById('categoryCustomWrapper');
const categoryCustomInput = document.getElementById('edit_category_custom');
const allergensToggle = document.getElementById('edit_show_allergens');
const allergensWrapper = document.getElementById('allergensWrapper');
const allergensInput = document.getElementById('edit_allergens');
const toggleLocalizedNames = document.getElementById('toggleLocalizedNames');
const toggleLocalizedDesc = document.getElementById('toggleLocalizedDesc');
const localizedNames = document.getElementById('localizedNames');
const localizedDescriptions = document.getElementById('localizedDescriptions');
const nameRuInput = document.getElementById('edit_name_ru');
const nameLvInput = document.getElementById('edit_name_lv');
const descRuInput = document.getElementById('edit_desc_ru');
const descLvInput = document.getElementById('edit_desc_lv');
document.addEventListener('DOMContentLoaded', () => {
    if (window.bootstrap) {
        Array.from(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]')).forEach(el => new bootstrap.Tooltip(el));
    }
});

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

function syncCategoryFields(type = null, customValue = null) {
    if (!categorySelect || !categoryCustomWrapper || !categoryCustomInput) {
        return;
    }
    const selected = type ?? categorySelect.value;
    categorySelect.value = selected;
    const showCustom = selected === 'custom';
    categoryCustomWrapper.classList.toggle('d-none', !showCustom);
    if (showCustom && customValue !== null && customValue !== undefined) {
        categoryCustomInput.value = customValue;
    }
    if (!showCustom) {
        categoryCustomInput.value = '';
    }
}

categorySelect?.addEventListener('change', () => syncCategoryFields());
syncCategoryFields(categorySelect?.value || 'main', '');

function toggleAllergens(visible) {
    if (!allergensWrapper || !allergensInput || !allergensToggle) return;
    allergensWrapper.classList.toggle('d-none', !visible);
    if (!visible) {
        allergensInput.value = '';
    }
}

allergensToggle?.addEventListener('change', () => toggleAllergens(allergensToggle.checked));

toggleLocalizedNames?.addEventListener('change', () => {
    if (!localizedNames) return;
    localizedNames.classList.toggle('d-none', !toggleLocalizedNames.checked);
    if (!toggleLocalizedNames.checked) {
        if (nameRuInput) nameRuInput.value = '';
        if (nameLvInput) nameLvInput.value = '';
    }
});

toggleLocalizedDesc?.addEventListener('change', () => {
    if (!localizedDescriptions) return;
    localizedDescriptions.classList.toggle('d-none', !toggleLocalizedDesc.checked);
    if (!toggleLocalizedDesc.checked) {
        if (descRuInput) descRuInput.value = '';
        if (descLvInput) descLvInput.value = '';
    }
});

function fillForm(data) {
    document.getElementById('edit_id').value = data.id || '';
    document.getElementById('edit_title').value = data.name_original || data.title || '';
    document.getElementById('edit_desc').value = data.description_original || data.description || '';
    document.getElementById('edit_ingr').value = data.ingredients_original || data.ingredients || '';
    if (toggleLocalizedNames && localizedNames) {
        const hasNames = Boolean((data.name_ru ?? '') || (data.name_lv ?? ''));
        toggleLocalizedNames.checked = hasNames;
        localizedNames.classList.toggle('d-none', !hasNames);
        if (nameRuInput) nameRuInput.value = data.name_ru ?? '';
        if (nameLvInput) nameLvInput.value = data.name_lv ?? '';
    }
    if (toggleLocalizedDesc && localizedDescriptions) {
        const hasDesc = Boolean((data.description_ru ?? '') || (data.description_lv ?? ''));
        toggleLocalizedDesc.checked = hasDesc;
        localizedDescriptions.classList.toggle('d-none', !hasDesc);
        if (descRuInput) descRuInput.value = data.description_ru ?? '';
        if (descLvInput) descLvInput.value = data.description_lv ?? '';
    }
    const allergens = data.allergens || '';
    if (allergensToggle) {
        allergensToggle.checked = allergens !== '';
        toggleAllergens(allergens !== '');
        if (allergensInput) {
            allergensInput.value = allergens;
        }
    }
    document.getElementById('current_image').value = data.image || '';
    document.getElementById('existing_gallery').value = JSON.stringify(data.gallery || []);
    syncManualPriceField(Boolean(data.use_manual_price));
    if (manualPriceInput && data.use_manual_price) {
        manualPriceInput.value = data.price || '';
    }
    const categoryOriginal = data.category_original || data.category || '';
    const role = data.category_role || 'main';
    syncCategoryFields(role, role === 'custom' ? categoryOriginal : '');
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
