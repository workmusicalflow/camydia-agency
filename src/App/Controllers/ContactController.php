<?php

namespace App\App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\App\Services\ContactService;

class ContactController
{
    protected $view;
    protected $contactService;
    
    public function __construct(Twig $view)
    {
        $this->view = $view;
        $this->contactService = new ContactService();
    }
    
    /**
     * Affiche le formulaire de contact
     */
    public function showContactForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'contact.twig', [
            'title' => 'Camydia Agency - Contact'
        ]);
    }
    
    /**
     * Traite la soumission du formulaire de contact
     */
    public function processContactForm(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validation des données
        $errors = $this->contactService->validateContactData($data);
        
        // Si des erreurs sont présentes, on retourne à la page de contact avec les erreurs
        if (!empty($errors)) {
            return $this->view->render($response, 'contact.twig', [
                'title' => 'Camydia Agency - Contact',
                'errors' => $errors,
                'data' => $data
            ]);
        }
        
        // Sauvegarde du contact et envoi des notifications
        $result = $this->contactService->saveContact($data);
        
        // Si tout s'est bien passé, on affiche un message de succès
        if ($result['success']) {
            return $this->view->render($response, 'contact.twig', [
                'title' => 'Camydia Agency - Contact',
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.'
            ]);
        }
        
        // En cas d'erreur, on affiche un message d'erreur
        return $this->view->render($response, 'contact.twig', [
            'title' => 'Camydia Agency - Contact',
            'error' => $result['error'],
            'data' => $data
        ]);
    }
    
    /**
     * Liste tous les contacts (page d'administration)
     */
    public function listContacts(Request $request, Response $response): Response
    {
        $contacts = $this->contactService->getAllContacts();
        
        return $this->view->render($response, 'admin/contacts.twig', [
            'title' => 'Camydia Agency - Liste des contacts',
            'contacts' => $contacts
        ]);
    }
    
    /**
     * Affiche les détails d'un contact (page d'administration)
     */
    public function viewContact(Request $request, Response $response, array $args): Response
    {
        $contactId = $args['id'] ?? null;
        
        if (!$contactId) {
            // Redirection vers la liste des contacts
            return $response->withHeader('Location', '/admin/contacts')->withStatus(302);
        }
        
        $contact = $this->contactService->getContactById($contactId);
        
        if (!$contact) {
            // Redirection vers la liste des contacts avec un message d'erreur
            return $response->withHeader('Location', '/admin/contacts?error=contact-not-found')->withStatus(302);
        }
        
        return $this->view->render($response, 'admin/contact-view.twig', [
            'title' => 'Camydia Agency - Détails du contact',
            'contact' => $contact
        ]);
    }
    
    /**
     * Met à jour le statut d'un contact (page d'administration)
     */
    public function updateContactStatus(Request $request, Response $response, array $args): Response
    {
        $contactId = $args['id'] ?? null;
        $data = $request->getParsedBody();
        $status = $data['status'] ?? null;
        
        if (!$contactId || !$status) {
            return $response->withJson(['success' => false, 'message' => 'Paramètres manquants'], 400);
        }
        
        $result = $this->contactService->updateContactStatus($contactId, $status);
        
        if ($result) {
            return $response->withJson(['success' => true]);
        }
        
        return $response->withJson(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut'], 500);
    }
}