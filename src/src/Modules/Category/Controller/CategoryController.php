<?php
namespace Modules\Category\Controller;

use Core\Renderer;
use Modules\Category\Domain\Category;

class CategoryController
{
    private $repo;
    private $achRepo;
    private Renderer $renderer;

    public function __construct($repo, $achRepo = null, Renderer $renderer = null)
    {
        $this->repo = $repo;
        $this->achRepo = $achRepo;
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

    public function sortAlphabetically($categoryId)
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_csrf']);
            return;
        }

        if (!$this->achRepo) {
            http_response_code(500);
            echo json_encode(['error' => 'achievement_repo_not_available']);
            return;
        }

        // Get all achievements in this category
        $achievements = $this->achRepo->all((int)$categoryId);
        
        // Sort alphabetically by title
        usort($achievements, function($a, $b) {
            return strcasecmp($a->title, $b->title);
        });
        
        // Update display_order for each achievement
        $orders = [];
        foreach ($achievements as $idx => $ach) {
            $orders[$ach->id] = $idx + 1;
        }
        
        $this->achRepo->reorder($orders);
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
