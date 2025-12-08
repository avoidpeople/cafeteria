<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;

class AdminMenuService
{
    public function __construct(private MenuRepositoryInterface $menuRepository)
    {
    }

    /** @return MenuItem[] */
    public function list(): array
    {
        return $this->menuRepository->findAll();
    }

    public function delete(int $id): void
    {
        $this->menuRepository->delete($id);
    }

    /** @return MenuItem[] */
    public function today(): array
    {
        return $this->menuRepository->findToday();
    }

    public function todayIds(): array
    {
        return $this->menuRepository->getTodayIds();
    }

    public function updateTodaySelection(array $ids): void
    {
        $this->menuRepository->setTodayMenu($ids);
    }

    public function save(array $data, array $files): array
    {
        $errors = [];
        $title = trim($data['title'] ?? '');
        $price = (float)($data['price'] ?? 0);
        $useManualPrice = !empty($data['use_manual_price']);
        if ($title === '') {
            $errors[] = 'Название обязательно.';
        }
        if ($useManualPrice && $price <= 0) {
            $errors[] = 'Укажите цену вручную для уникального блюда.';
        }
        if (!$useManualPrice) {
            $price = 0.0;
        }

        $gallery = $this->processGallery($files, $data['existing_gallery'] ?? '');
        if (isset($gallery['errors'])) {
            $errors = array_merge($errors, $gallery['errors']);
        }
        $images = $gallery['images'] ?? [];
        $cover = $images[0] ?? ($data['current_image'] ?? null);
        if ($cover && !in_array($cover, $images, true)) {
            array_unshift($images, $cover);
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $payload = [
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'ingredients' => trim($data['ingredients'] ?? ''),
            'price' => $price,
            'category' => trim($data['category'] ?? ''),
            'image_url' => $images[0] ?? null,
            'image_gallery' => $images,
            'use_manual_price' => $useManualPrice,
        ];

        if (!empty($data['id'])) {
            $this->menuRepository->update((int)$data['id'], $payload);
        } else {
            $this->menuRepository->create($payload);
        }

        return ['success' => true];
    }

    private function processGallery(array $files, string $existingGallery): array
    {
        $images = array_filter(array_map('trim', json_decode($existingGallery, true) ?? []));
        $errors = [];
        if (isset($files['image']['name']) && is_array($files['image']['name'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            foreach ($files['image']['name'] as $index => $name) {
                if (empty($name)) {
                    continue;
                }
                $tmp = $files['image']['tmp_name'][$index] ?? '';
                if (!is_uploaded_file($tmp)) {
                    continue;
                }
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
                if (!isset($allowed[$mime])) {
                    $errors[] = 'Недопустимый формат изображения: ' . htmlspecialchars($name);
                    continue;
                }
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '_' . $index . '.' . $allowed[$mime];
                $target = __DIR__ . '/../../../public/assets/images/' . $filename;
                if (move_uploaded_file($tmp, $target)) {
                    $images[] = $filename;
                }
            }
        }
        return ['images' => array_values(array_unique(array_filter($images))), 'errors' => $errors];
    }
}
