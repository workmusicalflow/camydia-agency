<?php

namespace App\App\Services;

use App\App\Database\Database;
use App\App\Utilities\PhoneNumberUtility;

class SmsService
{
    private $clientId;
    private $clientSecret;
    private $senderAddress;
    private $senderName;
    private $db;

    public function __construct()
    {
        // Configuration à partir des fichiers de configuration
        global $client_id, $client_secret;
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->senderAddress = 'tel:+2250595016840';
        $this->senderName = 'Topdigital';
        $this->db = Database::getInstance();
    }

    /**
     * Prépare un numéro de téléphone pour l'API Orange
     * Normalise et formate le numéro pour l'API SMS Orange (format tel:+225XXXXXXXXX)
     */
    private function preparePhoneForOrangeApi($phoneNumber)
    {
        // Normaliser d'abord le numéro
        $normalized = PhoneNumberUtility::normalize($phoneNumber);

        // Si le numéro n'est pas un mobile ivoirien valide, on retourne null
        if (!PhoneNumberUtility::isValidIvorianMobile($normalized)) {
            return null;
        }

        // Suppression des espaces et autres caractères non numériques (sauf +)
        $cleanNumber = preg_replace('/[^0-9+]/', '', $normalized);

        // Ajout du préfixe 'tel:' pour l'API Orange
        if (strpos($cleanNumber, 'tel:') !== 0) {
            $cleanNumber = 'tel:' . $cleanNumber;
        }

        return $cleanNumber;
    }

    /**
     * Obtient le jeton d'accès pour l'API Orange
     */
    private function getAccessToken()
    {
        $url = 'https://api.orange.com/oauth/v3/token';
        $data = 'grant_type=client_credentials';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Erreur cURL: ' . curl_error($ch));
        }
        curl_close($ch);

        $responseData = json_decode($response, true);
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        } else {
            throw new \Exception('Erreur: impossible d\'obtenir le jeton d\'accès');
        }
    }

    /**
     * Envoie un SMS à un numéro de téléphone
     * N'envoie le SMS que si le numéro est un mobile ivoirien valide
     */
    public function sendSms($receiverAddress, $message, $contactId = null)
    {
        // Préparation du numéro pour l'API Orange
        $preparedNumber = $this->preparePhoneForOrangeApi($receiverAddress);

        // Si le numéro n'est pas éligible pour l'envoi de SMS, on enregistre un log et on retourne false
        if ($preparedNumber === null) {
            // Enregistrement dans les logs
            $this->logSms(
                $receiverAddress,
                $message,
                'skipped',
                null,
                'Numéro non éligible pour l\'envoi de SMS (non ivoirien ou format invalide)',
                $contactId
            );

            return false;
        }

        try {
            // Obtenir le jeton d'accès
            $accessToken = $this->getAccessToken();

            // Configuration de l'URL et des données
            $url = 'https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($this->senderAddress) . '/requests';
            $smsData = [
                'outboundSMSMessageRequest' => [
                    'address' => $preparedNumber,
                    'outboundSMSTextMessage' => [
                        'message' => $message,
                    ],
                    'senderAddress' => $this->senderAddress,
                    'senderName' => $this->senderName,
                ]
            ];

            // Exécution de la requête
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception('Erreur cURL: ' . curl_error($ch));
            }
            curl_close($ch);

            $responseData = json_decode($response, true);

            // Récupération de l'URL de la ressource si présente
            $resourceUrl = null;
            if (isset($responseData['outboundSMSMessageRequest']['resourceURL'])) {
                $resourceUrl = $responseData['outboundSMSMessageRequest']['resourceURL'];
            }

            // Enregistrement dans les logs
            $this->logSms($receiverAddress, $message, 'success', $resourceUrl, null, $contactId);

            return true;
        } catch (\Exception $e) {
            // Enregistrement de l'erreur dans les logs
            $this->logSms($receiverAddress, $message, 'error', null, $e->getMessage(), $contactId);

            return false;
        }
    }

    /**
     * Enregistre les SMS envoyés dans la base de données
     */
    private function logSms($recipient, $message, $status, $resourceUrl = null, $errorMessage = null, $contactId = null)
    {
        $data = [
            'recipient' => $recipient,
            'message' => $message,
            'status' => $status,
            'resource_url' => $resourceUrl,
            'error_message' => $errorMessage
        ];

        if ($contactId) {
            $data['contact_id'] = $contactId;
        }

        $this->db->insert('sms_logs', $data);
    }
}
