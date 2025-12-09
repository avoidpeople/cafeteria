<?php $title = 'Doctor Gorilka — Профиль пользователя'; ?>
<div class="page-container">
    <h1>Профиль пользователя</h1>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Основные данные</h5>
                    <p class="mb-2"><strong>Логин:</strong> <?= htmlspecialchars($user->username) ?></p>
                    <p class="mb-2"><strong>Имя:</strong> <?= htmlspecialchars($user->firstName ?? '') ?></p>
                    <p class="mb-2"><strong>Фамилия:</strong> <?= htmlspecialchars($user->lastName ?? '') ?></p>
                    <p class="mb-2"><strong>Телефон:</strong> <?= htmlspecialchars($user->phone ?? 'Не указан') ?></p>
                    <?php if ($user->role === 'admin'): ?>
                        <p class="mb-2"><strong>Роль:</strong> Администратор</p>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex flex-column gap-2">
                        <div class="stat-card">
                            <div class="stat-label">Кол-во заказов</div>
                            <div class="stat-value"><?= $ordersCount ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Всего потрачено</div>
                            <div class="stat-value"><?= number_format($totalSpent, 2, '.', ' ') ?> €</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Смена пароля</h5>
                    <?php if (!empty($passwordSuccess)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($passwordSuccess) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($passwordErrors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($passwordErrors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="/profile/password" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div>
                            <label class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div>
                            <label class="form-label">Подтвердите новый пароль</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-success">Обновить пароль</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
