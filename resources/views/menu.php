<?php
$comboOptions = $comboOptions ?? ['main' => [], 'soup' => [], 'roles' => []];
$comboRoles = $comboOptions['roles'] ?? [];
$comboMainCount = count($comboOptions['main'] ?? []);
$comboSoupCount = count($comboOptions['soup'] ?? []);
?>
<div class="page-container menu-page">
<div class="hero-card mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold">Свежие блюда каждый день</h1>
            <p class="lead mb-4">Выбирайте любимые позиции, оформляйте заказы онлайн и отслеживайте их статус в личном кабинете.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="/orders" class="btn btn-light btn-lg text-primary">Мои заказы</a>
                <a href="/cart" class="btn btn-outline-light btn-lg text-white border-white">Перейти в корзину</a>
            </div>
            <div class="menu-hero-meta mt-4">
                <div class="menu-hero-metric">
                    <span class="label">Блюд сегодня</span>
                    <span class="value"><?= count($menuItems) ?></span>
                </div>
                <div class="menu-hero-metric">
                    <span class="label">Горячих</span>
                    <span class="value"><?= $comboMainCount ?></span>
                </div>
                <div class="menu-hero-metric">
                    <span class="label">Супов</span>
                    <span class="value"><?= $comboSoupCount ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="badge bg-light text-dark fs-6">Сегодня доступно: <?= count($menuItems) ?> блюд(а)</div>
        </div>
    </div>
</div>

