(() => {
    const container = document.getElementById('pendingOrdersContainer');
    const badge = document.getElementById('pendingBadge');
    if (!container || !badge) {
        return;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const texts = {
        empty: container.dataset.emptyText || 'No new orders',
        order: container.dataset.orderLabel || 'Order #:id',
        customer: container.dataset.customerLabel || 'Customer:',
        customerPlaceholder: container.dataset.customerPlaceholder || 'User',
        address: container.dataset.addressLabel || 'Address:',
        addressPlaceholder: container.dataset.addressPlaceholder || 'Not provided',
        itemsLabel: container.dataset.itemsLabel || 'Items:',
        itemsPlaceholder: container.dataset.itemsPlaceholder || 'Details pending',
        sumLabel: container.dataset.sumLabel || 'Total:',
        commentLabel: container.dataset.commentLabel || 'Comment:',
        commentPlaceholder: container.dataset.commentPlaceholder || 'No comment',
        accept: container.dataset.acceptLabel || 'Accept',
        decline: container.dataset.declineLabel || 'Decline',
        confirmDecline: container.dataset.confirmDecline || 'Decline order? This action cannot be undone.',
    };
    const locale = document.documentElement.getAttribute('lang') || 'ru';

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
                container.innerHTML = `<div class="text-muted">${texts.empty}</div>`;
                return;
            }

            container.innerHTML = '';
            orders.forEach((order) => {
                const card = document.createElement('div');
                card.className = 'pending-card';
                card.dataset.link = order.link;
                const itemsList = (order.items || []).map((item) => `
                    <li>${escapeHtml(item.quantity)} × ${escapeHtml(item.title)}</li>
                `).join('') || `<li class="text-muted">${texts.itemsPlaceholder}</li>`;
                const displayCode = escapeHtml(order.code || order.id);
                const orderTitle = texts.order.replace(':id', displayCode);
                const commentBlock = order.comment
                    ? `<div class="pending-card__comment comment-box"><div class="text-muted small mb-1">${texts.commentLabel}</div>${escapeHtml(order.comment)}</div>`
                    : '';
                card.innerHTML = `
                    <div class="pending-card__header">
                        <strong>${orderTitle}</strong>
                        <small>${order.created_at ? new Date(order.created_at).toLocaleString(locale, {hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit'}) : ''}</small>
                    </div>
                    <p><b>${texts.customer}</b> ${order.user || texts.customerPlaceholder}</p>
                    <p><b>${texts.address}</b> ${order.address || texts.addressPlaceholder}</p>
                    <div class="pending-card__items-wrapper">
                        <div class="text-muted small mb-1">${texts.itemsLabel}</div>
                        <ul class="pending-card__items">${itemsList}</ul>
                    </div>
                    ${commentBlock}
                    <p class="pending-card__sum">${texts.sumLabel} ${Number(order.total ?? 0).toFixed(2)} €</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" data-action="accept" data-id="${order.id}">${texts.accept}</button>
                        <button class="btn btn-outline-danger btn-sm" data-action="decline" data-id="${order.id}">${texts.decline}</button>
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
            const confirmed = window.confirm(texts.confirmDecline);
            if (!confirmed) {
                return;
            }
        }
        try {
            const response = await fetch('/api/admin/pending-orders/action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: new URLSearchParams({ id, action, _token: csrfToken }).toString(),
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
