<?php $title = 'Doctor Gorilka — ' . translate('auth.register.title'); ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card" style="max-width: 460px; width: 100%; padding: 20px;">
        <h2 class="mb-2"><?= htmlspecialchars(translate('auth.register.heading')) ?></h2>
        <p><?= htmlspecialchars(translate('auth.register.description')) ?> <a href="/login<?= !empty($next) ? '?next=' . urlencode($next) : '' ?>"><?= htmlspecialchars(translate('auth.register.login_link')) ?></a></p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger my-2"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="/register" class="d-flex flex-column gap-3 mt-3">
            <?= csrf_field() ?>
            <div class="d-flex gap-2 flex-column flex-md-row">
                <div class="flex-grow-1">
                    <label class="form-label"><?= htmlspecialchars(translate('auth.register.first_name')) ?></label>
                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($inputs['first_name'] ?? '') ?>" required maxlength="50">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label"><?= htmlspecialchars(translate('auth.register.last_name')) ?></label>
                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($inputs['last_name'] ?? '') ?>" required maxlength="50">
                </div>
            </div>

            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.register.phone')) ?></label>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($inputs['phone'] ?? '') ?>" required placeholder="+375 (29) 123-45-67" maxlength="30">
            </div>

            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.register.username')) ?></label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($inputs['username'] ?? '') ?>" required maxlength="60">
            </div>

            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.register.password')) ?></label>
                <input type="password" class="form-control" name="password" required maxlength="128">
            </div>

            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.register.password_confirm')) ?></label>
                <input type="password" class="form-control" name="confirm" required maxlength="128">
            </div>

            <?php if (!empty($next)): ?>
                <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-success"><?= htmlspecialchars(translate('auth.register.submit')) ?></button>
        </form>
    </div>
</div>
