<?php
namespace Core;

class Renderer
{
    private string $templatesPath;

    public function __construct(string $templatesPath = __DIR__ . '/../../templates')
    {
        $this->templatesPath = rtrim($templatesPath, "/\\");
    }

    public function render(string $viewPath, array $data = []): string
    {
        $full = $this->viewFile($viewPath);

        if (!file_exists($full)) {
            throw new \RuntimeException("View not found: {$full}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $full;
        return (string) ob_get_clean();
    }

    public function renderWithLayout(string $viewPath, array $data = []): void
    {
        echo $this->render('header', $data);
        echo $this->render($viewPath, $data);
        echo $this->render('footer', $data);
    }

    private function viewFile(string $viewPath): string
    {
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $viewPath);
        // allow shorthand 'Modules/Achievement/Views/index' or 'header'
        if (strpos($path, DIRECTORY_SEPARATOR) === false) {
            $candidate = $this->templatesPath . DIRECTORY_SEPARATOR . $path . '.php';
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $candidate = __DIR__ . '/../../' . $path . '.php';
        return $candidate;
    }

    public static function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
