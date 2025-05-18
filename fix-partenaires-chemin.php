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
        // Version corrigée avec chemin absolu
        // On utilise $_SERVER['DOCUMENT_ROOT'] qui pointe vers la racine web
        $partenairesDir = $_SERVER['DOCUMENT_ROOT'] . '/public/images/content/partenaires';
        $partenaires = [];
        
        if (is_dir($partenairesDir)) {
            $files = scandir($partenairesDir);
            
            foreach ($files as $file) {
                // Ignorer les dossiers . et .. et s'assurer que c'est une image
                if ($file !== '.' && $file !== '..' && !is_dir($partenairesDir . '/' . $file)) {
                    // Vérifier l'extension du fichier pour s'assurer que c'est une image
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        // Extraire le nom sans extension pour l'utiliser comme alt text
                        $name = pathinfo($file, PATHINFO_FILENAME);
                        $partenaires[] = [
                            'file' => $file,
                            'path' => '/public/images/content/partenaires/' . $file,
                            'name' => $name
                        ];
                    }
                }
            }
        }
        
        return $this->view->render($response, 'index.twig', [
            'title' => 'Camydia Agency - Accueil',
            'partenaires' => $partenaires
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

    public function contact(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'contact.twig', [
            'title' => 'Camydia Agency - Contact'
        ]);
    }
}