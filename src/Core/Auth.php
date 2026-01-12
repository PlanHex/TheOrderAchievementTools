<?php
namespace Core;

class Auth
{
    /**
     * Enforce Basic Auth only when mode === 'production' and credentials are set in config.
     * Config expected: ['auth' => ['user' => 'username', 'pass' => 'secret']]
     */
    public static function requireIfProduction(array $appConfig): void
    {
        $mode = $appConfig['mode'] ?? 'demo';
        if ($mode !== 'production') {
            return;
        }

        $auth = $appConfig['auth'] ?? null;
        if (!$auth || empty($auth['user']) || !isset($auth['pass'])) {
            // No credentials configured — deny access by default
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="Protected"');
            echo 'Authentication required (no credentials configured)';
            exit;
        }

        $providedUser = $_SERVER['PHP_AUTH_USER'] ?? null;
        $providedPass = $_SERVER['PHP_AUTH_PW'] ?? null;
        if (!hash_equals($auth['user'], (string)$providedUser) || !hash_equals($auth['pass'], (string)$providedPass)) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="Protected"');
            echo 'Invalid credentials';
            exit;
        }
    }
}
