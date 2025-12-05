<?php if (!empty($_SESSION['toast'])):
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
    $toastType = in_array($toast['type'], ['primary','success','info','warning','danger']) ? $toast['type'] : 'primary';
?>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="globalToast" class="toast text-bg-<?= $toastType ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($toast['message']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toastEl = document.getElementById('globalToast');
    if (toastEl) {
        const t = new bootstrap.Toast(toastEl, { delay: 4000 });
        t.show();
    }
});
</script>
<?php endif; ?>
