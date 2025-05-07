<?php

namespace App\App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\App\Database\Database;

class EmailService
{
    private $mailer;
    private $db;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->db = Database::getInstance();
        
        // Configuration du serveur SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = SMTP_AUTH;
        $this->mailer->Username = SMTP_USERNAME;
        $this->mailer->Password = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = SMTP_SECURE;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->CharSet = 'UTF-8';
    }
    
    /**
     * Envoie un email avec le modèle par défaut
     */
    public function sendEmail($to, $subject, $message, $contactId = null, $replyTo = null)
    {
        try {
            // Configuration de l'expéditeur
            $this->mailer->setFrom(SMTP_USERNAME, 'Camydia Agency');
            
            // Ajout du destinataire
            $this->mailer->addAddress($to);
            
            // Configuration de Reply-To si fourni
            if ($replyTo) {
                $this->mailer->addReplyTo($replyTo);
            }
            
            // Configuration du contenu
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->getEmailTemplate($message);
            $this->mailer->AltBody = strip_tags($message);
            
            // Envoi de l'email
            $this->mailer->send();
            
            // Enregistrement dans les logs
            $this->logEmail($to, $subject, $message, 'success', null, $contactId);
            
            return true;
        } catch (Exception $e) {
            // Enregistrement de l'erreur dans les logs
            $this->logEmail($to, $subject, $message, 'error', $this->mailer->ErrorInfo, $contactId);
            
            return false;
        }
    }
    
    /**
     * Génère un modèle d'email HTML avec le logo Camydia
     */
    private function getEmailTemplate($content)
    {
        $logoUrl = 'https://events-qualitas-ci.com/public/images/camydia/logo-camydia.jpg';
        
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Camydia Agency</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f9f9f9;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                    border-bottom: 1px solid #eee;
                }
                .logo {
                    max-width: 200px;
                    height: auto;
                }
                .content {
                    padding: 20px 0;
                }
                .footer {
                    text-align: center;
                    padding: 20px 0;
                    font-size: 12px;
                    color: #888;
                    border-top: 1px solid #eee;
                }
                .primary-color {
                    color: #ed1e79;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #ed1e79;
                    color: #ffffff !important;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 15px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="' . $logoUrl . '" alt="Camydia Agency Logo" class="logo">
                </div>
                <div class="content">
                    ' . $content . '
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' Camydia Agency. Tous droits réservés.</p>
                    <p>Cocody, Abidjan • +225 07 58 23 27 92 • camydia94@gmail.com</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        return $template;
    }
    
    /**
     * Enregistre les emails envoyés dans la base de données
     */
    private function logEmail($recipient, $subject, $message, $status, $errorMessage = null, $contactId = null)
    {
        $data = [
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'status' => $status,
            'error_message' => $errorMessage,
        ];
        
        if ($contactId) {
            $data['contact_id'] = $contactId;
        }
        
        $this->db->insert('email_logs', $data);
    }
}