<?php $title = 'Doctor Gorilka — ' . translate('profile.title'); ?>
<div class="page-container">
    <h1><?= htmlspecialchars(translate('profile.title')) ?></h1>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= htmlspecialchars(translate('profile.section.info')) ?></h5>
                    <p class="mb-2"><strong><?= htmlspecialchars(translate('profile.username')) ?>:</strong> <?= htmlspecialchars($user->username) ?></p>
                    <p class="mb-2"><strong><?= htmlspecialchars(translate('profile.first_name')) ?>:</strong> <?= htmlspecialchars($user->firstName ?? '') ?></p>
                    <p class="mb-2"><strong><?= htmlspecialchars(translate('profile.last_name')) ?>:</strong> <?= htmlspecialchars($user->lastName ?? '') ?></p>
                    <p class="mb-2"><strong><?= htmlspecialchars(translate('profile.phone')) ?>:</strong> <?= htmlspecialchars($user->phone ?? translate('profile.phone_missing')) ?></p>
                    <?php if ($user->role === 'admin'): ?>
                        <p class="mb-2"><strong><?= htmlspecialchars(translate('profile.role_label')) ?></strong> <?= htmlspecialchars(translate('profile.role_admin')) ?></p>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex flex-column gap-2">
                        <div class="stat-card">
                            <div class="stat-label"><?= htmlspecialchars(translate('profile.stat.orders')) ?></div>
                            <div class="stat-value"><?= $ordersCount ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label"><?= htmlspecialchars(translate('profile.stat.spent')) ?></div>
                            <div class="stat-value"><?= number_format($totalSpent, 2, '.', ' ') ?> €</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= htmlspecialchars(translate('profile.section.password')) ?></h5>
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
                            <label class="form-label"><?= htmlspecialchars(translate('profile.password.current')) ?></label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div>
                            <label class="form-label"><?= htmlspecialchars(translate('profile.password.new')) ?></label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div>
                            <label class="form-label"><?= htmlspecialchars(translate('profile.password.confirm')) ?></label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-success"><?= htmlspecialchars(translate('profile.password.submit')) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
