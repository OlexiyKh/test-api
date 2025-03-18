<?php

use App\Application\Controllers\ProductController;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ValidationException;
use DI\ContainerBuilder;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use function FastRoute\simpleDispatcher;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/app.php');
$container = $containerBuilder->build();

$request = ServerRequestFactory::fromGlobals();

$dispatcher = simpleDispatcher(function(RouteCollector $r) {
    $r->addRoute('GET', '/products', [ProductController::class, 'listProducts']);
    $r->addRoute('GET', '/products/{id}', [ProductController::class, 'getProduct']);
    $r->addRoute('POST', '/products', [ProductController::class, 'createProduct']);
    $r->addRoute('PATCH', '/products/{id}', [ProductController::class, 'updateProduct']);
    $r->addRoute('DELETE', '/products/{id}', [ProductController::class, 'deleteProduct']);
});

$routeInfo = $dispatcher->dispatch(
    $request->getMethod(),
    $request->getUri()->getPath()
);

try {
    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $response = new JsonResponse(['error' => 'Not found'], 404);
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            $response = new JsonResponse(
                ['error' => 'Method not allowed', 'allowed' => $allowedMethods],
                405,
                ['Allow' => implode(', ', $allowedMethods)]
            );
            break;
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];

            $controller = $container->get($handler[0]);
            $method = $handler[1];
            $response = $controller->$method($request, $vars);
            break;
    }
} catch (Exception $e) {
    $statusCode = match (true) {
        $e instanceof EntityNotFoundException => 404,
        $e instanceof ValidationException => 400,
        default => 500,
    };

    $response = new JsonResponse(['error' => $e->getMessage()], $statusCode);
}

(function() use ($response) {
    $statusLine = sprintf(
        'HTTP/%s %s %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    );
    header($statusLine, true, $response->getStatusCode());

    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("$name: $value", false);
        }
    }

    echo $response->getBody();
})();
