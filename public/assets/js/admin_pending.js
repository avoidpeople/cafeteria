(() => {
    const container = document.getElementById('pendingOrdersContainer');
    const badge = document.getElementById('pendingBadge');
    if (!container || !badge) {
        return;
    }

    const escapeHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const fetchPending = async () => {
        try {
            const response = await fetch('/api/admin/pending-orders', { credentials: 'same-origin' });
            if (!response.ok) {
                return;
            }
            const payload = await response.json();
            const orders = payload.orders || [];
            if ((payload.count || 0) > 0) {
                badge.textContent = payload.count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            if (!orders.length) {
                container.innerHTML = `<div class="text-muted">Новых заказов нет</div>`;
                return;
            }

            container.innerHTML = '';
            orders.forEach((order) => {
                const card = document.createElement('div');
                card.className = 'pending-card';
                card.dataset.link = order.link;
                const itemsList = (order.items || []).map((item) => `
                    <li>${escapeHtml(item.quantity)} × ${escapeHtml(item.title)}</li>
                `).join('') || '<li class="text-muted">Состав уточняется</li>';
                card.innerHTML = `
                    <div class="pending-card__header">
                        <strong>Заказ #${order.id}</strong>
                        <small>${order.created_at ? new Date(order.created_at).toLocaleString('ru-RU', {hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit'}) : ''}</small>
                    </div>
                    <p><b>Клиент:</b> ${order.user || 'Пользователь'}</p>
                    <p><b>Адрес:</b> ${order.address || 'Не указан'}</p>
                    <div class="pending-card__items-wrapper">
                        <div class="text-muted small mb-1">В заказе:</div>
                        <ul class="pending-card__items">${itemsList}</ul>
                    </div>
                    <p class="pending-card__sum">Сумма: ${Number(order.total ?? 0).toFixed(2)} €</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" data-action="accept" data-id="${order.id}">Принять</button>
                        <button class="btn btn-outline-danger btn-sm" data-action="decline" data-id="${order.id}">Отклонить</button>
                    </div>
                `;
                container.appendChild(card);
            });
        } catch (e) {
            // eslint-disable-line no-console
        }
    };

    const handleAction = async (id, action) => {
        if (action === 'decline') {
            const confirmed = window.confirm('Отклонить заказ? Эта операция необратима.');
            if (!confirmed) {
                return;
            }
        }
        try {
            const response = await fetch('/api/admin/pending-orders/action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: new URLSearchParams({ id, action }).toString(),
            });
            if (response.ok) {
                fetchPending();
            }
        } catch (e) {
            // eslint-disable-line no-console
        }
    };

    container.addEventListener('click', (event) => {
        const target = event.target;
        if (target.matches('button[data-action]')) {
            const { action, id } = target.dataset;
            handleAction(id, action);
            event.stopPropagation();
            return;
        }
        const card = target.closest('.pending-card');
        if (card && card.dataset.link) {
            window.location.href = card.dataset.link;
        }
    });

    fetchPending();
    setInterval(fetchPending, 8000);
})();
