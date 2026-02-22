<?php
namespace Modules\Reports\Controller;

use Core\Renderer;

class ReportsController
{
    private $categoryRepo;
    private $achievementRepo;
    private $userRepo;
    private Renderer $renderer;

    public function __construct($categoryRepo, $achievementRepo, $userRepo, Renderer $renderer)
    {
        $this->categoryRepo = $categoryRepo;
        $this->achievementRepo = $achievementRepo;
        $this->userRepo = $userRepo;
        $this->renderer = $renderer;
    }

    public function masterList()
    {
        $categories = $this->categoryRepo->all();
        $grouped = [];
        foreach ($categories as $cat) {
            $achs = $this->achievementRepo->all($cat->id);
            $grouped[$cat->id] = ['category' => $cat, 'achievements' => $achs];
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->renderer->render('src/Modules/Reports/Views/master', ['groups' => $grouped]);
    }

    public function rosterAll()
    {
        $users = $this->userRepo->all();
        $data = [];
        foreach ($users as $user) {
            $ua = $this->userRepo->getUserAchievements($user->id);
            $assigned = [];
            foreach ($ua as $aid => $order) {
                $ach = $this->achievementRepo->find((int)$aid);
                if ($ach) {
                    $assigned[$order] = $ach;
                }
            }
            ksort($assigned);
            $data[] = ['user' => $user, 'achievements' => array_values($assigned)];
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->renderer->render('src/Modules/Reports/Views/roster_all', ['usersData' => $data]);
    }

    public function rosterUser(int $userId)
    {
        $user = $this->userRepo->find($userId);
        if (!$user) {
            http_response_code(404);
            return;
        }
        $ua = $this->userRepo->getUserAchievements($userId);
        $assigned = [];
        foreach ($ua as $aid => $order) {
            $ach = $this->achievementRepo->find((int)$aid);
            if ($ach) {
                $assigned[$order] = $ach;
            }
        }
        ksort($assigned);
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->renderer->render('src/Modules/Reports/Views/roster', ['user' => $user, 'achievements' => array_values($assigned)]);
    }
}
