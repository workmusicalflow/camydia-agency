<?php

namespace App\App\Services;

use App\App\Database\Database;
use App\App\Services\EmailService;
use App\App\Services\SmsService;
use App\App\Utilities\PhoneNumberUtility;

class ContactService
{
    private $db;
    private $emailService;
    private $smsService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->emailService = new EmailService();
        $this->smsService = new SmsService();
    }

    /**
     * Valide les données du formulaire de contact
     */
    public function validateContactData($data)
    {
        $errors = [];

        // Vérification du nom
        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors['name'] = 'Le nom doit contenir au moins 3 caractères.';
        }

        // Vérification de l'email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Veuillez entrer une adresse email valide.';
        }

        // Vérification du téléphone (optionnel)
        if (!empty($data['phone'])) {
            // Enlever les espaces pour faciliter la validation
            $cleanPhone = preg_replace('/\s+/', '', $data['phone']);

            // Différents formats de numéros ivoiriens acceptés
            $ivorianPatterns = [
                'international' => '/^\+225[0-9]{9}$/', // +225XXXXXXXXX
                'internationalWith00' => '/^00225[0-9]{9}$/', // 00225XXXXXXXXX
                'local' => '/^0[0-9]{9}$/', // 0XXXXXXXXX (numéro local à 10 chiffres commençant par 0)
                'localShort' => '/^[0-9]{9}$/' // XXXXXXXXX (numéro à 9 chiffres sans le 0 initial)
            ];

            // Formats internationaux génériques
            $internationalGeneric = '/^\+[1-9][0-9]{1,14}$/'; // Format E.164 (numéro international commençant par +)
            $internationalGenericWith00 = '/^00[1-9][0-9]{1,14}$/'; // Variante avec 00 au lieu de +

            // Vérifier si c'est un numéro ivoirien
            $isIvorianNumber =
                preg_match($ivorianPatterns['international'], $cleanPhone) ||
                preg_match($ivorianPatterns['internationalWith00'], $cleanPhone) ||
                preg_match($ivorianPatterns['local'], $cleanPhone) ||
                preg_match($ivorianPatterns['localShort'], $cleanPhone);

            // Vérifier si c'est un numéro international générique
            $isInternationalNumber =
                preg_match($internationalGeneric, $cleanPhone) ||
                preg_match($internationalGenericWith00, $cleanPhone);

            // Si ce n'est ni un numéro ivoirien ni un numéro international valide
            if (!$isIvorianNumber && !$isInternationalNumber) {
                $errors['phone'] = 'Veuillez entrer un numéro de téléphone valide (formats acceptés: +225 XX XX XX XX XX, 07 XX XX XX XX ou format international).';
            }
        }

        // Vérification du sujet
        if (empty($data['subject'])) {
            $errors['subject'] = 'Veuillez sélectionner un sujet.';
        }

        // Vérification du message
        if (empty($data['message']) || strlen($data['message']) < 10) {
            $errors['message'] = 'Le message doit contenir au moins 10 caractères.';
        }

        // Vérification de l'acceptation de la politique de confidentialité
        if (empty($data['privacy']) || $data['privacy'] !== 'on') {
            $errors['privacy'] = 'Vous devez accepter la politique de confidentialité.';
        }

        return $errors;
    }

    /**
     * Enregistre un nouveau contact et envoie les notifications
     */
    public function saveContact($data)
    {
        // Préparation des données pour l'insertion
        $contactData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        // Insertion dans la base de données
        $contactId = $this->db->insert('contacts', $contactData);

        // Envoi des notifications si l'insertion a réussi
        if ($contactId) {
            $this->sendNotifications($contactData, $contactId);
            return ['success' => true, 'contactId' => $contactId];
        }

        return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement du contact.'];
    }

    /**
     * Envoie les notifications par email et SMS
     */
    private function sendNotifications($contactData, $contactId)
    {
        // Envoi d'un email à l'administrateur
        $adminEmailContent = $this->getAdminEmailContent($contactData);
        $this->emailService->sendEmail(
            ADMIN_EMAIL,
            'Nouveau message de contact depuis le site web',
            $adminEmailContent,
            $contactId,
            $contactData['email']
        );

        // Envoi d'un email au gestionnaire du site web
        $this->emailService->sendEmail(
            WEBMASTER_EMAIL,
            'Nouveau message de contact depuis le site web',
            $adminEmailContent,
            $contactId,
            $contactData['email']
        );

        // Envoi d'un email de confirmation au client
        $clientEmailContent = $this->getClientEmailContent($contactData);
        $this->emailService->sendEmail(
            $contactData['email'],
            'Merci pour votre message - Camydia Agency',
            $clientEmailContent,
            $contactId
        );

        // Préparation du contenu SMS
        $smsContent = $this->getSmsContent($contactData);

        // Envoi d'un SMS de notification à l'administrateur
        $this->smsService->sendSms(
            ADMIN_PHONE,
            $smsContent,
            $contactId
        );

        // Envoi d'un SMS de notification au gestionnaire du site web
        $this->smsService->sendSms(
            WEBMASTER_PHONE,
            $smsContent,
            $contactId
        );

        // Mettre à jour le statut du contact
        $this->db->update('contacts', ['status' => 'notified'], "id = {$contactId}");
    }

    /**
     * Génère le contenu de l'email pour l'administrateur
     */
    private function getAdminEmailContent($contactData)
    {
        // Préparation de la ligne téléphone
        $phoneHtml = '';
        if (!empty($contactData['phone'])) {
            $phoneHtml = '<p><strong>Téléphone:</strong> ' . htmlspecialchars($contactData['phone']) . '</p>';
        }

        return '
        <h2>Nouveau message de contact</h2>
        <p>Vous avez reçu un nouveau message de contact depuis votre site web.</p>
        
        <div style="background-color: #f9f9f9; border-left: 4px solid #ed1e79; padding: 15px; margin: 20px 0;">
            <p><strong>Nom:</strong> ' . htmlspecialchars($contactData['name']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($contactData['email']) . '</p>
            ' . $phoneHtml . '
            <p><strong>Sujet:</strong> ' . htmlspecialchars($contactData['subject']) . '</p>
            <p><strong>Message:</strong></p>
            <div style="margin-left: 20px;">' . nl2br(htmlspecialchars($contactData['message'])) . '</div>
            <p><strong>Date:</strong> ' . date('d/m/Y H:i:s') . '</p>
            <p><strong>IP:</strong> ' . ($contactData['ip_address'] ?? 'Non disponible') . '</p>
        </div>
        
        <p>Vous pouvez répondre directement à cet email pour contacter l\'expéditeur.</p>
        <p><em>Note: Ce message a été envoyé simultanément à ' . ADMIN_EMAIL . ' et ' . WEBMASTER_EMAIL . ' pour assurer un suivi optimal.</em></p>
        ';
    }

    /**
     * Génère le contenu de l'email pour le client
     */
    private function getClientEmailContent($contactData)
    {
        // Préparation de la ligne téléphone
        $phoneHtml = '';
        if (!empty($contactData['phone'])) {
            $phoneHtml = '<p><strong>Téléphone:</strong> ' . htmlspecialchars($contactData['phone']) . '</p>';
        }

        return '
        <h2>Merci pour votre message</h2>
        <p>Cher(e) ' . htmlspecialchars($contactData['name']) . ',</p>
        
        <p>Nous vous remercions d\'avoir contacté Camydia Agency. Votre message a bien été reçu et nous vous répondrons dans les plus brefs délais.</p>
        
        <p>Voici un récapitulatif de votre message :</p>
        
        <div style="background-color: #f9f9f9; border-left: 4px solid #ed1e79; padding: 15px; margin: 20px 0;">
            <p><strong>Sujet:</strong> ' . htmlspecialchars($contactData['subject']) . '</p>
            ' . $phoneHtml . '
            <p><strong>Message:</strong></p>
            <div style="margin-left: 20px;">' . nl2br(htmlspecialchars($contactData['message'])) . '</div>
            <p><strong>Date:</strong> ' . date('d/m/Y H:i:s') . '</p>
        </div>
        
        <p>Si vous avez besoin d\'une réponse urgente, n\'hésitez pas à nous contacter directement par téléphone au +225 07 58 23 27 92.</p>
        
        <p>Cordialement,<br>
        L\'équipe Camydia Agency</p>
        
        <a href="https://www.camydia-agency.site" class="button">Visiter notre site</a>
        ';
    }

    /**
     * Génère le contenu du SMS pour l'administrateur
     */
    private function getSmsContent($contactData)
    {
        // On limite le contenu à 160 caractères
        $name = $contactData['name'];
        $subject = $contactData['subject'];
        $phone = !empty($contactData['phone']) ? $contactData['phone'] : 'Non fourni';

        return "Nouveau contact sur le site web de Camydia Agency: {$name}, Tél: {$phone}, Sujet: {$subject}. Consultez votre email pour plus de détails.";
    }

    /**
     * Récupère tous les contacts
     */
    public function getAllContacts()
    {
        return $this->db->findAll('contacts', '1', [], 'created_at DESC');
    }

    /**
     * Récupère un contact par son ID
     */
    public function getContactById($id)
    {
        return $this->db->find('contacts', 'id = :id', ['id' => $id]);
    }

    /**
     * Met à jour le statut d'un contact
     */
    public function updateContactStatus($id, $status)
    {
        return $this->db->update('contacts', ['status' => $status], "id = {$id}");
    }
}
