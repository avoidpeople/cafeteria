<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('dishModal');
    const modalTitle = document.getElementById('dishTitle');
    const modalDesc = document.getElementById('dishDescription');
    const modalIngr = document.getElementById('dishIngridients');
    const modalCat = document.getElementById('dishCategory');
    const modalPrice = document.getElementById('dishPrice');
    const modalUniqueBadge = document.getElementById('dishUniqueBadge');
    const carouselEl = document.getElementById('dishCarousel');
    const carouselInner = document.getElementById('dishCarouselInner');
    const carouselIndicators = document.getElementById('dishCarouselIndicators');
    const carouselPrev = document.getElementById('dishCarouselPrev');
    const carouselNext = document.getElementById('dishCarouselNext');
    const modalAddBtn = document.getElementById('modalAddToCart');
    const dishModal = bootstrap.Modal.getOrCreateInstance(modal);
    let carouselInstance = bootstrap.Carousel.getOrCreateInstance(carouselEl, { interval: false });
    carouselInstance.pause();

    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('click', () => {
            const item = JSON.parse(card.dataset.item);
            modalTitle.textContent = item.title;
            modalDesc.textContent = item.description || 'Описание пока отсутствует.';
            modalIngr.textContent = item.ingredients ? `Состав: ${item.ingredients}` : '';
            modalCat.textContent = item.category ? `Категория: ${item.category}` : 'Без категории';
            const rawPrice = typeof item.raw_price !== 'undefined' ? parseFloat(item.raw_price) : parseFloat(item.price);
            const isUnique = Boolean(item.is_unique);
            modalUniqueBadge?.classList.toggle('d-none', !isUnique);
            if (modalPrice) {
                modalPrice.textContent = isUnique && rawPrice > 0
                    ? `${rawPrice.toFixed(2)} €`
                    : 'Входит в комплексный обед';
            }
            modalAddBtn.dataset.id = item.id;
            modalAddBtn.dataset.comboRole = card.dataset.comboRole || 'main';

            let gallery = Array.isArray(item.gallery) && item.gallery.length ? item.gallery : [];
            if (!gallery.length && item.image) {
                gallery = [item.image];
            }
            if (!gallery.length) {
                gallery = [''];
            }

            carouselInner.innerHTML = '';
            carouselIndicators.innerHTML = '';

            gallery.forEach((img, index) => {
                const isActive = index === 0 ? 'active' : '';
                const slide = document.createElement('div');
                slide.className = `carousel-item ${isActive}`;
                const imgEl = document.createElement('img');
                imgEl.className = 'd-block w-100 rounded dish-carousel-img';
                imgEl.alt = item.title;
                imgEl.src = img ? `/assets/images/${img}` : 'https://via.placeholder.com/400x250?text=Нет+фото';
                slide.appendChild(imgEl);
                carouselInner.appendChild(slide);

                if (gallery.length > 1) {
                    const indicator = document.createElement('button');
                    indicator.type = 'button';
                    indicator.setAttribute('data-bs-target', '#dishCarousel');
                    indicator.setAttribute('data-bs-slide-to', index);
                    indicator.className = isActive;
                    indicator.setAttribute('aria-label', `Слайд ${index + 1}`);
                    if (index === 0) {
                        indicator.setAttribute('aria-current', 'true');
                    }
                    carouselIndicators.appendChild(indicator);
                }
            });

            const controlsVisible = gallery.length > 1;
            carouselPrev.style.display = controlsVisible ? '' : 'none';
            carouselNext.style.display = controlsVisible ? '' : 'none';
            carouselIndicators.style.display = controlsVisible ? '' : 'none';
            carouselInstance.dispose();
            carouselInstance = new bootstrap.Carousel(carouselEl, { interval: false, ride: false, wrap: controlsVisible });
            carouselInstance.to(0);
            dishModal.show();
        });
    });

    modalAddBtn.addEventListener('click', () => {
        const role = modalAddBtn.dataset.comboRole || 'main';
        openComboModal(role, modalAddBtn.dataset.id);
    });

    // Combo builder
    const comboModalEl = document.getElementById('comboModal');
    const comboModal = comboModalEl ? bootstrap.Modal.getOrCreateInstance(comboModalEl) : null;
    const comboBuilderBtn = document.getElementById('comboBuilderButton');
    const comboExitBtn = document.getElementById('comboBuilderReset');
    const comboSummary = document.getElementById('comboSelectionPreview');
    const comboSubmit = document.getElementById('comboSubmit');
    const comboError = document.getElementById('comboError');
    const comboPriceValue = document.getElementById('comboPriceValue');
    const comboPriceHint = document.getElementById('comboPriceHint');
    const comboButtons = Array.from(document.querySelectorAll('.combo-select-btn'));
    const comboState = { main: null, soup: null };
    const cardMap = new Map();
    const COMBO_BASE_PRICE = 4.0;
    const COMBO_SOUP_EXTRA = 0.5;

    if (comboModalEl) {
        comboModalEl.querySelectorAll('.combo-option-card').forEach(card => {
            const role = card.dataset.role;
            const id = card.dataset.id || '';
            const key = `${role}:${id || 'none'}`;
            cardMap.set(key, card);
            card.addEventListener('click', () => {
                setSelection(role, id);
            });
        });
    }

    comboButtons.forEach(btn => {
        btn.addEventListener('click', event => {
            event.stopPropagation();
            openComboModal(btn.dataset.comboRole || 'main', btn.dataset.id);
        });
    });

    comboBuilderBtn?.addEventListener('click', () => openComboModal());
    comboExitBtn?.addEventListener('click', () => {
        comboModal?.hide();
        toggleComboMode(false);
    });

    comboModalEl?.addEventListener('shown.bs.modal', () => {
        toggleComboMode(true);
        ensureDefaults();
        updateUI();
    });

    comboModalEl?.addEventListener('hidden.bs.modal', () => {
        toggleComboMode(false);
        if (comboError) {
            comboError.classList.add('d-none');
            comboError.textContent = '';
        }
    });

    comboSubmit?.addEventListener('click', async () => {
        if (!comboState.main) {
            if (comboError) {
                comboError.textContent = 'Выберите горячее блюдо';
                comboError.classList.remove('d-none');
            }
            return;
        }
        comboError?.classList.add('d-none');
        const formData = new FormData();
        formData.append('main_id', comboState.main);
        if (comboState.soup) {
            formData.append('soup_id', comboState.soup);
        }
        const originalText = comboSubmit.textContent;
        comboSubmit.disabled = true;
        comboSubmit.textContent = 'Добавляем...';
        try {
            const response = await fetch('/api/cart/combo', { method: 'POST', body: formData });
            const data = await response.json();
            showToast(data.message || 'Комплексный обед добавлен', data.success);
            if (!data.success && comboError) {
                comboError.textContent = data.message || 'Не удалось добавить комплексный обед';
                comboError.classList.remove('d-none');
            }
            if (data.success) {
                resetComboSelection();
                comboModal?.hide();
            }
        } catch (error) {
            showToast('Не удалось добавить комплексный обед', false);
            if (comboError) {
                comboError.textContent = 'Ошибка при сохранении комплекса';
                comboError.classList.remove('d-none');
            }
        } finally {
            comboSubmit.disabled = false;
            comboSubmit.textContent = originalText;
        }
    });

    function setSelection(role, id) {
        if (role === 'main') {
            comboState.main = id;
        } else if (role === 'soup') {
            comboState.soup = id || null;
        }
        updateUI();
    }

    function ensureDefaults() {
        if (!comboState.main) {
            const firstMain = comboModalEl?.querySelector('#comboMainOptions .combo-option-card');
            if (firstMain) {
                comboState.main = firstMain.dataset.id;
            }
        }
        if (comboState.soup === null) {
            const skipSoup = comboModalEl?.querySelector('#comboSoupOptions .combo-option-card[data-id=\"\"]');
            if (skipSoup) {
                comboState.soup = null;
            }
        }
    }

    function updateUI() {
        updateModalCards();
        updateSummary();
        updateMenuButtons();
        updatePrice();
    }

    function updateModalCards() {
        cardMap.forEach((card, key) => {
            const [role, identifier] = key.split(':');
            const selectedId = role === 'soup' ? (comboState.soup ?? 'none') : comboState.main;
            const normalized = identifier === 'none' ? null : identifier;
            const isActive = role === 'soup'
                ? ((comboState.soup ?? null) === (normalized ?? null))
                : (comboState.main === normalized);
            card.classList.toggle('active', Boolean(isActive));
        });
        if (comboSubmit) {
            comboSubmit.disabled = !comboState.main;
        }
    }

    function updateMenuButtons() {
        comboButtons.forEach(btn => {
            const role = btn.dataset.comboRole || 'main';
            const id = btn.dataset.id || '';
            const isActive = role === 'soup'
                ? comboState.soup === id
                : comboState.main === id;
            btn.classList.toggle('selected', Boolean(isActive));
            btn.textContent = isActive ? 'Выбрано' : (btn.dataset.defaultText || 'Добавить в комплекс');
        });
    }

    function updateSummary() {
        if (!comboSummary) return;
        comboSummary.innerHTML = '';
        const mainData = getCardData('main', comboState.main);
        const soupData = comboState.soup ? getCardData('soup', comboState.soup) : null;
        if (mainData) {
            comboSummary.appendChild(renderSummaryItem(mainData, 'Горячее'));
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'text-muted';
            placeholder.textContent = 'Выберите горячее блюдо';
            comboSummary.appendChild(placeholder);
        }
        if (soupData) {
            comboSummary.appendChild(renderSummaryItem(soupData, 'Суп'));
        } else if (comboState.soup === null) {
            comboSummary.appendChild(renderNoSoupSummary());
        }
    }

    function updatePrice() {
        if (!comboPriceValue) return;
        const mainData = comboState.main ? getCardData('main', comboState.main) : null;
        const soupData = comboState.soup ? getCardData('soup', comboState.soup) : null;
        if (!mainData) {
            comboPriceValue.textContent = `${COMBO_BASE_PRICE.toFixed(2)} €`;
            comboPriceHint && (comboPriceHint.textContent = 'Выберите горячее блюдо');
            return;
        }
        let total = mainData.is_unique ? parsePrice(mainData.price) : COMBO_BASE_PRICE;
        const hintParts = [];
        if (mainData.is_unique) {
            hintParts.push(`Основное: ${formatCurrency(mainData.price)}`);
        } else {
            hintParts.push('Стандартный сет 4.00 €');
        }
        if (soupData) {
            if (soupData.is_unique) {
                const soupPrice = parsePrice(soupData.price);
                total += soupPrice;
                hintParts.push(`Суп: ${formatCurrency(soupPrice)}`);
            } else {
                total += COMBO_SOUP_EXTRA;
                hintParts.push('Суп: +0.50 €');
            }
        }
        comboPriceValue.textContent = `${total.toFixed(2)} €`;
        if (comboPriceHint) {
            comboPriceHint.textContent = hintParts.join(' · ');
        }
    }

    function getCardData(role, id) {
        if (!id && role === 'soup') {
            const card = cardMap.get('soup:none');
            if (!card) return null;
            return extractCardData(card);
        }
        const card = cardMap.get(`${role}:${id}`);
        return card ? extractCardData(card) : null;
    }

    function extractCardData(card) {
        return {
            role: card.dataset.role,
            title: card.dataset.title || 'Блюдо',
            description: card.dataset.description || '',
            image: card.dataset.image || '',
            price: card.dataset.price || '0',
            is_unique: card.dataset.unique === '1',
        };
    }

    function renderSummaryItem(data, label) {
        const wrapper = document.createElement('div');
        wrapper.className = 'combo-selection-item';
        const thumb = document.createElement('div');
        thumb.className = 'combo-selection-thumb';
        if (data.image) {
            const img = document.createElement('img');
            img.src = `/assets/images/${data.image}`;
            img.alt = data.title;
            thumb.appendChild(img);
        } else {
            thumb.textContent = 'Нет фото';
        }
        const body = document.createElement('div');
        const titleEl = document.createElement('div');
        titleEl.className = 'combo-selection-title';
        titleEl.textContent = data.title;
        if (data.is_unique) {
            const badge = document.createElement('span');
            badge.className = 'combo-unique-chip ms-2';
            badge.textContent = '★ Уникальное';
            titleEl.appendChild(badge);
        }
        const meta = document.createElement('div');
        meta.className = 'text-muted small';
        meta.textContent = label;
        const desc = document.createElement('div');
        desc.className = 'text-muted small text-truncate-2';
        desc.textContent = data.description || 'Описание появится позже';
        body.append(meta, titleEl, desc);
        if (data.is_unique && parsePrice(data.price) > 0) {
            const price = document.createElement('div');
            price.className = 'combo-selection-price';
            price.textContent = `${formatCurrency(data.price)} €`;
            body.append(price);
        }
        wrapper.append(thumb, body);
        return wrapper;
    }

    function renderNoSoupSummary() {
        const wrapper = document.createElement('div');
        wrapper.className = 'combo-selection-item combo-selection-item--placeholder';
        const thumb = document.createElement('div');
        thumb.className = 'combo-selection-thumb';
        thumb.textContent = '—';
        const body = document.createElement('div');
        const meta = document.createElement('div');
        meta.className = 'text-muted small';
        meta.textContent = 'Суп';
        const title = document.createElement('div');
        title.className = 'combo-selection-title';
        title.textContent = 'Без супа';
        body.append(meta, title);
        wrapper.append(thumb, body);
        return wrapper;
    }

    function openComboModal(role = null, id = null) {
        if (!comboModal) return;
        if (role && id) {
            setSelection(role, id);
        } else {
            ensureDefaults();
            updateUI();
        }
        comboModal.show();
    }

    function resetComboSelection() {
        comboState.main = null;
        comboState.soup = null;
        updateUI();
    }

    function toggleComboMode(active) {
        document.body.classList.toggle('combo-mode', active);
        comboExitBtn && (comboExitBtn.hidden = !active);
    }

    function showToast(message, success = true) {
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${success ? 'text-bg-success' : 'text-bg-danger'} border-0 position-fixed top-50 start-50 translate-middle`;
        toastEl.style.zIndex = 1080;
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 2400 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    function parsePrice(value) {
        const numeric = parseFloat(value);
        return Number.isFinite(numeric) ? numeric : 0;
    }

    function formatCurrency(value) {
        return parsePrice(value).toFixed(2);
    }
});
</script>
