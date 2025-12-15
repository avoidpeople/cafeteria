(() => {
    const container = document.getElementById('notificationsContainer');
    const badge = document.getElementById('notificationsBadge');
    const drawer = document.getElementById('notificationDrawer');
    const clearBtn = document.getElementById('clearNotificationsBtn');
    if (!container || !badge) {
        return;
    }
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
    const emptyText = container.dataset.emptyText || 'No notifications yet';
    const loginText = container.dataset.loginText || 'Sign in to receive order updates.';
    const noneText = container.dataset.noneText || 'No orders yet';
    const clearedText = container.dataset.clearedText || emptyText;

    const storageKey = 'notifLastSeen';
    let lastSeenId = parseInt(localStorage.getItem(storageKey), 10) || 0;
    let latestFetchedId = lastSeenId;
    const amountLabel = container.dataset.amountLabel || 'Total:';
    const locale = document.documentElement.getAttribute('lang') || 'ru';

    const render = (data) => {
        if (!data || !data.authenticated) {
            badge.classList.add('d-none');
            container.innerHTML = `<div class="text-muted">${loginText}</div>`;
            return;
        }

        const items = data.items || [];

        if (!items.length) {
            container.innerHTML = `<div class="text-muted">${noneText}</div>`;
            return;
        }

        const unseenCount = items.filter((item) => item.id > lastSeenId).length;
        if (unseenCount > 0 && !(drawer?.classList.contains('show'))) {
            badge.textContent = unseenCount;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }

        container.innerHTML = '';
        let maxId = latestFetchedId;
        items.forEach((item) => {
            if (item.id > maxId) {
                maxId = item.id;
            }
            const isNew = item.id > lastSeenId;
            const card = document.createElement('a');
            card.href = item.link;
            card.className = `notification-card status-${item.status} ${isNew ? 'notification-card-new' : ''}`;
            card.dataset.id = item.id;
            card.innerHTML = `
                <div class="notification-card__header">
                    <strong>${item.title}</strong>
                    <small>${item.created_at ? new Date(item.created_at).toLocaleString(locale, {hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit'}) : ''}</small>
                </div>
                <p class="notification-card__text">${item.message}</p>
                <div class="notification-card__meta">${amountLabel} ${Number(item.amount ?? 0).toFixed(2)} €</div>
            `;
            container.appendChild(card);

        });
        latestFetchedId = maxId;
    };

    const fetchNotifications = async () => {
        try {
            const response = await fetch('/api/notifications', {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                return;
            }
            const payload = await response.json();
            render(payload);
        } catch (e) {
            // eslint-disable-line no-console
        }
    };

    const markAsSeen = () => {
        if (latestFetchedId <= lastSeenId) {
            return;
        }
        lastSeenId = latestFetchedId;
        localStorage.setItem(storageKey, String(lastSeenId));
        badge.classList.add('d-none');
        container.querySelectorAll('.notification-card-new').forEach((el) => el.classList.remove('notification-card-new'));
    };

    const clearNotifications = async () => {
        try {
            const response = await fetch('/api/notifications/clear', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                credentials: 'same-origin',
                body: new URLSearchParams({ _token: csrfToken }).toString(),
            });
            if (response.ok) {
                container.innerHTML = `<div class="text-muted">${clearedText}</div>`;
                badge.classList.add('d-none');
                lastSeenId = 0;
                latestFetchedId = 0;
                localStorage.removeItem(storageKey);
            }
        } catch (e) {
            // eslint-disable-line no-console
        }
    };

    drawer?.addEventListener('shown.bs.offcanvas', markAsSeen);
    clearBtn?.addEventListener('click', clearNotifications);

    fetchNotifications();
    setInterval(fetchNotifications, 8000);
})();