<div class="combo-hero mb-4">
    <div class="combo-hero__content">
        <p class="text-uppercase text-muted small mb-2">Комплексный обед</p>
        <h2 class="mb-2">Соберите Комплексный обед от 4 €</h2>
        <p class="mb-3 text-muted">Выберите горячее и при желании суп. Мы зафиксируем цену и добавим весь набор в корзину одной позицией.</p>
        <div class="combo-hero__badges mb-3">
            <span class="combo-hero__badge">1. Горячее блюдо</span>
            <span class="combo-hero__badge">2. Суп по желанию</span>
            <span class="combo-hero__badge">3. Одна позиция в корзине</span>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-gradient btn-lg" id="comboBuilderButton" type="button">Создать комплекс</button>
            <button class="btn btn-outline-light btn-lg" type="button" id="comboBuilderReset" hidden>Выйти из режима</button>
        </div>
        <div class="combo-hero__note text-muted">Режим конструктора можно выключить в любой момент — корзина сохранит текущий набор.</div>
    </div>
    <div class="combo-hero__price">
        <div class="combo-price-pill">4 € <span class="text-muted">без супа</span></div>
        <div class="combo-price-pill">4.5 € <span class="text-muted">с супом</span></div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 menu-filters">
    <div class="card-body">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-sm-6 col-md-5">
                <label class="form-label text-muted mb-1">Поиск</label>
                <input type="text" class="form-control" name="search" placeholder="Название или описание" value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="col-sm-6 col-md-3">
                <label class="form-label text-muted mb-1">Категория</label>
                <select name="category" class="form-select">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2 mt-3 mt-md-4">
                <button type="submit" class="btn btn-primary flex-grow-1">Применить</button>
                <a href="/menu" class="btn btn-outline-secondary flex-grow-1">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($menuItems)): ?>
    <div class="empty-state">
        <?php if ($search !== '' || $selectedCategory !== ''): ?>
            По вашему запросу ничего не найдено. Попробуйте изменить фильтры.
        <?php else: ?>
            Меню на сегодня пока не сформировано. Загляните позже.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
        <?php foreach ($menuItems as $item): ?>
            <?php $gallery = $item->galleryImages(); ?>
            <div class="col">
                <?php $role = $comboRoles[$item->id] ?? 'main'; ?>
                <div class="card h-100 border-0 shadow-sm menu-card" data-item='<?= json_encode([
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description ?? '',
                    'ingredients' => $item->ingredients ?? '',
                    'category' => $item->category,
                    'price' => number_format($item->price, 2, '.', ' '),
                    'gallery' => $gallery,
                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>' data-combo-role="<?= htmlspecialchars($role) ?>">
                <?php if (!empty($gallery[0])): ?>
                    <img src="/assets/images/<?= $gallery[0] ?>" class="card-img-top" alt="<?= htmlspecialchars($item->title) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/400x220?text=Нет+фото" class="card-img-top" alt="no image">
                <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <div class="small text-muted mb-2"><?= htmlspecialchars($item->category ?? 'Без категории') ?></div>
                        <h5 class="card-title"><?= htmlspecialchars($item->title) ?></h5>
                        <p class="card-text flex-grow-1 text-muted fst-italic">Нажмите, чтобы увидеть фото и описание</p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fs-5 fw-bold text-success menu-price"><?= number_format($item->price, 2, '.', ' ') ?> €</span>
                            <button type="button" class="btn btn-outline-light combo-select-btn" data-id="<?= $item->id ?>" data-default-text="Добавить в комплекс" data-combo-role="<?= htmlspecialchars($role) ?>" onclick="event.stopPropagation();">Добавить в комплекс</button>
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
            <p class="text-uppercase text-muted small mb-1">Комплексный обед · 4.5 €</p>
            <h5 class="modal-title">Соберите персональный набор</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="combo-step">
                    <div class="combo-step-head">
                        <div>
                            <h6 class="mb-0">1. Горячее <span class="text-danger">*</span></h6>
                            <small class="text-muted">Выберите одно блюдо</small>
                        </div>
                    </div>
                    <?php if (!empty($comboOptions['main'])): ?>
                        <div class="combo-option-grid" id="comboMainOptions">
                            <?php foreach ($comboOptions['main'] as $option): ?>
                                <button type="button" class="combo-option-card" data-role="main" data-id="<?= $option['id'] ?>" data-title="<?= htmlspecialchars($option['title']) ?>" data-description="<?= htmlspecialchars($option['description'] ?? '') ?>" data-image="<?= htmlspecialchars($option['image'] ?? '') ?>">
                                    <div class="combo-option-thumb">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="/assets/images/<?= htmlspecialchars($option['image']) ?>" alt="<?= htmlspecialchars($option['title']) ?>">
                                        <?php else: ?>
                                            <span>Нет фото</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="combo-option-body">
                                        <div class="combo-option-title"><?= htmlspecialchars($option['title']) ?></div>
                                        <div class="combo-option-desc text-truncate-2">
                                            <?= htmlspecialchars($option['description'] ?? 'Описание появится позже') ?>
                                        </div>
                                        <span class="combo-option-tag">Горячее</span>
                                    </div>
                                    <span class="combo-option-check"></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Пока нет горячих блюд в меню.</p>
                    <?php endif; ?>
                </div>

                <div class="combo-step mt-4">
                    <div class="combo-step-head">
                        <div>
                            <h6 class="mb-0">2. Суп <span class="text-muted">(опционально)</span></h6>
                            <small class="text-muted">Можно пропустить</small>
                        </div>
                    </div>
                    <div class="combo-option-grid" id="comboSoupOptions">
                        <button type="button" class="combo-option-card" data-role="soup" data-id="" data-title="Без супа" data-description="" data-image="">
                            <div class="combo-option-thumb">
                                <span>—</span>
                            </div>
                            <div class="combo-option-body">
                                <div class="combo-option-title">Без супа</div>
                                <div class="combo-option-desc">Добавить только горячее блюдо</div>
                                <span class="combo-option-tag">Пропустить</span>
                            </div>
                            <span class="combo-option-check"></span>
                        </button>
                        <?php if (!empty($comboOptions['soup'])): ?>
                            <?php foreach ($comboOptions['soup'] as $option): ?>
                                <button type="button" class="combo-option-card" data-role="soup" data-id="<?= $option['id'] ?>" data-title="<?= htmlspecialchars($option['title']) ?>" data-description="<?= htmlspecialchars($option['description'] ?? '') ?>" data-image="<?= htmlspecialchars($option['image'] ?? '') ?>">
                                    <div class="combo-option-thumb">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="/assets/images/<?= htmlspecialchars($option['image']) ?>" alt="<?= htmlspecialchars($option['title']) ?>">
                                        <?php else: ?>
                                            <span>Нет фото</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="combo-option-body">
                                        <div class="combo-option-title"><?= htmlspecialchars($option['title']) ?></div>
                                        <div class="combo-option-desc text-truncate-2">
                                            <?= htmlspecialchars($option['description'] ?? 'Описание появится позже') ?>
                                        </div>
                                        <span class="combo-option-tag">Суп</span>
                                    </div>
                                    <span class="combo-option-check"></span>
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted small">Супы недоступны сегодня</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="combo-summary">
                    <h6 class="text-uppercase text-muted mb-3">Итог набора</h6>
                    <div class="combo-selection-list" id="comboSelectionPreview">
                        <div class="text-muted">Выберите горячее блюдо, чтобы начать</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <div class="text-muted small">Стоимость</div>
                            <div class="combo-price" id="comboPriceValue">4.00 €</div>
                        </div>
                        <div class="text-end text-muted small" id="comboPriceHint">Суп добавит 0.5 €</div>
                    </div>
                    <button type="button" class="btn btn-gradient w-100 mt-3" id="comboSubmit" disabled>Добавить комплексный обед</button>
                    <div class="text-danger small mt-2 d-none" id="comboError"></div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/menu_scripts.php'; ?>
