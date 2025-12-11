<script>
document.addEventListener('DOMContentLoaded', () => {
    const translationsEl = document.getElementById('menuTranslations');
    const menuTexts = translationsEl ? JSON.parse(translationsEl.textContent) : {};
    const t = (key, fallback = '') => menuTexts[key] ?? fallback;

    const modal = document.getElementById('dishModal');
    const modalTitle = document.getElementById('dishTitle');
    const modalDesc = document.getElementById('dishDescription');
    const modalIngr = document.getElementById('dishIngridients');
    const modalCat = document.getElementById('dishCategory');
    const modalPrice = document.getElementById('dishPrice');
    const modalAllergens = document.getElementById('dishAllergens');
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
            modalDesc.textContent = item.description || t('description_missing', 'Description is not available.');
            modalIngr.textContent = item.ingredients ? `${t('ingredients_prefix', 'Ingredients:')} ${item.ingredients}` : '';
            modalCat.textContent = item.category ? `${t('category_prefix', 'Category:')} ${item.category}` : t('category_none', 'No category');
            if (modalAllergens) {
                modalAllergens.textContent = item.allergens ? `${t('allergens_prefix', 'Allergens:')} ${item.allergens}` : '';
                modalAllergens.style.display = item.allergens ? '' : 'none';
            }
            const rawPrice = typeof item.raw_price !== 'undefined' ? parseFloat(item.raw_price) : parseFloat(item.price);
            const isUnique = Boolean(item.is_unique);
            modalUniqueBadge?.classList.toggle('d-none', !isUnique);
            if (modalPrice) {
                modalPrice.textContent = isUnique && rawPrice > 0
                    ? `${rawPrice.toFixed(2)} €`
                    : t('included_price', 'Included in combo');
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
                imgEl.src = img ? `/assets/images/${img}` : `https://via.placeholder.com/400x250?text=${encodeURIComponent(t('no_photo', 'No photo'))}`;
                slide.appendChild(imgEl);
                carouselInner.appendChild(slide);

                if (gallery.length > 1) {
                    const indicator = document.createElement('button');
                    indicator.type = 'button';
                    indicator.setAttribute('data-bs-target', '#dishCarousel');
                    indicator.setAttribute('data-bs-slide-to', index);
                    indicator.className = isActive;
                    indicator.setAttribute('aria-label', `${t('slide_label', 'Slide')} ${index + 1}`);
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
        const role = modalAddBtn.dataset.comboRole;
        if (role) {
            selectCategory(role, modalAddBtn.dataset.id || null);
        }
        openComboModal();
        dishModal.hide();
    });

    // Combo builder
    const comboModalEl = document.getElementById('comboModal');
    const comboModal = comboModalEl ? bootstrap.Modal.getOrCreateInstance(comboModalEl) : null;
    const comboBuilderBtn = document.getElementById('comboBuilderButton');
    const comboSummary = document.getElementById('comboSelectionPreview');
    const comboSubmit = document.getElementById('comboSubmit');
    const comboError = document.getElementById('comboError');
    const comboPriceValue = document.getElementById('comboPriceValue');
    const comboPriceHint = document.getElementById('comboPriceHint');
    const defaultPriceHint = comboPriceHint ? comboPriceHint.textContent : '';
    const comboButtons = Array.from(document.querySelectorAll('.combo-select-btn'));
    const comboConfigEl = document.getElementById('comboConfig');
    const comboConfig = comboConfigEl ? JSON.parse(comboConfigEl.textContent) : {};
    const comboCategories = Array.isArray(comboConfig.categories) ? comboConfig.categories : [];
    const categoryMap = {};
    const categorySteps = {};
    comboCategories.forEach(cat => {
        categoryMap[cat.key] = cat;
    });
    const requiredKeys = comboCategories.filter(cat => cat.required).map(cat => cat.key);
    const comboState = {};
    comboCategories.forEach(cat => {
        comboState[cat.key] = null;
    });
    const cardMap = new Map();
    const COMBO_BASE_PRICE = parseFloat(comboConfig.base_price ?? 4) || 4;
    let missingKeys = [];

    if (comboModalEl) {
        comboModalEl.querySelectorAll('.combo-step').forEach(step => {
            const key = step.dataset.category;
            if (key) {
                categorySteps[key] = step;
            }
        });
        comboModalEl.querySelectorAll('.combo-option-card').forEach(card => {
            const category = card.dataset.category;
            const id = card.dataset.id || '';
            const key = `${category}:${id || 'none'}`;
            cardMap.set(key, card);
            card.addEventListener('click', () => {
                selectCategory(category, id);
                updateUI();
            });
        });
    }

    comboButtons.forEach(btn => {
        btn.addEventListener('click', event => {
            event.stopPropagation();
            const role = btn.dataset.comboRole;
            if (role) {
                selectCategory(role, btn.dataset.id || null);
            }
            openComboModal();
        });
    });

    comboBuilderBtn?.addEventListener('click', () => openComboModal());
    comboModalEl?.addEventListener('show.bs.modal', () => {
        toggleComboMode(true);
    });

    comboModalEl?.addEventListener('shown.bs.modal', () => {
        updateUI();
    });

    comboModalEl?.addEventListener('hide.bs.modal', () => {
        toggleComboMode(false);
    });

    comboModalEl?.addEventListener('hidden.bs.modal', () => {
        if (comboError) {
            comboError.classList.add('d-none');
            comboError.textContent = '';
        }
    });

    comboSubmit?.addEventListener('click', async () => {
        const missingRequired = requiredKeys.filter(key => !comboState[key]);
        if (missingRequired.length > 0) {
            missingKeys = missingRequired;
            comboError?.classList.remove('d-none');
            const missingLabels = missingRequired.map(key => categoryMap[key]?.label || key);
            comboError && (comboError.textContent = `${t('summary_pick_main', 'Select required dishes')}: ${missingLabels.join(', ')}`);
            applyMissingHighlight();
            scrollToCategory(missingRequired[0]);
            return;
        }
        missingKeys = [];
        applyMissingHighlight();
        comboError?.classList.add('d-none');
        const formData = new FormData();
        if (comboState.main) {
            formData.append('main_id', comboState.main);
        }
        if (comboState.garnish) {
            formData.append('garnish_id', comboState.garnish);
        }
        if (comboState.soup) {
            formData.append('soup_id', comboState.soup);
        }
        Object.entries(comboState).forEach(([key, value]) => {
            if (['main', 'garnish', 'soup'].includes(key)) {
                return;
            }
            if (value) {
                formData.append(`extras[${key}]`, value);
            }
        });
        const originalText = comboSubmit.textContent;
        comboSubmit.disabled = true;
        comboSubmit.textContent = t('adding', 'Adding...');
        try {
            const response = await fetch('/api/cart/combo', { method: 'POST', body: formData });
            const data = await response.json();
            showToast(data.message || t('toast_added', 'Combo added'), data.success);
            if (!data.success && comboError) {
                comboError.textContent = data.message || t('error_add_combo', 'Failed to add combo');
                comboError.classList.remove('d-none');
            }
            if (data.success) {
                resetComboSelection();
                comboModal?.hide();
            }
        } catch (error) {
            showToast(t('toast_failed', 'Failed to add combo'), false);
            if (comboError) {
                comboError.textContent = t('error_save', 'Failed to save combo');
                comboError.classList.remove('d-none');
            }
        } finally {
            comboSubmit.disabled = false;
            comboSubmit.textContent = originalText;
        }
    });

    function updateUI() {
        updateModalCards();
        updateSummary();
        updateMenuButtons();
        updatePrice();
        if (missingKeys.length && requiredKeys.every(key => comboState[key])) {
            missingKeys = [];
        }
        applyMissingHighlight();
    }

    function updateModalCards() {
        cardMap.forEach((card, key) => {
            const lastColon = key.lastIndexOf(':');
            const category = lastColon >= 0 ? key.slice(0, lastColon) : key;
            const identifier = lastColon >= 0 ? key.slice(lastColon + 1) : 'none';
            const normalized = identifier === 'none' ? null : identifier;
            const current = comboState[category] ?? null;
            const isActive = (current ?? null) === (normalized ?? null);
            card.classList.toggle('active', Boolean(isActive));
        });
    }

    function updateMenuButtons() {
        comboButtons.forEach(btn => {
            const role = btn.dataset.comboRole || 'main';
            const id = btn.dataset.id || '';
            const isActive = comboState[role] === id;
            btn.classList.toggle('selected', Boolean(isActive));
            btn.textContent = isActive ? t('button_selected', 'Selected') : (btn.dataset.defaultText || t('button_add', 'Add to combo'));
        });
    }

    function updateSummary() {
        if (!comboSummary) return;
        comboSummary.innerHTML = '';
        comboCategories.forEach(category => {
            const selection = comboState[category.key];
            if (selection) {
                const data = getCardData(category.key, selection);
                if (data) {
                    comboSummary.appendChild(renderSummaryItem(data, category.label));
                }
            } else if (category.required) {
                const placeholder = document.createElement('div');
                placeholder.className = 'text-muted';
                placeholder.textContent = category.label + ': ' + t('summary_pick_main', 'Select required dish');
                comboSummary.appendChild(placeholder);
            } else if (category.key === 'soup') {
                comboSummary.appendChild(renderNoSoupSummary());
            }
        });
    }

    function updatePrice() {
        if (!comboPriceValue) return;
        let base = COMBO_BASE_PRICE;
        const mainId = comboState.main;
        if (mainId) {
            const mainData = getCardData('main', mainId);
            const mainPrice = mainData ? parsePrice(mainData.price) : 0;
            if (mainPrice > 0) {
                base = mainPrice;
            }
        }
        let total = base;
        const hintParts = [];
        const hasRequired = requiredKeys.every(key => comboState[key]);
        if (!hasRequired) {
            comboPriceValue.textContent = `${total.toFixed(2)} €`;
            comboPriceHint && (comboPriceHint.textContent = t('summary_pick_main', 'Select required dish'));
            return;
        }
        comboCategories.forEach(category => {
            const selectionId = comboState[category.key];
            if (!selectionId) {
                return;
            }
            const data = getCardData(category.key, selectionId);
            const price = data ? parsePrice(data.price) : 0;
            if (['main', 'garnish'].includes(category.key)) {
                return;
            }
            if (price > 0) {
                total += price;
                hintParts.push(`${category.label}: +${formatCurrency(price)} €`);
            }
        });
        comboPriceValue.textContent = `${total.toFixed(2)} €`;
        if (comboPriceHint) {
            comboPriceHint.textContent = hintParts.length ? hintParts.join(' · ') : defaultPriceHint;
        }
    }

    function getCardData(category, id) {
        const card = cardMap.get(`${category}:${id}`);
        return card ? extractCardData(card) : null;
    }

    function extractCardData(card) {
        return {
            role: card.dataset.role,
            title: card.dataset.title || t('default_dish', 'Dish'),
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
            thumb.textContent = t('no_photo', 'No photo');
        }
        const body = document.createElement('div');
        const titleEl = document.createElement('div');
        titleEl.className = 'combo-selection-title';
        titleEl.textContent = data.title;
        if (data.is_unique) {
            const badge = document.createElement('span');
            badge.className = 'combo-unique-chip ms-2';
            badge.textContent = t('unique_badge', '★ Unique');
            titleEl.appendChild(badge);
        }
        const meta = document.createElement('div');
        meta.className = 'text-muted small';
        meta.textContent = label;
        const desc = document.createElement('div');
        desc.className = 'text-muted small text-truncate-2';
        desc.textContent = data.description || t('description_pending', 'Description coming soon');
        body.append(meta, titleEl, desc);
        if (parsePrice(data.price) > 0) {
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
        meta.textContent = t('label_soup', 'Soup');
        const title = document.createElement('div');
        title.className = 'combo-selection-title';
        title.textContent = t('label_no_soup', 'No soup');
        body.append(meta, title);
        wrapper.append(thumb, body);
        return wrapper;
    }

    function openComboModal() {
        if (!comboModal) return;
        updateUI();
        comboModal.show();
    }

    function resetComboSelection() {
        Object.keys(comboState).forEach(key => {
            comboState[key] = null;
        });
        updateUI();
    }

    function selectCategory(category, id) {
        if (!(category in comboState)) {
            return;
        }
        comboState[category] = id || null;
    }

    function toggleComboMode(active) {
        document.body.classList.toggle('combo-mode', active);
    }

    function showToast(message, success = true) {
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${success ? 'text-bg-success' : 'text-bg-danger'} border-0 position-fixed top-50 start-50 translate-middle`;
        toastEl.style.zIndex = 1300;
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

    function applyMissingHighlight() {
        Object.entries(categorySteps).forEach(([key, el]) => {
            el.classList.toggle('combo-step-missing', missingKeys.includes(key));
        });
        if (!missingKeys.length && comboError && requiredKeys.every(key => comboState[key])) {
            comboError.classList.add('d-none');
            comboError.textContent = '';
        }
    }

    function scrollToCategory(key) {
        const el = categorySteps[key];
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
