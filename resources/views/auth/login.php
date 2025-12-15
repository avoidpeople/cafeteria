<?php $title = 'Doctor Gorilka — ' . translate('auth.login.title'); ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card" style="max-width: 420px; width: 100%; padding: 20px;">
        <h2 class="mb-3"><?= htmlspecialchars(translate('auth.login.heading')) ?></h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" class="d-flex flex-column gap-3">
            <?= csrf_field() ?>
            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.login.username')) ?></label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($usernameValue) ?>" required>
            </div>
            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.login.password')) ?></label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <?php if (!empty($next)): ?>
                <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(translate('auth.login.submit')) ?></button>
        </form>

        <a href="/" class="btn btn-outline-secondary w-100 mt-3"><?= htmlspecialchars(translate('auth.login.cancel')) ?></a>
        <p class="mt-3 mb-0"><?= htmlspecialchars(translate('auth.login.no_account')) ?>
            <a href="/register<?= !empty($next) ? '?next=' . urlencode($next) : '' ?>"><?= htmlspecialchars(translate('auth.login.register_link')) ?></a>
        </p>
    </div>
</div>
