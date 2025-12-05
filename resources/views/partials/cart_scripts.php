<?php if (!empty($cartItems)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rows = Array.from(document.querySelectorAll('.cart-row'));
    const selectedTotalEl = document.getElementById('selectedTotal');
    const selectedCountEl = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitOrder');
    const tbody = document.getElementById('cartTableBody');
    const STORAGE_KEY = 'cart_selected_items';
    const savedState = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    let storageDirty = false;

    function updateTotals() {
        let total = 0;
        let count = 0;
        rows.forEach(row => {
            if (row.dataset.available === '0') {
                row.classList.remove('row-selected');
                return;
            }
            const checkbox = row.querySelector('.toggle-item');
            if (checkbox.checked) {
                row.classList.add('row-selected');
                total += parseFloat(row.dataset.sum);
                count += parseInt(row.dataset.qty, 10);
            } else {
                row.classList.remove('row-selected');
            }
        });
        selectedTotalEl.textContent = total.toFixed(2) + ' €';
        selectedCountEl.textContent = count;
        submitBtn.disabled = total === 0;
    }

    function sortRows() {
        const sorted = rows.slice().sort((a, b) => {
            const aAvailable = a.dataset.available !== '0';
            const bAvailable = b.dataset.available !== '0';
            if (aAvailable !== bAvailable) {
                return aAvailable ? -1 : 1;
            }
            if (!aAvailable && !bAvailable) {
                return 0;
            }
            const aChecked = a.querySelector('.toggle-item').checked ? 1 : 0;
            const bChecked = b.querySelector('.toggle-item').checked ? 1 : 0;
            return bChecked - aChecked;
        });
        sorted.forEach(row => tbody.appendChild(row));
    }

    function updateChipText(checkbox) {
        const chip = checkbox.closest('.toggle-chip');
        const textEl = chip.querySelector('.chip-text');
        if (textEl) {
            textEl.textContent = checkbox.checked ? 'В заказе' : 'Не выбрано';
        }
        chip.classList.toggle('active', checkbox.checked);
    }

    rows.forEach(row => {
        const checkbox = row.querySelector('.toggle-item');
        const id = row.dataset.id;
        const isAvailable = row.dataset.available !== '0';

        if (!isAvailable) {
            row.classList.add('row-disabled');
            if (savedState.hasOwnProperty(id)) {
                delete savedState[id];
                storageDirty = true;
            }
            checkbox.checked = false;
            checkbox.disabled = true;
            const chip = row.querySelector('.toggle-chip');
            if (chip) {
                chip.classList.remove('active');
                chip.classList.add('disabled');
                const textEl = chip.querySelector('.chip-text');
                if (textEl) {
                    textEl.textContent = 'Нет в меню';
                }
            }
            return;
        }

        if (savedState[id] === false) {
            checkbox.checked = false;
        } else if (savedState[id] === true) {
            checkbox.checked = true;
        }
        updateChipText(checkbox);

        checkbox.addEventListener('change', () => {
            savedState[id] = checkbox.checked;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(savedState));
            updateChipText(checkbox);
            sortRows();
            updateTotals();
        });

        row.addEventListener('click', (event) => {
            if (!isAvailable) {
                return;
            }
            if (event.target.closest('.no-row-toggle') || event.target.closest('.toggle-chip') || event.target.tagName === 'A') {
                return;
            }
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        });
    });

    if (storageDirty) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(savedState));
    }

    sortRows();
    updateTotals();
});
</script>
<?php endif; ?>
