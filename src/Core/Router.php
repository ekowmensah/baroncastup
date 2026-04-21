<?php

namespace SmartCast\Core;

/**
 * Simple Router Class
 */
class Router
{
    private $routes = [];
    private $middlewares = [];
    private $currentGroup = [];
    
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function group($attributes, $callback)
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup = array_merge($this->currentGroup, $attributes);
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
    }
    
    private function addRoute($method, $path, $handler)
    {
        $prefix = isset($this->currentGroup['prefix']) ? $this->currentGroup['prefix'] : '';
        $middleware = isset($this->currentGroup['middleware']) ? $this->currentGroup['middleware'] : null;
        
        $fullPath = $prefix . $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Get base path from APP_URL
        $appUrlParts = parse_url(APP_URL);
        $basePath = $appUrlParts['path'] ?? '';
        
        // Handle both mod_rewrite and non-mod_rewrite scenarios
        if (!empty($basePath)) {
            if (strpos($path, $basePath . '/index.php') === 0) {
                // Non-mod_rewrite: /smartcast/index.php/login
                $path = substr($path, strlen($basePath . '/index.php'));
            } elseif (strpos($path, $basePath) === 0) {
                // mod_rewrite: /smartcast/login
                $path = substr($path, strlen($basePath));
            }
        }
        
        if (empty($path)) {
            $path = '/';
        }
        
        // Debug output (remove in production)
        if (APP_DEBUG) {
            error_log("Router Debug - Method: $method, Path: $path, Original URI: " . $_SERVER['REQUEST_URI']);
            error_log("Registered routes: " . print_r(array_map(function($r) { return $r['method'] . ' ' . $r['path']; }, $this->routes), true));
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Check middleware
                if ($route['middleware'] && !$this->checkMiddleware($route['middleware'])) {
                    $this->redirect('/login');
                    return;
                }
                
                $params = $this->extractParams($route['path'], $path);
                $this->callHandler($route['handler'], $params);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        $this->show404();
    }
    
    private function matchPath($routePath, $requestPath)
    {
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath)
    {
        $params = [];
        
        // Extract parameter names from route
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Extract values from request path
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            
            for ($i = 0; $i < count($paramNames[1]); $i++) {
                $params[$paramNames[1][$i]] = $matches[$i] ?? null;
            }
        }
        
        return $params;
    }
    
    private function checkMiddleware($middleware)
    {
        switch ($middleware) {
            case 'auth':
                return isset($_SESSION['user_id']);
            default:
                return true;
        }
    }
    
    private function callHandler($handler, $params = [])
    {
        if (is_string($handler)) {
            list($controller, $method) = explode('@', $handler);
            
            $controllerClass = "SmartCast\\Controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                
                if (method_exists($controllerInstance, $method)) {
                    // Convert associative array to indexed array for call_user_func_array
                    $paramValues = array_values($params);
                    
                    // Debug output (remove in production)
                    if (defined('APP_DEBUG') && APP_DEBUG) {
                        error_log("Calling $controllerClass::$method with params: " . print_r($paramValues, true));
                    }
                    
                    call_user_func_array([$controllerInstance, $method], $paramValues);
                } else {
                    throw new \Exception("Method {$method} not found in {$controllerClass}");
                }
            } else {
                throw new \Exception("Controller {$controllerClass} not found");
            }
        } elseif (is_callable($handler)) {
            $paramValues = array_values($params);
            call_user_func_array($handler, $paramValues);
        }
    }
    
    private function redirect($path)
    {
        // Check if path is already a full URL
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            $redirectUrl = $path;
        } else {
            $redirectUrl = APP_URL . $path;
        }
        
        header("Location: " . $redirectUrl);
        exit;
    }
    
    private function show404()
    {
        $templatePath = __DIR__ . '/../../views/errors/404.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }
}
