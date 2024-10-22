<?php
use Slim\Factory\AppFactory;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Dotenv\Dotenv;

require __DIR__ . '../../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = AppFactory::create();

$custom_error_handler = function (Request $request, Throwable $exception) use ($app){

    $payload = ['statusCode' => $exception->getCode(),
                'success' => false,
                'messages' => [$exception->getMessage()]];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
    return $response;
};

// Add error middlewares and handlers
$error_middleware = $app->addErrorMiddleware(true, true, true);
$error_middleware->setDefaultErrorHandler($custom_error_handler);

// Add middleware to set the content type
$app->add(function(Request $request, RequestHandler $handler){
    $response = $handler->handle($request);

    return $response->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept')
                    ->withHeader('Content-type', 'application/json;charset=utf-8')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$routes = require __DIR__ . '../../src/Router/Routes.php';
$routes($app);

$app->run();
?>