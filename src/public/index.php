<?php
session_start();

// very small PSR-4-ish autoloader for this repo
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/..';
    $file = $base . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    // try src/ prefix
    $file = $base . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
});

// bootstrap container and router
$container = new \Core\Container();
$appConfig = require __DIR__ . '/../config/app.php';
// enforce auth in production
\Core\Auth::requireIfProduction($appConfig);
$router = new \Core\Router();
$renderer = new \Core\Renderer(__DIR__ . '/../templates');

// Route definitions
$router->add('GET', '/', function () use ($renderer) {
    echo $renderer->render('header');
    echo '<main style="padding:1rem"><h1>The Order Achievements Tool</h1>';
    echo '<p>Welcome — use the navigation to view Categories, Achievements, or Users.</p>';
    echo '<ul><li><a href="/export/master">Export: Master List (all achievements)</a></li>';
    echo '<li><a href="/export/roster">Export: Complete Roster (all users)</a></li>';
    echo '<li><a href="/export/roster?user_id=1">Export: Individual Roster (user 1)</a></li></ul></main>';
    echo $renderer->render('footer');
});

$router->add('GET', '/categories', function () use ($container, $renderer) {
    $repo = $container->get('category_repository');
    $categories = $repo->all();
    $renderer->renderWithLayout('src/Modules/Category/Views/index', ['categories' => $categories]);
});

$router->add('GET', '/categories/create', function () use ($renderer) {
    echo $renderer->render('header');
    echo (new \Core\Renderer(__DIR__ . '/../templates'))->render('src/Modules/Category/Views/create');
    echo $renderer->render('footer');
});

$router->add('POST', '/categories/store', function () use ($container, $renderer) {
    $ctrl = new \Modules\Category\Controller\CategoryController($container->get('category_repository'), null, $renderer);
    $ctrl->store();
});

$router->add('POST', '/categories/:id/sort-alphabetically', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\Category\Controller\CategoryController($container->get('category_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->sortAlphabetically($id);
});

$router->add('GET', '/achievements', function () use ($container, $renderer) {
    $ctrl = new \Modules\Achievement\Controller\AchievementController($container->get('achievement_repository'), $container->get('category_repository'), $renderer);
    $ctrl->index();
});

$router->add('GET', '/achievements/create', function () use ($container, $renderer) {
    $catRepo = $container->get('category_repository');
    $categories = $catRepo->all();
    $renderer->renderWithLayout('src/Modules/Achievement/Views/create', ['categories' => $categories]);
});

$router->add('GET', '/achievements/:id/edit', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\Achievement\Controller\AchievementController($container->get('achievement_repository'), $container->get('category_repository'), $renderer);
    $ctrl->edit($id);
});

$router->add('GET', '/achievements/:id/sort', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\Achievement\Controller\AchievementController($container->get('achievement_repository'), $container->get('category_repository'), $renderer);
    $ctrl->sort($id);
});

$router->add('POST', '/achievements/:id/reorder', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\Achievement\Controller\AchievementController($container->get('achievement_repository'), $container->get('category_repository'), $renderer);
    $ctrl->reorder($id);
});

$router->add('POST', '/achievements/store', function () use ($container, $renderer) {
    $ctrl = new \Modules\Achievement\Controller\AchievementController($container->get('achievement_repository'), $container->get('category_repository'), $renderer);
    $ctrl->store();
});

$router->add('POST', '/achievements/:id/update', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\Achievement\Controller\AchievementController($container->get('achievement_repository'), $container->get('category_repository'), $renderer);
    $ctrl->update($id);
});

$router->add('GET', '/users', function () use ($container, $renderer) {
    $repo = $container->get('user_repository');
    $users = $repo->all();
    $renderer->renderWithLayout('src/Modules/User/Views/index', ['users' => $users]);
});

$router->add('GET', '/users/create', function () use ($renderer) {
    $renderer->renderWithLayout('src/Modules/User/Views/create');
});

$router->add('POST', '/users/store', function () use ($container, $renderer) {
    $ctrl = new \Modules\User\Controller\UserController($container->get('user_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->store();
});

$router->add('GET', '/users/:id/edit', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\User\Controller\UserController($container->get('user_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->edit($id);
});

$router->add('POST', '/users/:id/update', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\User\Controller\UserController($container->get('user_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->update($id);
});

$router->add('POST', '/users/:id/achievements/add', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\User\Controller\UserController($container->get('user_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->addAchievement($id);
});

$router->add('POST', '/users/:id/achievements/:aid/remove', function ($id, $aid) use ($container, $renderer) {
    $ctrl = new \Modules\User\Controller\UserController($container->get('user_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->removeAchievement($id, $aid);
});

$router->add('POST', '/users/:id/achievements/reorder', function ($id) use ($container, $renderer) {
    $ctrl = new \Modules\User\Controller\UserController($container->get('user_repository'), $container->get('achievement_repository'), $renderer);
    $ctrl->reorderAchievements($id);
});

$router->add('GET', '/export/master', function () use ($container, $renderer) {
    $ctrl = new \Modules\Reports\Controller\ReportsController($container->get('category_repository'), $container->get('achievement_repository'), $container->get('user_repository'), $renderer);
    $ctrl->masterList();
});

$router->add('GET', '/export/roster', function () use ($container, $renderer) {
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $ctrl = new \Modules\Reports\Controller\ReportsController($container->get('category_repository'), $container->get('achievement_repository'), $container->get('user_repository'), $renderer);
    
    if ($userId !== null) {
        $ctrl->rosterUser($userId);
    } else {
        $ctrl->rosterAll();
    }
});

// API endpoint for reordering
$router->add('POST', '/api/reorder', function () use ($container) {
    // Expect JSON body: { type: 'category'|'achievement'|'user', user_id?: int, orders: {id:display, ...}, csrf_token: '...' }
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_json']);
        return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? null;
    if (!\Core\Csrf::validate($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'invalid_csrf']);
        return;
    }

    $type = $input['type'] ?? null;
    $orders = $input['orders'] ?? [];
    if (!in_array($type, ['category','achievement','user'], true) || !is_array($orders)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_payload']);
        return;
    }

    if ($type === 'category') {
        $repo = $container->get('category_repository');
        $repo->reorder($orders);
        echo json_encode(['ok' => true]);
        return;
    }

    if ($type === 'achievement') {
        $repo = $container->get('achievement_repository');
        $repo->reorder($orders);
        echo json_encode(['ok' => true]);
        return;
    }

    if ($type === 'user') {
        $userId = isset($input['user_id']) ? (int)$input['user_id'] : null;
        if ($userId === null) {
            http_response_code(400);
            echo json_encode(['error' => 'missing_user_id']);
            return;
        }
        $repo = $container->get('user_repository');
        $repo->reorderAchievements($userId, $orders);
        echo json_encode(['ok' => true]);
        return;
    }

    http_response_code(400);
    echo json_encode(['error' => 'unknown_type']);
});

// Dispatch
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $path);

