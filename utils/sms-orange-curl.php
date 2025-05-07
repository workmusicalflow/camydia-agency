<?php

// Configuration des paramètres d'authentification
$client_id = 'DGxbQKd9JHXLdFaWGtv0FfqFFI7Gu03a';
$client_secret = 'S4ywfdZUjNvOXErMr5NyQwgliBCdXIAYp1DcibKThBXs';

// Fonction pour obtenir le jeton d'accès
function getAccessToken($client_id, $client_secret)
{
    $url = 'https://api.orange.com/oauth/v3/token';
    $data = 'grant_type=client_credentials';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        'Content-Type: application/x-www-form-urlencoded'
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die('Erreur cURL : ' . curl_error($ch));
    }
    curl_close($ch);

    $response_data = json_decode($response, true);
    if (isset($response_data['access_token'])) {
        return $response_data['access_token'];
    } else {
        die('Erreur: impossible d\'obtenir le jeton d\'accès');
    }
}

// Fonction pour envoyer un SMS
function sendSMS($access_token, $sender_address, $receiver_address, $sender_name, $message)
{
    $url = 'https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($sender_address) . '/requests';
    $sms_data = array(
        'outboundSMSMessageRequest' => array(
            'address' => $receiver_address,
            'outboundSMSTextMessage' => array(
                'message' => $message,
            ),
            'senderAddress' => $sender_address,
            'senderName' => $sender_name,
        )
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms_data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die('Erreur cURL : ' . curl_error($ch));
    }
    curl_close($ch);

    return json_decode($response, true);
}


// Obtenir le jeton d'accès
$access_token = getAccessToken($client_id, $client_secret);

// Configuration des paramètres pour l'envoi de SMS
$sender_address = 'tel:+2250595016840';  // Remplacez par votre numéro d'expéditeur
$receiver_address = 'tel:+2250758232792'; // Remplacez par le numéro de destinataire
$sender_name = 'Topdigital';
$message = "Bénéficiez gratuitement du pack de démarrage e-entrepreneur.";

// Envoyer le SMS et récupérer la `resourceURL`
$sms_response = sendSMS($access_token, $sender_address, $receiver_address, $sender_name, $message);

// Affichage de la réponse du serveur pour l'envoi de SMS
echo 'Réponse de l\'API SMS: ';
echo '<pre>';
print_r($sms_response);
echo '</pre>';

// Vérifier si `resourceURL` est présent dans la réponse
if (isset($sms_response['outboundSMSMessageRequest']['resourceURL'])) {
    $resource_url = $sms_response['outboundSMSMessageRequest']['resourceURL'];
} else {
    echo 'Erreur: resourceURL non trouvé dans la réponse de l\'API SMS';
}

/* 
Réponse de l'API SMS:
Array
(
    [outboundSMSMessageRequest] => Array
        (
            [address] => Array
                (
                    [0] => tel:+2250777104936
                )

            [senderAddress] => tel:+2250595016840
            [senderName] => 225HBC
            [outboundSMSTextMessage] => Array
                (
                    [message] => Bénéficiez gratuitement du pack de démarrage e-entrepreneur.
                )

            [resourceURL] => https://api.orange.com/smsmessaging/v1/outbound/tel:+2250595016840/requests/dcaa2c02-296e-4960-9745-ae4c66e900bc
        )
)
*/