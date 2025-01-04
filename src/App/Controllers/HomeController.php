<?php

namespace App\App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeController
{
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'index.twig', [
            'title' => 'Camydia Agency - Accueil'
        ]);
    }

    public function about(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'about.twig', [
            'title' => 'Camydia Agency - Notre Histoire'
        ]);
    }

    public function services(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'services.twig', [
            'title' => 'Camydia Agency - Services'
        ]);
    }

    public function team(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'team.twig', [
            'title' => 'Camydia Agency - Notre Équipe'
        ]);
    }

    public function contact(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'contact.twig', [
            'title' => 'Camydia Agency - Contact'
        ]);
    }
}