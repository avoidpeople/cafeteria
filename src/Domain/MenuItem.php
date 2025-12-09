<?php

namespace App\Domain;

class MenuItem
{
    public ?string $title = null;
    public ?string $description = null;
    public ?string $category = null;
    public ?string $ingredients = null;

    private ?string $legacyTitle;
    private ?string $legacyDescription;
    private ?string $legacyCategory;
    private ?string $legacyIngredients;

    public function __construct(
        public int $id,
        public ?string $nameOriginal,
        public ?string $nameRu,
        public ?string $nameLv,
        public ?string $descriptionOriginal,
        public ?string $descriptionRu,
        public ?string $descriptionLv,
        public ?string $ingredientsOriginal,
        public ?string $ingredientsRu,
        public ?string $ingredientsLv,
        public float $price,
        public bool $useManualPrice = false,
        public ?string $categoryOriginal = null,
        public ?string $categoryRu = null,
        public ?string $categoryLv = null,
        public ?string $imageUrl = null,
        public array $gallery = [],
        public bool $isToday = false,
        ?string $legacyTitle = null,
        ?string $legacyDescription = null,
        ?string $legacyCategory = null,
        ?string $legacyIngredients = null,
    ) {
        $this->legacyTitle = $legacyTitle;
        $this->legacyDescription = $legacyDescription;
        $this->legacyCategory = $legacyCategory;
        $this->legacyIngredients = $legacyIngredients;
        $this->title = $this->getNameAttribute();
        $this->description = $this->getDescriptionAttribute();
        $this->category = $this->getCategoryAttribute();
        $this->ingredients = $this->getIngredientsAttribute();
    }

    public function isUnique(): bool
    {
        return $this->useManualPrice;
    }

    public function galleryImages(): array
    {
        $images = $this->gallery;
        if ($this->imageUrl) {
            array_unshift($images, $this->imageUrl);
        }
        $images = array_values(array_unique(array_filter($images, fn ($img) => is_string($img) && $img !== '')));
        return $images;
    }

    public function primaryImage(): ?string
    {
        $images = $this->galleryImages();
        return $images[0] ?? null;
    }

    public function getNameAttribute(?string $locale = null): ?string
    {
        return $this->resolveLocalizedValue(
            [
                'ru' => $this->nameRu,
                'lv' => $this->nameLv,
            ],
            $this->nameOriginal,
            $this->legacyTitle,
            $locale
        );
    }

    public function getDescriptionAttribute(?string $locale = null): ?string
    {
        return $this->resolveLocalizedValue(
            [
                'ru' => $this->descriptionRu,
                'lv' => $this->descriptionLv,
            ],
            $this->descriptionOriginal,
            $this->legacyDescription,
            $locale
        );
    }

    public function getCategoryAttribute(?string $locale = null): ?string
    {
        return $this->resolveLocalizedValue(
            [
                'ru' => $this->categoryRu,
                'lv' => $this->categoryLv,
            ],
            $this->categoryOriginal,
            $this->legacyCategory,
            $locale
        );
    }

    public function getIngredientsAttribute(?string $locale = null): ?string
    {
        return $this->resolveLocalizedValue(
            [
                'ru' => $this->ingredientsRu,
                'lv' => $this->ingredientsLv,
            ],
            $this->ingredientsOriginal,
            $this->legacyIngredients,
            $locale
        );
    }

    private function resolveLocalizedValue(array $localized, ?string $original, ?string $fallback, ?string $targetLocale = null): ?string
    {
        $locale = $targetLocale ?? $this->detectLocale();
        $localizedValue = $localized[$locale] ?? null;
        if ($this->isFilled($localizedValue)) {
            return $localizedValue;
        }

        foreach ($localized as $value) {
            if ($this->isFilled($value)) {
                return $value;
            }
        }

        if ($this->isFilled($original)) {
            return $original;
        }

        if ($this->isFilled($fallback)) {
            return $fallback;
        }

        return null;
    }

    private function detectLocale(): string
    {
        if (function_exists('currentLocale')) {
            return currentLocale();
        }
        return 'ru';
    }

    private function isFilled(?string $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
