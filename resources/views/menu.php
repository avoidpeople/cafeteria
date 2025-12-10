<?php
$comboOptions = $comboOptions ?? ['categories' => [], 'roles' => [], 'counts' => []];
$comboCategories = $comboOptions['categories'] ?? [];
$comboRoles = $comboOptions['roles'] ?? [];
$comboCounts = $comboOptions['counts'] ?? ['main' => 0, 'garnish' => 0, 'soup' => 0];
$comboMainCount = $comboCounts['main'] ?? 0;
$comboGarnishCount = $comboCounts['garnish'] ?? 0;
$comboSoupCount = $comboCounts['soup'] ?? 0;
$comboBasePrice = $comboOptions['base_price'] ?? 4.0;
$comboBasePriceFormatted = number_format($comboBasePrice, 2, '.', ' ');
$title = 'Doctor Gorilka — ' . translate('nav.menu');
$locale = currentLocale();
$localizedFieldMap = [
    'ru' => ['name' => 'nameRu', 'description' => 'descriptionRu', 'category' => 'categoryRu'],
    'lv' => ['name' => 'nameLv', 'description' => 'descriptionLv', 'category' => 'categoryLv'],
];
$activeLocalizedFields = $localizedFieldMap[$locale] ?? ['name' => 'nameOriginal', 'description' => 'descriptionOriginal', 'category' => 'categoryOriginal'];
?>
<div class="page-container menu-page">
<div class="hero-card mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold"><?= htmlspecialchars(translate('menu.hero.title')) ?></h1>
            <p class="lead mb-4"><?= htmlspecialchars(translate('menu.hero.subtitle')) ?></p>
            <div class="d-flex flex-wrap gap-2">
                <a href="/orders" class="btn btn-light btn-lg text-primary"><?= htmlspecialchars(translate('menu.hero.orders_btn')) ?></a>
                <a href="/cart" class="btn btn-outline-light btn-lg text-white border-white"><?= htmlspecialchars(translate('menu.hero.cart_btn')) ?></a>
            </div>
            <div class="menu-hero-meta mt-4">
                <div class="menu-hero-metric">
                    <span class="label"><?= htmlspecialchars(translate('menu.hero.metric_today')) ?></span>
                    <span class="value"><?= count($menuItems) ?></span>
                </div>
                <div class="menu-hero-metric">
                    <span class="label"><?= htmlspecialchars(translate('menu.hero.metric_main')) ?></span>
                    <span class="value"><?= $comboMainCount ?></span>
                </div>
                <div class="menu-hero-metric">
                    <span class="label"><?= htmlspecialchars(translate('menu.hero.metric_garnish')) ?></span>
                    <span class="value"><?= $comboGarnishCount ?></span>
                </div>
                <div class="menu-hero-metric">
                    <span class="label"><?= htmlspecialchars(translate('menu.hero.metric_soups')) ?></span>
                    <span class="value"><?= $comboSoupCount ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="badge bg-light text-dark fs-6"><?= htmlspecialchars(translate('menu.hero.available_badge', ['count' => count($menuItems)])) ?></div>
        </div>
    </div>
</div>

