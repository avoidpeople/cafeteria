<?php $title = 'Вход'; ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card" style="max-width: 420px; width: 100%; padding: 20px;">
        <h2 class="mb-3">Авторизация</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" class="d-flex flex-column gap-3">
            <div>
                <label class="form-label">Логин</label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($usernameValue) ?>" required>
            </div>
            <div>
                <label class="form-label">Пароль</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Войти</button>
        </form>

        <a href="/" class="btn btn-outline-secondary w-100 mt-3">Отмена и на главную</a>
        <p class="mt-3 mb-0">Нет аккаунта? <a href="/register">Зарегистрируйтесь</a></p>
    </div>
</div>
