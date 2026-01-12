<?php
namespace Core;

class Container
{
    private array $services = [];
    private array $instances = [];
    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        // If no explicit config provided, attempt to load from config/*.php
        if (empty($this->config)) {
            $appConfigFile = __DIR__ . '/../../config/app.php';
            $dbConfigFile = __DIR__ . '/../../config/database.php';
            $this->config['app'] = file_exists($appConfigFile) ? require $appConfigFile : [];
            $this->config['db'] = file_exists($dbConfigFile) ? require $dbConfigFile : [];
        }
        $this->registerDefaults();
    }

    private function registerDefaults(): void
    {
        // Register database service
        $this->set('database', function ($c) {
            $dbConf = $c->config('db', []);
            return new \Core\Database($dbConf);
        });

        // repositories: pick InMemory vs MySQL based on app.mode
        $mode = $this->config['app']['mode'] ?? ($this->config['app'] ?? [])['mode'] ?? 'demo';
        $dataDir = __DIR__ . '/../../data';

        // Category repository
        $this->set('category_repository', function ($c) use ($mode, $dataDir) {
            if ($mode === 'production') {
                return new \Infrastructure\Persistence\MySQL\CategoryRepository($c->get('database'));
            }
            return new \Infrastructure\Persistence\InMemory\CategoryRepository($dataDir);
        });

        // Achievement repository
        $this->set('achievement_repository', function ($c) use ($mode, $dataDir) {
            if ($mode === 'production') {
                return new \Infrastructure\Persistence\MySQL\AchievementRepository($c->get('database'));
            }
            return new \Infrastructure\Persistence\InMemory\AchievementRepository($dataDir);
        });

        // User repository
        $this->set('user_repository', function ($c) use ($mode, $dataDir) {
            if ($mode === 'production') {
                return new \Infrastructure\Persistence\MySQL\UserRepository($c->get('database'));
            }
            return new \Infrastructure\Persistence\InMemory\UserRepository($dataDir);
        });
    }

    public function set(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }

    public function get(string $name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->services[$name])) {
            throw new \RuntimeException("Service '{$name}' is not registered.");
        }

        $this->instances[$name] = call_user_func($this->services[$name], $this);
        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