<div class="combo-hero mb-4">
    <div class="combo-hero__content">
        <p class="text-uppercase text-muted small mb-2"><?= htmlspecialchars(translate('menu.combo.tagline')) ?></p>
        <h2 class="mb-2"><?= htmlspecialchars(translate('menu.combo.title')) ?></h2>
        <p class="mb-3 text-muted"><?= htmlspecialchars(translate('menu.combo.description')) ?></p>
        <div class="combo-hero__badges mb-3">
            <span class="combo-hero__badge"><?= htmlspecialchars(translate('menu.combo.badge_main')) ?></span>
            <span class="combo-hero__badge"><?= htmlspecialchars(translate('menu.combo.badge_garnish')) ?></span>
            <span class="combo-hero__badge"><?= htmlspecialchars(translate('menu.combo.badge_optional')) ?></span>
            <span class="combo-hero__badge"><?= htmlspecialchars(translate('menu.combo.badge_cart')) ?></span>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-gradient btn-lg" id="comboBuilderButton" type="button"><?= htmlspecialchars(translate('menu.combo.create_btn')) ?></button>
            <button class="btn btn-outline-light btn-lg" type="button" id="comboBuilderReset" hidden><?= htmlspecialchars(translate('menu.combo.exit_btn')) ?></button>
        </div>
        <div class="combo-hero__note text-muted"><?= htmlspecialchars(translate('menu.combo.note')) ?></div>
    </div>
    <div class="combo-hero__price">
        <div class="combo-price-pill"><?= htmlspecialchars(translate('menu.combo.price_base', ['price' => $comboBasePriceFormatted])) ?></div>
        <div class="combo-price-pill"><?= htmlspecialchars(translate('menu.combo.price_extras')) ?></div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 menu-filters">
    <div class="card-body">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-sm-6 col-md-5">
                <label class="form-label text-muted mb-1"><?= htmlspecialchars(translate('menu.filters.search_label')) ?></label>
                <input type="text" class="form-control" name="search" placeholder="<?= htmlspecialchars(translate('menu.filters.search_placeholder')) ?>" value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="col-sm-6 col-md-3">
                <label class="form-label text-muted mb-1"><?= htmlspecialchars(translate('menu.filters.category_label')) ?></label>
                <select name="category" class="form-select">
                    <option value=""><?= htmlspecialchars(translate('menu.filters.category_all')) ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2 mt-3 mt-md-4">
                <button type="submit" class="btn btn-primary flex-grow-1"><?= htmlspecialchars(translate('menu.filters.submit')) ?></button>
                <a href="/menu" class="btn btn-outline-secondary flex-grow-1"><?= htmlspecialchars(translate('menu.filters.reset')) ?></a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($menuItems)): ?>
    <div class="empty-state">
        <?php if ($search !== '' || $selectedCategory !== ''): ?>
            <?= htmlspecialchars(translate('menu.empty.filtered')) ?>
        <?php else: ?>
            <?= htmlspecialchars(translate('menu.empty.default')) ?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
        <?php foreach ($menuItems as $item): ?>
            <?php $gallery = $item->galleryImages(); $isUnique = $item->isUnique(); ?>
            <?php
                $nameField = $activeLocalizedFields['name'];
                $descriptionField = $activeLocalizedFields['description'];
                $categoryField = $activeLocalizedFields['category'];
                $localizedName = $item->$nameField ?? $item->nameOriginal ?? $item->title;
                $localizedDescription = $item->$descriptionField ?? $item->descriptionOriginal ?? $item->description ?? translate('common.no_description');
                $localizedCategory = $item->$categoryField ?? $item->categoryOriginal ?? translate('menu.card.no_category');
                $localizedIngredients = $item->ingredients ?? $item->ingredientsOriginal ?? '';
                $roleKey = $comboRoles[$item->id] ?? null;
            ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm menu-card" data-item='<?= json_encode([
                    'id' => $item->id,
                    'title' => $localizedName,
                    'description' => $localizedDescription ?? '',
                    'ingredients' => $localizedIngredients,
                    'category' => $localizedCategory,
                    'price' => number_format($item->price, 2, '.', ' '),
                    'raw_price' => (float)$item->price,
                    'is_unique' => $isUnique,
                    'gallery' => $gallery,
                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>' data-combo-role="<?= htmlspecialchars($roleKey ?? '') ?>">
                <?php if (!empty($gallery[0])): ?>
                    <img src="/assets/images/<?= $gallery[0] ?>" class="card-img-top" alt="<?= htmlspecialchars($localizedName) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/400x220?text=<?= urlencode(translate('common.no_photo')) ?>" class="card-img-top" alt="<?= htmlspecialchars(translate('common.no_photo')) ?>">
                <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <div class="small text-muted mb-2"><?= htmlspecialchars($localizedCategory) ?></div>
                        <h5 class="card-title d-flex align-items-center gap-2">
                            <?= htmlspecialchars($localizedName) ?>
                            <?php if ($isUnique): ?>
                                <span class="unique-chip" title="<?= htmlspecialchars(translate('menu.card.unique_badge')) ?>">★</span>
                            <?php endif; ?>
                        </h5>
                        <p class="card-text flex-grow-1 text-muted fst-italic"><?= htmlspecialchars(translate('menu.card.click_hint')) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <?php if ($isUnique): ?>
                                <span class="fs-5 fw-bold text-info menu-price"><?= number_format($item->price, 2, '.', ' ') ?> €</span>
                            <?php else: ?>
                                <span class="menu-price menu-price--placeholder text-muted"><?= htmlspecialchars(translate('menu.card.price_included')) ?></span>
                            <?php endif; ?>
                            <?php if ($roleKey): ?>
                                <button type="button" class="btn btn-outline-light combo-select-btn" data-id="<?= $item->id ?>" data-default-text="<?= htmlspecialchars(translate('menu.card.button_default')) ?>" data-combo-role="<?= htmlspecialchars($roleKey) ?>" onclick="event.stopPropagation();"><?= htmlspecialchars(translate('menu.card.button_default')) ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>

