<?php

// Définition du chemin racine de l'application
// Adapté pour la structure LWS où private est dans public_html
define('APP_ROOT', __DIR__ . '/private');

// Autoloader Composer
require APP_ROOT . '/vendor/autoload.php';

// Chargement de la configuration de l'application
require_once APP_ROOT . '/src/App/Config/app.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\App\Controllers\HomeController;
use App\App\Controllers\ContactController;

// Création du container
$container = new Container();

// Configuration de Twig pour la production
$container->set('view', function () {
    $twig = Twig::create(APP_ROOT . '/views', [
        'cache' => APP_ROOT . '/cache/twig',  // Activer le cache en production
        'debug' => false                      // Désactiver le mode debug en production
    ]);
    $twig->addExtension(new \App\App\Twig\RouteExtension());
    return $twig;
});

// Configuration des contrôleurs
$container->set(HomeController::class, function ($container) {
    return new HomeController($container->get('view'));
});

$container->set(ContactController::class, function ($container) {
    return new ContactController($container->get('view'));
});

// Création de l'application avec le container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Ajout du middleware Twig
$app->add(TwigMiddleware::createFromContainer($app));

// Configuration des erreurs pour la production
// Désactiver les erreurs détaillées (false), mais activer la journalisation
$displayErrorDetails = false;  // Ne pas afficher les détails d'erreur aux utilisateurs
$logErrors = true;             // Journaliser les erreurs
$logErrorDetails = true;       // Journaliser les détails des erreurs
$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

// Routes
require APP_ROOT . '/src/routes.php';

// Exécution de l'application
$app->run();
