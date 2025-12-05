<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('dishModal');
    const modalTitle = document.getElementById('dishTitle');
    const modalDesc = document.getElementById('dishDescription');
    const modalIngr = document.getElementById('dishIngridients');
    const modalCat = document.getElementById('dishCategory');
    const modalPrice = document.getElementById('dishPrice');
    const carouselEl = document.getElementById('dishCarousel');
    const carouselInner = document.getElementById('dishCarouselInner');
    const carouselIndicators = document.getElementById('dishCarouselIndicators');
    const carouselPrev = document.getElementById('dishCarouselPrev');
    const carouselNext = document.getElementById('dishCarouselNext');
    const modalAddBtn = document.getElementById('modalAddToCart');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
    let carouselInstance = bootstrap.Carousel.getOrCreateInstance(carouselEl, { interval: false });
    carouselInstance.pause();

    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('click', () => {
            const item = JSON.parse(card.dataset.item);
            modalTitle.textContent = item.title;
            modalDesc.textContent = item.description || 'Описание пока отсутствует.';
            modalIngr.textContent = item.ingredients ? `Состав: ${item.ingredients}` : '';
            modalCat.textContent = item.category ? `Категория: ${item.category}` : 'Без категории';
            modalPrice.textContent = `${item.price} €`;
            let gallery = Array.isArray(item.gallery) && item.gallery.length
                ? item.gallery
                : (item.image ? [item.image] : []);
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
                    indicator.className = index === 0 ? 'active' : '';
                    indicator.setAttribute('aria-label', 'Слайд ' + (index + 1));
                    if (index === 0) {
                        indicator.setAttribute('aria-current', 'true');
                    }
                    carouselIndicators.appendChild(indicator);
                }
            });

            const controlsVisible = (gallery.length > 1);
            carouselPrev.style.display = controlsVisible ? '' : 'none';
            carouselNext.style.display = controlsVisible ? '' : 'none';
            carouselIndicators.style.display = controlsVisible ? '' : 'none';
            carouselInstance.dispose();
            carouselInstance = new bootstrap.Carousel(carouselEl, { interval: false, ride: false, wrap: gallery.length > 1 });
            carouselInstance.to(0);
            modalAddBtn.dataset.id = item.id;
            modalInstance.show();
        });
    });

    modalAddBtn.addEventListener('click', async () => {
        const id = modalAddBtn.dataset.id;
        if (!id) return;
        await addToCart(id);
    });

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', async (event) => {
            event.stopPropagation();
            await addToCart(btn.dataset.id);
        });
    });

    async function addToCart(id) {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('/api/cart/add', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        const toastEl = document.createElement('div');
        const toastClass = data.success ? 'text-bg-success' : 'text-bg-danger';
        toastEl.className = `toast align-items-center ${toastClass} border-0 position-fixed top-50 start-50 translate-middle`;
        toastEl.style.zIndex = 1080;
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${data.message || 'Товар добавлен'}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
});
</script>
