<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\App\Controllers\HomeController;

// Configuration de base
define('APP_ROOT', dirname(__DIR__));

// Création du container
$container = new Container();

// Configuration de Twig
$container->set('view', function () {
    $twig = Twig::create(APP_ROOT . '/views', [
        'cache' => false,
        'debug' => true
    ]);
    $twig->addExtension(new \Twig\Extension\DebugExtension());
    return $twig;
});

// Configuration du HomeController
$container->set(HomeController::class, function ($container) {
    return new HomeController($container->get('view'));
});

// Création de l'application avec le container
AppFactory::setContainer($container);
$router = AppFactory::create();

// Ajout du middleware Twig
$router->add(TwigMiddleware::createFromContainer($router));

// Activation des erreurs détaillées
$router->addErrorMiddleware(true, true, true);

// Routes
require APP_ROOT . '/src/routes.php';

// Exécution de l'application
$router->run();