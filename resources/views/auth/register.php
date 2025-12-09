<?php $title = 'Doctor Gorilka — Регистрация'; ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card" style="max-width: 460px; width: 100%; padding: 20px;">
        <h2 class="mb-2">Регистрация</h2>
        <p>Уже есть аккаунт? <a href="/login">Войдите</a></p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger my-2"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="/register" class="d-flex flex-column gap-3 mt-3">
            <div class="d-flex gap-2 flex-column flex-md-row">
                <div class="flex-grow-1">
                    <label class="form-label">Имя</label>
                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($inputs['first_name'] ?? '') ?>" required>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label">Фамилия</label>
                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($inputs['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div>
                <label class="form-label">Номер телефона</label>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($inputs['phone'] ?? '') ?>" required placeholder="+375 (29) 123-45-67">
            </div>

            <div>
                <label class="form-label">Логин</label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($inputs['username'] ?? '') ?>" required>
            </div>

            <div>
                <label class="form-label">Пароль</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div>
                <label class="form-label">Повторите пароль</label>
                <input type="password" class="form-control" name="confirm" required>
            </div>

            <button type="submit" class="btn btn-success">Зарегистрироваться</button>
        </form>
    </div>
</div>
