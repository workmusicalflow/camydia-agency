<?php
/**
 * Script de test pour l'API Orange SMS
 * 
 * Ce script permet de tester l'API Orange SMS en envoyant des messages aux numéros spécifiés.
 * Il enregistre tous les résultats dans un fichier de log pour faciliter le diagnostic.
 */

// Chemin vers le fichier de log
$logFile = __DIR__ . '/sms-api-test-results.log';

// Fonction pour logger les informations
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    // Afficher dans la console
    echo $logEntry;
    
    // Écrire dans le fichier de log
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Charger les identifiants de l'API depuis le fichier de configuration
$configFile = dirname(__DIR__) . '/src/App/Config/app.php';
logMessage("Chargement de la configuration depuis: $configFile");

if (!file_exists($configFile)) {
    logMessage("ERREUR: Fichier de configuration non trouvé: $configFile");
    exit(1);
}

// Inclure le fichier de configuration pour obtenir les identifiants
require_once $configFile;

// Vérifier si les variables sont définies
if (!isset($client_id) || !isset($client_secret)) {
    logMessage("ERREUR: Identifiants API non trouvés dans le fichier de configuration");
    exit(1);
}

logMessage("Configuration chargée avec succès");
logMessage("Client ID: " . substr($client_id, 0, 4) . '...' . substr($client_id, -4));
logMessage("Client Secret: " . substr($client_secret, 0, 4) . '...' . substr($client_secret, -4));

// Fonction pour obtenir le jeton d'accès
function getAccessToken($clientId, $clientSecret) {
    logMessage("Demande d'un jeton d'accès...");
    
    $url = 'https://api.orange.com/oauth/v3/token';
    $data = 'grant_type=client_credentials';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    // Capturer les informations de débogage cURL
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Récupérer les informations de débogage
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    if (curl_errno($ch)) {
        logMessage("ERREUR cURL: " . curl_error($ch));
        logMessage("Détails de débogage cURL: " . $verboseLog);
        return null;
    }
    
    curl_close($ch);
    
    logMessage("Réponse HTTP: $httpCode");
    logMessage("Réponse brute: " . $response);
    
    $responseData = json_decode($response, true);
    if (isset($responseData['access_token'])) {
        logMessage("Jeton d'accès obtenu avec succès: " . substr($responseData['access_token'], 0, 10) . '...');
        return $responseData['access_token'];
    } else {
        logMessage("ERREUR: Impossible d'obtenir le jeton d'accès. Réponse: " . print_r($responseData, true));
        return null;
    }
}

// Fonction pour préparer un numéro de téléphone pour l'API Orange
function preparePhoneNumber($phoneNumber) {
    // Nettoyage du numéro (suppression des espaces et caractères non numériques sauf +)
    $cleanNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
    
    // Vérifier si le numéro commence déjà par 'tel:'
    if (strpos($cleanNumber, 'tel:') === 0) {
        return $cleanNumber;
    }
    
    // Ajouter le préfixe 'tel:' pour l'API Orange
    return 'tel:' . $cleanNumber;
}

// Fonction pour envoyer un SMS
function sendSMS($accessToken, $senderAddress, $receiverAddress, $senderName = null, $message) {
    $preparedReceiver = preparePhoneNumber($receiverAddress);
    
    logMessage("Envoi de SMS à $preparedReceiver...");
    logMessage("Message: \"$message\"");
    
    $url = 'https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($senderAddress) . '/requests';
    $smsData = [
        'outboundSMSMessageRequest' => [
            'address' => $preparedReceiver,
            'outboundSMSTextMessage' => [
                'message' => $message,
            ],
            'senderAddress' => $senderAddress,
        ]
    ];
    
    // Ajouter le nom d'expéditeur uniquement s'il est spécifié
    if ($senderName !== null) {
        $smsData['outboundSMSMessageRequest']['senderName'] = $senderName;
    }
    
    logMessage("URL de l'API: $url");
    logMessage("Données JSON: " . json_encode($smsData, JSON_PRETTY_PRINT));
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    // Capturer les informations de débogage cURL
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Récupérer les informations de débogage
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    if (curl_errno($ch)) {
        logMessage("ERREUR cURL: " . curl_error($ch));
        logMessage("Détails de débogage cURL: " . $verboseLog);
        curl_close($ch);
        return ['success' => false, 'error' => curl_error($ch)];
    }
    
    curl_close($ch);
    
    logMessage("Réponse HTTP: $httpCode");
    logMessage("Réponse brute: " . $response);
    
    $responseData = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        logMessage("SMS envoyé avec succès à $receiverAddress");
        if (isset($responseData['outboundSMSMessageRequest']['resourceURL'])) {
            logMessage("resourceURL: " . $responseData['outboundSMSMessageRequest']['resourceURL']);
        }
        return ['success' => true, 'data' => $responseData];
    } else {
        logMessage("ERREUR: Échec de l'envoi du SMS à $receiverAddress. Code HTTP: $httpCode");
        return ['success' => false, 'error' => "HTTP $httpCode", 'data' => $responseData];
    }
}

// Début des tests
logMessage("=== DÉBUT DU TEST DE L'API ORANGE SMS ===");

// Obtenir le jeton d'accès
$accessToken = getAccessToken($client_id, $client_secret);
if (!$accessToken) {
    logMessage("ERREUR FATALE: Impossible de continuer sans jeton d'accès");
    exit(1);
}

// Configuration des paramètres pour l'envoi de SMS
$senderAddress = 'tel:+2250595016840';  // Adresse d'expéditeur
$senderName = 'Topdigital';  // Nouveau nom d'expéditeur à tester
$message = "Ceci est un test de l'API Orange SMS depuis Camydia Agency.";

// Liste des numéros à tester
$testNumbers = [
    '+2250141399354',
    '+2250768174439'
];

$results = [];

// Tester chaque numéro
foreach ($testNumbers as $number) {
    logMessage("=== TEST POUR LE NUMÉRO: $number ===");
    
    $result = sendSMS($accessToken, $senderAddress, $number, $senderName, $message);
    $results[$number] = $result;
    
    // Attendre un court instant entre les envois pour éviter toute limitation de l'API
    sleep(2);
}

// Résumé des résultats
logMessage("=== RÉSUMÉ DES TESTS ===");
foreach ($results as $number => $result) {
    $status = $result['success'] ? "SUCCÈS" : "ÉCHEC";
    logMessage("$number: $status");
}

logMessage("=== FIN DU TEST DE L'API ORANGE SMS ===");
logMessage("Le fichier de log complet est disponible à: $logFile");

// Retourner un code de sortie basé sur le succès global
$allSuccess = true;
foreach ($results as $result) {
    if (!$result['success']) {
        $allSuccess = false;
        break;
    }
}

exit($allSuccess ? 0 : 1);