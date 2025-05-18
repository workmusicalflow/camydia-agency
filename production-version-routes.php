<?php

// Les routes utilisent la variable $app définie dans index.php
// Ne pas charger une seconde fois les configurations
// require_once __DIR__ . '/App/Config/app.php';

use App\App\Controllers\HomeController;
use App\App\Controllers\ContactController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

// Routes principales
$app->get('/', [HomeController::class, 'index']);
$app->get('/about', [HomeController::class, 'about']);
$app->get('/services', [HomeController::class, 'services']);
$app->get('/contact', [ContactController::class, 'showContactForm']);
$app->post('/contact', [ContactController::class, 'processContactForm']);

// Routes d'administration (à protéger dans une application réelle)
$app->get('/admin/contacts', [ContactController::class, 'listContacts']);
$app->get('/admin/contacts/{id}', [ContactController::class, 'viewContact']);
$app->post('/admin/contacts/{id}/status', [ContactController::class, 'updateContactStatus']);

// La gestion des erreurs 404 a été configurée dans index.php
// Pour simplifier et éviter des erreurs, nous n'ajoutons pas de gestionnaire spécifique ici