<?php
namespace Modules\User\Controller;

use Core\Renderer;
use Modules\User\Domain\User;

class UserController
{
    private $repo;
    private $achRepo;
    private Renderer $renderer;

    public function __construct($repo, $achRepo, Renderer $renderer)
    {
        $this->repo = $repo;
        $this->achRepo = $achRepo;
        $this->renderer = $renderer;
    }

    public function index()
    {
        $users = $this->repo->all();
        $this->renderer->renderWithLayout('src/Modules/User/Views/index', ['users' => $users]);
    }

    public function show($id)
    {
        $user = $this->repo->find((int)$id);
        if (!$user) {
            http_response_code(404);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>User not found</h1></main>';
            echo $this->renderer->render('footer');
            return;
        }

        $ua = [];
        if (method_exists($this->repo, 'getUserAchievements')) {
            $ua = $this->repo->getUserAchievements((int)$id);
        }

        $assigned = [];
        foreach ($ua as $aid => $order) {
            $ach = $this->achRepo->find((int)$aid);
            if ($ach) $assigned[] = $ach;
        }

        $this->renderer->renderWithLayout('src/Modules/User/Views/show', ['user' => $user, 'achievements' => $assigned]);
    }

    public function create()
    {
        $this->renderer->renderWithLayout('src/Modules/User/Views/create');
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
        $user = new User(null, $name);
        $this->repo->save($user);
        header('Location: /users');
        exit;
    }
}
