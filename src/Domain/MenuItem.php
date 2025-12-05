<?php

namespace App\Domain;

class MenuItem
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $ingredients,
        public float $price,
        public ?string $category,
        public ?string $imageUrl,
        public array $gallery = [],
        public bool $isToday = false,
    ) {
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
}
