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

        // use interface method
        $ua = $this->repo->getUserAchievements((int)$id);

        // build ordered list
        $assigned = [];
        foreach ($ua as $aid => $order) {
            $ach = $this->achRepo->find((int)$aid);
            if ($ach) $assigned[$order] = $ach;
        }
        ksort($assigned);
        $assigned = array_values($assigned);

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

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Validation Error</h1><p>User name cannot be empty.</p>';
            echo '<a href="/users/create">Back</a></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $user = new User(null, $name);
        $this->repo->save($user);
        header('Location: /users');
        exit;
    }

    public function edit($id)
    {
        $user = $this->repo->find((int)$id);
        if (!$user) {
            http_response_code(404);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>User not found</h1></main>';
            echo $this->renderer->render('footer');
            return;
        }

        $all = $this->achRepo->all();
        $assignedMap = $this->repo->getUserAchievements((int)$id);
        $this->renderer->renderWithLayout('src/Modules/User/Views/edit', ['user' => $user, 'achievements' => $all, 'assignedMap' => $assignedMap]);
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

        $user = $this->repo->find((int)$id);
        if (!$user) {
            http_response_code(404);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>User not found</h1></main>';
            echo $this->renderer->render('footer');
            return;
        }

        $user->name = $_POST['name'] ?? $user->name;
        $this->repo->save($user);
        header('Location: /users');
        exit;
    }

    public function delete($id)
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Invalid CSRF token</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        try {
            $this->repo->delete((int)$id);
            header('Location: /users');
        } catch (\PDOException $e) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem">';
            echo '<h1>Cannot Delete User</h1>';
            echo '<p>This user has achievements assigned. Please remove all assignments first.</p>';
            echo '<a href="/users">Back to Users</a>';
            echo '</main>';
            echo $this->renderer->render('footer');
        }
        exit;
    }

    public function addAchievement($userId)
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Invalid CSRF token</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $aid = isset($_POST['achievement_id']) ? (int)$_POST['achievement_id'] : null;
        $display = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        if ($aid === null) {
            header('Location: /users/' . (int)$userId . '/edit');
            exit;
        }

        $this->repo->addAchievement((int)$userId, $aid, $display);
        header('Location: /users/' . (int)$userId . '/edit');
        exit;
    }

    public function removeAchievement($userId, $achievementId)
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!\Core\Csrf::validate($token)) {
            http_response_code(400);
            echo $this->renderer->render('header');
            echo '<main style="padding:1rem"><h1>Invalid CSRF token</h1></main>';
            echo $this->renderer->render('footer');
            exit;
        }

        $this->repo->removeAchievement((int)$userId, (int)$achievementId);
        header('Location: /users/' . (int)$userId . '/edit');
        exit;
    }

    public function reorderAchievements($userId)
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
        $map = [];
        foreach ($orders as $id => $val) {
            $map[(int)$id] = (int)$val;
        }
        $this->repo->reorderAchievements((int)$userId, $map);
        header('Location: /users/' . (int)$userId . '/edit');
        exit;
    }
}