<?php include __DIR__ . '/partials/menu_modal.php'; ?>
<div class="modal fade" id="comboModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
            <p class="text-uppercase text-muted small mb-1"><?= htmlspecialchars(translate('menu.combo_modal.tagline')) ?></p>
            <h5 class="modal-title"><?= htmlspecialchars(translate('menu.combo_modal.title')) ?></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
            <div class="col-lg-7" id="comboCategories">
                <?php foreach ($comboCategories as $index => $category): ?>
                    <div class="combo-step <?= $index > 0 ? 'mt-4' : '' ?>" data-category="<?= htmlspecialchars($category['key']) ?>">
                        <div class="combo-step-head">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($category['label']) ?><?= $category['required'] ? ' *' : '' ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($category['hint']) ?></small>
                            </div>
                        </div>
                        <?php if (!empty($category['items'])): ?>
                            <div class="combo-option-grid" data-category-grid="<?= htmlspecialchars($category['key']) ?>">
                                <?php if (!$category['required'] && !empty($category['skip'])): ?>
                                    <button type="button" class="combo-option-card combo-option-card--skip" data-category="<?= htmlspecialchars($category['key']) ?>" data-id="">
                                        <div class="combo-option-thumb">—</div>
                                        <div class="combo-option-body">
                                            <div class="combo-option-title"><?= htmlspecialchars($category['skip']['title']) ?></div>
                                            <div class="combo-option-desc"><?= htmlspecialchars($category['skip']['description']) ?></div>
                                            <span class="combo-option-tag"><?= htmlspecialchars($category['skip']['tag']) ?></span>
                                        </div>
                                        <span class="combo-option-check"></span>
                                    </button>
                                <?php endif; ?>
                                <?php foreach ($category['items'] as $option): ?>
                                    <button type="button" class="combo-option-card" data-category="<?= htmlspecialchars($category['key']) ?>" data-id="<?= $option['id'] ?>" data-title="<?= htmlspecialchars($option['title']) ?>" data-description="<?= htmlspecialchars($option['description'] ?? '') ?>" data-image="<?= htmlspecialchars($option['image'] ?? '') ?>" data-price="<?= htmlspecialchars((string)($option['price'] ?? 0)) ?>" data-custom-price="<?= htmlspecialchars((string)($option['custom_price'] ?? '')) ?>" data-unique="<?= !empty($option['unique']) ? '1' : '0' ?>">
                                        <div class="combo-option-thumb">
                                            <?php if (!empty($option['image'])): ?>
                                                <img src="/assets/images/<?= htmlspecialchars($option['image']) ?>" alt="<?= htmlspecialchars($option['title']) ?>">
                                            <?php else: ?>
                                                <span><?= htmlspecialchars(translate('common.no_photo')) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="combo-option-body">
                                            <div class="combo-option-title"><?= htmlspecialchars($option['title']) ?></div>
                                            <div class="combo-option-desc text-truncate-2">
                                                <?= htmlspecialchars($option['description'] ?? translate('menu.combo_modal.description_pending')) ?>
                                            </div>
                                            <span class="combo-option-tag"><?= htmlspecialchars($category['label']) ?></span>
                                            <div class="combo-option-extra">
                                                <?php if (!empty($option['price'])): ?>
                                                    <?php if (!empty($category['required'])): ?>
                                                        <span class="combo-option-unique"><?= number_format((float)$option['price'], 2, '.', ' ') ?> €</span>
                                                    <?php else: ?>
                                                        <span class="combo-option-unique">+<?= number_format((float)$option['price'], 2, '.', ' ') ?> €</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="combo-option-regular"><?= htmlspecialchars(translate('combo.category.free')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="combo-option-check"></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small"><?= htmlspecialchars(translate('combo.category.empty')) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-lg-5">
                <div class="combo-summary">
                    <h6 class="text-uppercase text-muted mb-3"><?= htmlspecialchars(translate('menu.combo_modal.summary_title')) ?></h6>
                    <div class="combo-selection-list" id="comboSelectionPreview">
                        <div class="text-muted"><?= htmlspecialchars(translate('menu.combo_modal.summary_placeholder')) ?></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <div class="text-muted small"><?= htmlspecialchars(translate('menu.combo_modal.summary_cost_label')) ?></div>
                            <div class="combo-price" id="comboPriceValue"><?= number_format($comboBasePrice, 2, '.', ' ') ?> €</div>
                        </div>
                        <div class="text-end text-muted small" id="comboPriceHint"><?= htmlspecialchars(translate('menu.combo_modal.summary_hint')) ?></div>
                    </div>
                    <button type="button" class="btn btn-gradient w-100 mt-3" id="comboSubmit" disabled><?= htmlspecialchars(translate('menu.combo_modal.submit')) ?></button>
                    <div class="text-danger small mt-2 d-none" id="comboError"></div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="application/json" id="menuTranslations"><?= json_encode([
    'description_missing' => translate('menu.js.description_missing'),
    'ingredients_prefix' => translate('menu.js.ingredients_prefix'),
    'category_prefix' => translate('menu.js.category_prefix'),
    'category_none' => translate('menu.js.category_none'),
    'included_price' => translate('menu.js.included_price'),
    'no_photo' => translate('menu.js.no_photo'),
    'slide_label' => translate('menu.js.slide_label'),
    'summary_pick_main' => translate('menu.js.summary_pick_main'),
    'adding' => translate('menu.js.adding'),
    'toast_added' => translate('menu.js.toast_added'),
    'error_add_combo' => translate('menu.js.error_add_combo'),
    'toast_failed' => translate('menu.js.toast_failed'),
    'error_save' => translate('menu.js.error_save'),
    'button_selected' => translate('menu.js.button_selected'),
    'button_add' => translate('menu.js.button_add'),
    'label_main' => translate('menu.js.label_main'),
    'label_soup' => translate('menu.js.label_soup'),
    'label_no_soup' => translate('menu.js.label_no_soup'),
    'hint_main_unique' => translate('menu.js.hint_main_unique'),
    'hint_main_standard' => translate('menu.js.hint_main_standard'),
    'hint_soup_unique' => translate('menu.js.hint_soup_unique'),
    'hint_soup_standard' => translate('menu.js.hint_soup_standard'),
    'default_dish' => translate('menu.js.default_dish'),
    'description_pending' => translate('menu.js.description_pending'),
    'unique_badge' => translate('menu.card.unique_badge'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="comboConfig"><?= json_encode([
    'base_price' => $comboBasePrice,
    'categories' => $comboCategories,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php include __DIR__ . '/partials/menu_scripts.php'; ?>
