<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\Container;
use App\Controllers\TransactionController;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/doctrine.php';

$container = new Container();

// Set up Doctrine EntityManager
$container->set(EntityManager::class, function () {
    return createEntityManager();
});

// Set up TransactionController
$container->set(TransactionController::class, function ($container) {
    return new TransactionController($container->get(EntityManager::class));
});

AppFactory::setContainer($container);
$app = AppFactory::create();

// Add CORS middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Handle OPTIONS requests
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Health check endpoint
$app->get('/health', function (Request $request, Response $response) {
    $data = ['status' => 'ok'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Transaction routes
$app->get('/api/v1/transactions[/]', [TransactionController::class, 'index']);
$app->get('/api/v1/transactions/grid', [TransactionController::class, 'grid']);
$app->get('/api/v1/transactions/{id}', [TransactionController::class, 'show']);
$app->post('/api/v1/transactions[/]', [TransactionController::class, 'create']);
$app->put('/api/v1/transactions/{id}', [TransactionController::class, 'update']);
$app->delete('/api/v1/transactions/{id}', [TransactionController::class, 'delete']);

$app->run();
