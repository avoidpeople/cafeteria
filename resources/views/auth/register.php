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
                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($inputs['first_name'] ?? '') ?>" required maxlength="20">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label"><?= htmlspecialchars(translate('auth.register.last_name')) ?></label>
                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($inputs['last_name'] ?? '') ?>" required maxlength="20">
                </div>
            </div>

            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.register.phone')) ?></label>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($inputs['phone'] ?? '') ?>" required placeholder="+375 (29) 123-45-67" maxlength="30">
            </div>

            <div>
                <label class="form-label"><?= htmlspecialchars(translate('auth.register.username')) ?></label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($inputs['username'] ?? '') ?>" required maxlength="20">
            </div>

            <div>
                <label class="form-label" for="passwordField"><?= htmlspecialchars(translate('auth.register.password')) ?></label>
                <div class="password-field position-relative">
                    <input type="password" class="form-control pe-5" id="passwordField" name="password" required maxlength="128" autocomplete="new-password">
                    <button type="button" class="password-toggle" aria-label="<?= htmlspecialchars(translate('auth.register.password_toggle_show')) ?>" data-password-toggle="passwordField" aria-pressed="false" data-visible="false">
                        <span class="password-toggle__icon" aria-hidden="true"></span>
                        <span class="visually-hidden"><?= htmlspecialchars(translate('auth.register.password_toggle_show')) ?></span>
                    </button>
                </div>
                <div class="password-requirements mt-2 is-hidden" id="passwordRequirements" aria-live="polite">
                    <div class="password-requirements__item invalid" data-password-rule="length">
                        <span class="password-requirements__icon" aria-hidden="true"></span>
                        <span><?= htmlspecialchars(translate('auth.register.requirements.length')) ?></span>
                    </div>
                    <div class="password-requirements__item invalid" data-password-rule="uppercase">
                        <span class="password-requirements__icon" aria-hidden="true"></span>
                        <span><?= htmlspecialchars(translate('auth.register.requirements.uppercase')) ?></span>
                    </div>
                    <div class="password-requirements__item invalid" data-password-rule="lowercase">
                        <span class="password-requirements__icon" aria-hidden="true"></span>
                        <span><?= htmlspecialchars(translate('auth.register.requirements.lowercase')) ?></span>
                    </div>
                    <div class="password-requirements__item invalid" data-password-rule="digit">
                        <span class="password-requirements__icon" aria-hidden="true"></span>
                        <span><?= htmlspecialchars(translate('auth.register.requirements.digit')) ?></span>
                    </div>
                    <div class="password-requirements__item invalid" data-password-rule="special">
                        <span class="password-requirements__icon" aria-hidden="true"></span>
                        <span><?= htmlspecialchars(translate('auth.register.requirements.special')) ?></span>
                    </div>
                </div>
            </div>

            <div>
                <label class="form-label" for="confirmPasswordField"><?= htmlspecialchars(translate('auth.register.password_confirm')) ?></label>
                <div class="password-field position-relative">
                    <input type="password" class="form-control pe-5" id="confirmPasswordField" name="confirm" required maxlength="128" autocomplete="new-password">
                    <button type="button" class="password-toggle" aria-label="<?= htmlspecialchars(translate('auth.register.password_toggle_show')) ?>" data-password-toggle="confirmPasswordField" aria-pressed="false" data-visible="false">
                        <span class="password-toggle__icon" aria-hidden="true"></span>
                        <span class="visually-hidden"><?= htmlspecialchars(translate('auth.register.password_toggle_show')) ?></span>
                    </button>
                </div>
                <div class="text-danger small mt-1 d-none" data-password-mismatch><?= htmlspecialchars(translate('auth.register.passwords_mismatch')) ?></div>
            </div>

            <?php if (!empty($next)): ?>
                <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-success"><?= htmlspecialchars(translate('auth.register.submit')) ?></button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('passwordField');
    const confirmInput = document.getElementById('confirmPasswordField');
    const requirementItems = document.querySelectorAll('[data-password-rule]');
    const requirementContainer = document.getElementById('passwordRequirements');
    const toggleButtons = document.querySelectorAll('[data-password-toggle]');
    const mismatchMessage = document.querySelector('[data-password-mismatch]');

    const passwordChecks = (value) => ({
        length: value.length >= 10,
        uppercase: /[A-Z]/.test(value),
        lowercase: /[a-z]/.test(value),
        digit: /[0-9]/.test(value),
        special: /[^A-Za-z0-9]/.test(value),
    });

    const updateRequirements = () => {
        const value = passwordInput?.value || '';
        const states = passwordChecks(value);
        requirementItems.forEach((item) => {
            const rule = item.getAttribute('data-password-rule');
            const isValid = Boolean(states[rule]);
            item.classList.toggle('valid', isValid);
            item.classList.toggle('invalid', !isValid);
        });
    };

    const updateMismatchState = () => {
        if (!passwordInput || !confirmInput || !mismatchMessage) return;
        const hasValue = confirmInput.value.length > 0;
        const isMatch = confirmInput.value === passwordInput.value;
        const shouldShow = hasValue && !isMatch;
        confirmInput.classList.toggle('is-invalid', shouldShow);
        mismatchMessage.classList.toggle('d-none', !shouldShow);
    };

    const updateRequirementsVisibility = () => {
        if (!requirementContainer) return;
        const hasValue = (passwordInput?.value || '').length > 0;
        requirementContainer.classList.toggle('is-hidden', !hasValue);
    };

    const toggleVisibility = (button) => {
        const targetId = button.getAttribute('data-password-toggle');
        const target = document.getElementById(targetId);
        if (!target) return;
        const isVisible = button.getAttribute('data-visible') === 'true';
        const nextType = isVisible ? 'password' : 'text';
        target.setAttribute('type', nextType);
        button.setAttribute('data-visible', String(!isVisible));
        button.setAttribute('aria-pressed', String(!isVisible));
        const label = !isVisible ? '<?= htmlspecialchars(translate('auth.register.password_toggle_hide')) ?>' : '<?= htmlspecialchars(translate('auth.register.password_toggle_show')) ?>';
        button.setAttribute('aria-label', label);
        const hiddenLabel = button.querySelector('.visually-hidden');
        if (hiddenLabel) {
            hiddenLabel.textContent = label;
        }
    };

    passwordInput?.addEventListener('input', () => {
        updateRequirements();
        updateRequirementsVisibility();
        updateMismatchState();
    });

    passwordInput?.addEventListener('blur', updateRequirementsVisibility);

    confirmInput?.addEventListener('input', updateMismatchState);

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => toggleVisibility(button));
    });

    updateRequirements();
    updateRequirementsVisibility();
    updateMismatchState();
});
</script>
