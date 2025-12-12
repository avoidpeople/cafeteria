<?php
use Carbon\Carbon;

$currentLocale = currentLocale() === 'lv' ? 'lv' : 'ru';
$homeDateLabel = Carbon::now('Europe/Riga')->locale($currentLocale)->isoFormat('D MMMM YYYY, dddd');
?>
<div class="page-container home-page">
    <section class="hero-card mb-5">
        <div class="hero-grid">
            <div>
                <p class="hero-pill mb-3"><?= htmlspecialchars(translate('hero.tagline')) ?></p>
                <h1><?= htmlspecialchars(translate('hero.title')) ?></h1>
                <p class="lead">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?= htmlspecialchars(translate('hero.authenticated')) ?>
                    <?php else: ?>
                        <?= htmlspecialchars(translate('hero.guest')) ?>
                    <?php endif; ?>
                </p>
                <div class="hero-stats">
                    <div>
                        <span><?= $menuCount ?></span>
                        <p><?= htmlspecialchars(translate('hero.stats_caption')) ?></p>
                    </div>
                </div>
                <div class="mt-0 mb-3">
                    <span class="badge rounded-pill bg-body-secondary text-body fw-semibold d-inline-flex align-items-center gap-2 px-3 py-2">
                        <span aria-hidden="true">📅</span>
                        <?= htmlspecialchars($homeDateLabel) ?>
                    </span>
                </div>
                 <div class="hero-actions">
                    <a href="/menu" class="btn btn-light btn-lg text-primary fw-semibold px-4"><?= htmlspecialchars(translate('hero.cta_menu')) ?></a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="/register" class="btn btn-outline-light btn-lg px-4"><?= htmlspecialchars(translate('hero.cta_register')) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-panel shadow-sm">
                <h3><?= htmlspecialchars(translate('contact.title')) ?></h3>
                <ul class="list-unstyled mb-4">
                    <li><strong><?= htmlspecialchars(translate('contact.address')) ?>:</strong> Višķu iela 24, Daugavpils, LV-5410</li>
                    <li><strong><?= htmlspecialchars(translate('contact.phone')) ?>:</strong> +371 20 173 444</li>
                    <li><strong><?= htmlspecialchars(translate('contact.email')) ?>:</strong> doctor.gorilka@example.com</li>
                </ul>
                <div class="schedule">
                    <div>
                        <span><?= htmlspecialchars(translate('contact.weekday')) ?></span><br>
                        <strong>10:00 – 17:00</strong>
                    </div>
                    <div>
                        <span><?= htmlspecialchars(translate('contact.weekend')) ?></span><br>
                        <strong><?= htmlspecialchars(translate('contact.weekend_off')) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="steps-card shadow-sm mt-5">
        <h3 class="mb-4"><?= htmlspecialchars(translate('steps.title')) ?></h3>
        <div class="steps-grid">
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">1</span>
                <h4><?= htmlspecialchars(translate('steps.1.title')) ?></h4>
                <p><?= htmlspecialchars(translate('steps.1.text')) ?></p>
            </div>
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">2</span>
                <h4><?= htmlspecialchars(translate('steps.2.title')) ?></h4>
                <p><?= htmlspecialchars(translate('steps.2.text')) ?></p>
            </div>
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">3</span>
                <h4><?= htmlspecialchars(translate('steps.3.title')) ?></h4>
                <p><?= htmlspecialchars(translate('steps.3.text')) ?></p>
            </div>
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">4</span>
                <h4><?= htmlspecialchars(translate('steps.4.title')) ?></h4>
                <p><?= htmlspecialchars(translate('steps.4.text')) ?></p>
            </div>
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">5</span>
                <h4><?= htmlspecialchars(translate('steps.5.title')) ?></h4>
                <p><?= htmlspecialchars(translate('steps.5.text')) ?></p>
            </div>
        </div>
    </section>

    <br>
    <section class="info-grid mb-5">
        <div class="info-card shadow-sm">
            <h3><?= htmlspecialchars(translate('info.menu_title')) ?></h3>
            <p><?= htmlspecialchars(translate('info.menu_text')) ?></p>
        </div>
        <div class="info-card shadow-sm">
            <h3><?= htmlspecialchars(translate('info.history_title')) ?></h3>
            <p><?= htmlspecialchars(translate('info.history_text')) ?></p>
        </div>
        <div class="info-card shadow-sm">
            <h3><?= htmlspecialchars(translate('info.cart_title')) ?></h3>
            <p><?= htmlspecialchars(translate('info.cart_text')) ?></p>
        </div>
    </section>

    <!-- <section class="testimonials mt-5">
        <h3 class="mb-4"><?= htmlspecialchars(translate('testimonials.title')) ?></h3>
        <div class="testimonials-grid">
            <article class="testimonial">
                <p><?= htmlspecialchars(translate('testimonials.1')) ?></p>
                <span><?= htmlspecialchars(translate('testimonials.1.author')) ?></span>
            </article>
            <article class="testimonial">
                <p><?= htmlspecialchars(translate('testimonials.2')) ?></p>
                <span><?= htmlspecialchars(translate('testimonials.2.author')) ?></span>
            </article>
            <article class="testimonial">
                <p><?= htmlspecialchars(translate('testimonials.3')) ?></p>
                <span><?= htmlspecialchars(translate('testimonials.3.author')) ?></span>
            </article>
        </div>
    </section> -->

    <section class="cta-panel shadow-sm mt-5">
        <div>
            <h3><?= htmlspecialchars(translate('cta.title')) ?></h3>
            <p><?= htmlspecialchars(translate('cta.text')) ?></p>
        </div>
        <div class="cta-actions">
            <a href="/menu" class="btn btn-primary btn-lg"><?= htmlspecialchars(translate('cta.menu')) ?></a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/register" class="btn btn-outline-primary btn-lg"><?= htmlspecialchars(translate('cta.register')) ?></a>
            <?php endif; ?>
        </div>
    </section>
</div>
