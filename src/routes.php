<?php

use App\App\Controllers\HomeController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

// Routes principales
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/services', [HomeController::class, 'services']);
$router->get('/team', [HomeController::class, 'team']);
$router->get('/contact', [HomeController::class, 'contact']);

// Gestion des erreurs 404
$errorMiddleware = $router->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails) {
        global $container;
        $view = $container->get('view');
        $response = new Response();
        return $view->render($response, 'errors/404.twig')->withStatus(404);
    }
);