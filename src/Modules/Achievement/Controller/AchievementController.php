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
}
