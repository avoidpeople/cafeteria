<div class="page-container">
<div class="hero-card mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold">Свежие блюда каждый день</h1>
            <p class="lead mb-4">Выбирайте любимые позиции, оформляйте заказы онлайн и отслеживайте их статус в личном кабинете.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="/orders" class="btn btn-light btn-lg text-primary">Мои заказы</a>
                <a href="/cart" class="btn btn-outline-light btn-lg text-white border-white">Перейти в корзину</a>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="badge bg-light text-dark fs-6">Сегодня доступно: <?= count($menuItems) ?> блюд(а)</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
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
                <div class="card h-100 border-0 shadow-sm menu-card" data-item='<?= json_encode([
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description ?? '',
                    'ingredients' => $item->ingredients ?? '',
                    'category' => $item->category,
                    'price' => number_format($item->price, 2, '.', ' '),
                    'gallery' => $gallery,
                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
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
                            <span class="fs-5 fw-bold text-success"><?= number_format($item->price, 2, '.', ' ') ?> €</span>
                            <button class="btn btn-primary add-to-cart" data-id="<?= $item->id ?>" onclick="event.stopPropagation();">В корзину</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>

<?php include __DIR__ . '/partials/menu_modal.php'; ?>
<?php include __DIR__ . '/partials/menu_scripts.php'; ?>
