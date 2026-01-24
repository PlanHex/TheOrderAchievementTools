<?php
namespace Modules\Achievement\Controller;

use Core\Renderer;
use Modules\Achievement\Domain\Achievement;

class AchievementController
{
    private $repo;
    private $catRepo;
    private Renderer $renderer;

    public function __construct($repo, $catRepo, Renderer $renderer)
    {
        $this->repo = $repo;
        $this->catRepo = $catRepo;
        $this->renderer = $renderer;
    }

    public function index()
    {
        $catId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $achievements = $this->repo->all($catId);
        $categories = $this->catRepo->all();
        $this->renderer->renderWithLayout('src/Modules/Achievement/Views/index', ['achievements' => $achievements, 'categories' => $categories, 'category_id' => $catId]);
    }

    public function create()
    {
        $categories = $this->catRepo->all();
        $this->renderer->renderWithLayout('src/Modules/Achievement/Views/create', ['categories' => $categories]);
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

        $data = $_POST;
        $ach = new Achievement(null, (int)$data['category_id'], $data['title'] ?? '', $data['description'] ?? null, (int)($data['points'] ?? 0), $data['image_url'] ?? null, 0);
        $this->repo->save($ach);
        header('Location: /achievements');
        exit;
    }

    public function edit($id)
    {
        $achievement = $this->repo->find((int)$id);
        if (!$achievement) {
            http_response_code(404);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Achievement not found</h1></main>';
            echo $this->renderer->render('footer');
            return;
        }
        $categories = $this->catRepo->all();
        $this->renderer->renderWithLayout('src/Modules/Achievement/Views/edit', ['achievement' => $achievement, 'categories' => $categories]);
    }

    public function update($id)
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Invalid CSRF token</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $achievement = $this->repo->find((int)$id);
        if (!$achievement) {
            http_response_code(404);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Achievement not found</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $data = $_POST;
        $achievement->categoryId = (int)$data['category_id'];
        $achievement->title = $data['title'] ?? '';
        $achievement->description = $data['description'] ?? null;
        $achievement->points = (int)($data['points'] ?? 0);
        $achievement->imageUrl = $data['image_url'] ?? null;
        
        $this->repo->save($achievement);
        header('Location: /achievements');
        exit;
    }

    public function sort($categoryId)
    {
        $category = $this->catRepo->find((int)$categoryId);
        if (!$category) {
            http_response_code(404);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Category not found</h1></main>';
            echo $this->renderer->render('footer');
            return;
        }

        $achievements = $this->repo->all((int)$categoryId);
        $this->renderer->renderWithLayout('src/Modules/Achievement/Views/sort', ['category' => $category, 'achievements' => $achievements]);
    }

    public function reorder($categoryId)
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Invalid CSRF token</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $orders = $_POST['orders'] ?? [];
        // sanitize to int map
        $map = [];
        foreach ($orders as $id => $val) {
            $map[(int)$id] = (int)$val;
        }

        $this->repo->reorder($map);
        header('Location: /achievements?category=' . (int)$categoryId);
        exit;
    }
}
