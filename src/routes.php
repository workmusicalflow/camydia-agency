<?php

// Chargement des configurations de l'application
require_once __DIR__ . '/App/Config/app.php';

use App\App\Controllers\HomeController;
use App\App\Controllers\ContactController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

// Routes principales
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/services', [HomeController::class, 'services']);
$router->get('/contact', [ContactController::class, 'showContactForm']);
$router->post('/contact', [ContactController::class, 'processContactForm']);

// Routes d'administration (à protéger dans une application réelle)
$router->get('/admin/contacts', [ContactController::class, 'listContacts']);
$router->get('/admin/contacts/{id}', [ContactController::class, 'viewContact']);
$router->post('/admin/contacts/{id}/status', [ContactController::class, 'updateContactStatus']);

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