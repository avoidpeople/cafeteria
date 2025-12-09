<?php

namespace App\Application\Controller\Admin;

use App\Application\Service\AdminMenuService;
use App\Application\Service\AuthService;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;
use function translate;

class MenuController
{
    public function __construct(
        private AuthService $authService,
        private AdminMenuService $menuService,
        private ViewRenderer $view,
        private SessionManager $session
    ) {
    }

    private function requireAdmin(): void
    {
        if ($this->session->get('role') !== 'admin') {
            header('Location: /');
            exit;
        }
    }

    public function index(): string
    {
        $this->requireAdmin();
        $items = $this->menuService->list();
        $todayItems = $this->menuService->today();
        $todayIds = $this->menuService->todayIds();
        $errors = $this->session->get('admin_menu_errors', []);
        $this->session->unset('admin_menu_errors');
        return $this->view->render('admin/menu', [
            'title' => 'Doctor Gorilka — ' . translate('admin.menu.title'),
            'items' => $items,
            'errors' => $errors,
            'todayItems' => $todayItems,
            'todayIds' => $todayIds,
        ]);
    }

    public function save(): void
    {
        $this->requireAdmin();
        $result = $this->menuService->save($_POST, $_FILES);
        if (!$result['success']) {
            $this->session->set('admin_menu_errors', $result['errors']);
        } else {
            setToast(translate('admin.menu.toast.saved'), 'success');
        }
        header('Location: /admin/menu');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->menuService->delete($id);
            setToast(translate('admin.menu.toast.deleted', ['id' => $id]), 'warning');
        }
        header('Location: /admin/menu');
        exit;
    }

    public function today(): void
    {
        $this->requireAdmin();
        $ids = $_POST['today_ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        $this->menuService->updateTodaySelection($ids);
        setToast(translate('admin.menu.toast.today_updated'), 'success');
        header('Location: /admin/menu');
        exit;
    }
}
