<?php

declare(strict_types=1);

namespace App\Support;

/**
 * 정적 경로 기반의 작은 라우터. (정규식/파라미터 라우트는 필요 시 확장)
 */
final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path    = $this->normalize((string) (parse_url($uri, PHP_URL_PATH) ?: '/'));
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => $path]);

            return;
        }

        echo $handler();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
