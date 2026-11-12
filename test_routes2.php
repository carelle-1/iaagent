<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$router = app('router');
$routes = $router->getRoutes();
foreach($routes as $route) {
    if(str_starts_with($route->uri(), 'admin/')) {
        $middleware = $route->gatherMiddleware();
        echo $route->methods()[0] . ' ' . $route->uri() . ' -> ' . $route->getName() . ' | Middleware: ' . implode(', ', $middleware) . PHP_EOL;
    }
}