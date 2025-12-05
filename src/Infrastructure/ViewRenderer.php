<?php

namespace App\Infrastructure;

class ViewRenderer
{
    public function __construct(private string $basePath, private string $layout)
    {
    }

    public function render(string $view, array $params = []): string
    {
        $viewFile = $this->basePath . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View {$view} not found");
        }

        extract($params, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($this->layout && file_exists($this->layout)) {
            ob_start();
            require $this->layout;
            return ob_get_clean();
        }

        return $content;
    }
}
