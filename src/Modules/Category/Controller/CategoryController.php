<?php
namespace Modules\Category\Controller;

use Core\Renderer;
use Modules\Category\Domain\Category;

class CategoryController
{
    private $repo;
    private Renderer $renderer;

    public function __construct($repo, Renderer $renderer)
    {
        $this->repo = $repo;
        $this->renderer = $renderer;
    }

    public function index()
    {
        $categories = $this->repo->all();
        $this->renderer->renderWithLayout('src/Modules/Category/Views/index', ['categories' => $categories]);
    }

    public function create()
    {
        $this->renderer->renderWithLayout('src/Modules/Category/Views/create');
    }

    public function store()
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Invalid CSRF token</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $cat = new Category(null, $name, 0);
        $this->repo->save($cat);
        header('Location: /categories');
        exit;
    }
}
