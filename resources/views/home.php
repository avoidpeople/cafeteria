<div class="page-container home-page">
    <section class="hero-card mb-5">
        <div class="hero-grid">
            <div>
                <p class="hero-pill">Modern cafeteria</p>
                <h1>Свежие блюда, онлайн‑заказы и полный контроль в одном месте</h1>
                <p class="lead">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php echo 'Добро пожаловать! Продолжайте заказы, отслеживайте статусы, повторяйте любимые блюда.'; ?>
                    <?php else: ?>
                        <?php echo 'Создайте профиль за минуту, добавляйте позиции в корзину и отслеживайте статус приготовления в реальном времени.'; ?>
                    <?php endif; ?>
                </p>
                <div class="hero-stats">
                    <div>
                        <span><?= $menuCount ?></span>
                        <p>позиций доступно сегодня</p>
                    </div>
                    <div>
                        <span>*** мин</span>
                        <p>среднее время доставки</p>
                    </div>
                </div>
                 <div class="hero-actions">
                    <a href="/menu" class="btn btn-light btn-lg text-primary fw-semibold px-4">Открыть меню</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="/register" class="btn btn-outline-light btn-lg px-4">Создать профиль</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-panel shadow-sm">
                <h3>Мы рядом</h3>
                <ul class="list-unstyled mb-4">
                    <li><strong>Адрес:</strong> Минск, ул. Образцова, 10</li>
                    <li><strong>Телефон:</strong> +371 ********</li>
                    <li><strong>Email:</strong> cafeteria@example.com</li>  
                </ul>
                <div class="schedule">
                    <div>
                        <span>Понедельник‑Пятница</span>
                        <strong>10:00 – 18:00</strong>
                    </div>
                    <div>
                        <span>Суббота-Воскресенье</span>
                        <strong>Выходной</strong>
                    </div>
                </div>
                <p class="text-white-50 mt-3">Заказы, оформленные вне графика, обрабатываются в начале следующего рабочего дня.</p>
            </div>
        </div>
    </section>

    <section class="info-grid mb-5">
        <div class="info-card shadow-sm">
            <h3>Цифровое меню</h3>
            <p>Подробные карточки с галереей фотографий, описанием, составом и актуальной ценой.</p>
        </div>
        <div class="info-card shadow-sm">
            <h3>История и статусы</h3>
            <p>Каждый заказ сохраняется с адресом доставки, временем, статусом и возможностью повторить его.</p>
        </div>
        <div class="info-card shadow-sm">
            <h3>Умная корзина</h3>
            <p>Отмечайте блюда, которые попадут в заказ, сортируйте список и оформляйте одним кликом.</p>
        </div>
    </section>

    <section class="cards-grid">
        <div class="feature-card shadow-sm">
            <h4>Без очередей</h4>
            <p>Система уведомлений заменяет бумажные талончики: вы точно знаете, когда заказ будет готов.</p>
        </div>
        <div class="feature-card shadow-sm">
            <h4>Любимые комбинации</h4>
            <p>Сохраняем состав заказа и позволяем повторить его целиком или изменить пару позиций.</p>
        </div>
        <div class="feature-card shadow-sm">
            <h4>Живое меню</h4>
            <p>Администраторы мгновенно обновляют блюда, добавляют фото и отмечают наличие.</p>
        </div>
    </section>

    <section class="gallery-strip mt-5">
        <div class="gallery-title">
            <p class="hero-pill text-uppercase">Визуальная лента</p>
            <h3>Немного атмосферы кухни</h3>
            <p>Фотографии обновляются вместе с меню — можно увидеть, как будут выглядеть блюда до оформления заказа.</p>
        </div>
        <div class="gallery-grid">
            <div class="gallery-item" style="background-image:url('https://images.unsplash.com/photo-1496417263034-38ec4f0b665a?auto=format&fit=crop&w=600&q=60');"></div>
            <div class="gallery-item" style="background-image:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&q=60');"></div>
            <div class="gallery-item" style="background-image:url('https://images.unsplash.com/photo-1470337458703-46ad1756a187?auto=format&fit=crop&w=600&q=60');"></div>
            <div class="gallery-item" style="background-image:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=60');"></div>
        </div>
    </section>

    <section class="steps-card shadow-sm mt-5">
        <h3 class="mb-4">Как всё работает</h3>
        <div class="steps-grid">
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">1</span>
                <h4>Выбираете блюдо</h4>
                <p>Используйте фильтры по категории и поиску. Карточка раскрывается в модальном окне с галереей.</p>
            </div>
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">2</span>
                <h4>Собираете корзину</h4>
                <p>Добавляйте позиции, меняйте количество, отмечайте, что войдет в заказ, и следите за итоговой суммой.</p>
            </div>
            <div class="step-item">
                <span class="badge bg-primary rounded-pill">3</span>
                <h4>Оформляете и ждёте</h4>
                <p>Укажите адрес доставки или точку выдачи — система сообщит о готовности и обновит историю заказов.</p>
            </div>
        </div>
    </section>

    <section class="testimonials mt-5">
        <h3 class="mb-4">Отзывы посетителей</h3>
        <div class="testimonials-grid">
            <article class="testimonial">
                <p>«Очень удобно оформлять заказ прямо с пары: за пять минут до перерыва отмечаю блюда и сразу вижу статус. Понравились push‑подсказки про готовность.»</p>
                <span>— Анастасия, студентка 3 курса</span>
            </article>
            <article class="testimonial">
                <p>«Как преподаватель, ценю экономию времени. Вхожу, открываю историю и повторяю «любимый завтрак». Ещё бы и кофе подписками сделали!»</p>
                <span>— Владимир Иванович</span>
            </article>
            <article class="testimonial">
                <p>«Работаю в столовой, и новая система здорово разгрузила кассу — меньше очередей и меньше ошибок в заказах.»</p>
                <span>— Ольга, администратор</span>
            </article>
        </div>
    </section>

    <section class="cta-panel shadow-sm mt-5">
        <div>
            <h3>Готовы сделать первый заказ?</h3>
            <p>Войдите или зарегистрируйтесь, чтобы собрать корзину и оформить доставку прямо сейчас.</p>
        </div>
        <div class="cta-actions">
            <a href="/menu" class="btn btn-primary btn-lg">Посмотреть меню</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/register" class="btn btn-outline-primary btn-lg">Зарегистрироваться</a>
            <?php endif; ?>
        </div>
    </section>
</div>
