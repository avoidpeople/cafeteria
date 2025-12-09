<?php

namespace App\Application\Controller;

use App\Application\Service\ComboService;
use App\Application\Service\MenuService;
use App\Domain\MenuItem;
use App\Infrastructure\ViewRenderer;
use function translate;

class MenuController
{
    public function __construct(private MenuService $menuService, private ViewRenderer $view)
    {
    }

    public function index(): string
    {
        $search = trim($_GET['search'] ?? '');
        $selectedCategory = trim($_GET['category'] ?? '');
        $categories = $this->menuService->categories();
        $menuItems = $this->menuService->menuItems($search, $selectedCategory);
        $comboOptions = $this->buildComboOptions($menuItems);

        return $this->view->render('menu', [
            'search' => $search,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'menuItems' => $menuItems,
            'comboOptions' => $comboOptions,
        ]);
    }

    /**
     * @param MenuItem[] $menuItems
     */
    private function buildComboOptions(array $menuItems): array
    {
        $basePrice = ComboService::BASE_PRICE;
        $categories = [
            'main' => [
                'key' => 'main',
                'label' => translate('combo.category.main'),
                'hint' => translate('combo.category.main_hint'),
                'required' => true,
                'items' => [],
                'skip' => null,
            ],
            'garnish' => [
                'key' => 'garnish',
                'label' => translate('combo.category.garnish'),
                'hint' => translate('combo.category.garnish_hint'),
                'required' => true,
                'items' => [],
                'skip' => null,
            ],
            'soup' => [
                'key' => 'soup',
                'label' => translate('combo.category.soup'),
                'hint' => translate('combo.category.soup_hint'),
                'required' => false,
                'items' => [],
                'skip' => [
                    'title' => translate('menu.combo_modal.soup_skip'),
                    'description' => translate('menu.combo_modal.soup_skip_desc'),
                    'tag' => translate('menu.combo_modal.soup_skip_tag'),
                ],
            ],
        ];
        $roles = [];
        $customGroups = [];

        foreach ($menuItems as $item) {
            if (!$item instanceof MenuItem) {
                continue;
            }
            $role = $item->categoryRole ?? 'main';
            $key = $role;
            $label = $item->category ?? translate('menu.card.no_category');
            if ($role === 'custom') {
                $slug = $item->categoryKey ?: $this->slugify($label . '-' . $item->id);
                $key = 'extra:' . $slug;
                if (!isset($customGroups[$key])) {
                    $customGroups[$key] = [
                        'key' => $key,
                        'label' => $label,
                        'hint' => translate('combo.category.optional_hint'),
                        'required' => false,
                        'items' => [],
                        'skip' => [
                            'title' => translate('combo.category.skip_generic'),
                            'description' => translate('combo.category.skip_desc'),
                            'tag' => translate('combo.category.skip_tag'),
                        ],
                    ];
                }
                $group =& $customGroups[$key];
            } else {
                if (!isset($categories[$role])) {
                    continue;
                }
                $group =& $categories[$role];
            }

            $group['items'][] = $this->mapComboItem($item, $key, $group['label'], (bool)$group['required']);
            $roles[$item->id] = $key;
            unset($group);
        }

        $ordered = [
            $categories['main'],
            $categories['garnish'],
            $categories['soup'],
        ];
        if ($customGroups) {
            $extraGroups = array_values($customGroups);
            usort($extraGroups, static fn (array $a, array $b) => strnatcasecmp($a['label'], $b['label']));
            foreach ($extraGroups as $group) {
                $ordered[] = $group;
            }
        }

        return [
            'categories' => $ordered,
            'roles' => $roles,
            'base_price' => $basePrice,
            'counts' => [
                'main' => count($categories['main']['items']),
                'garnish' => count($categories['garnish']['items']),
                'soup' => count($categories['soup']['items']),
            ],
        ];
    }

    private function mapComboItem(MenuItem $item, string $key, string $categoryLabel, bool $required): array
    {
        $price = $required ? null : ($item->price > 0 ? round($item->price, 2) : null);
        return [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $categoryLabel,
            'role' => $key,
            'image' => $item->primaryImage(),
            'description' => $item->description ?? null,
            'price' => $price,
            'unique' => $item->isUnique(),
        ];
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value);
        return trim((string)$value, '-') ?: 'extra';
    }
}
