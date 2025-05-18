<?php
/**
 * Script de test SMTP pour Camydia Agency
 * 
 * Ce script permet de tester la configuration SMTP directement sur le serveur.
 * À exécuter via le terminal cPanel pour vérifier les paramètres d'envoi d'emails.
 */

// Chargement des variables d'environnement
$dotenv = parse_ini_file('.env');

// Configuration SMTP (prendre les valeurs du fichier .env)
$smtp_host = $dotenv['MAIL_HOST'] ?? 'mail.camydia-agency.site';
$smtp_port = $dotenv['MAIL_PORT'] ?? 465;
$smtp_user = $dotenv['MAIL_USERNAME'] ?? 'contact@camydia-agency.site';
$smtp_pass = $dotenv['MAIL_PASSWORD'] ?? 'votre_mot_de_passe';
$smtp_from = $dotenv['MAIL_FROM_ADDRESS'] ?? 'contact@camydia-agency.site';
$smtp_name = $dotenv['MAIL_FROM_NAME'] ?? 'Camydia Agency';
$smtp_secure = $dotenv['MAIL_ENCRYPTION'] ?? 'ssl';

// Destinataire de test (à modifier avec votre adresse email)
$to_email = 'votre_email@exemple.com';
$to_name = 'Votre Nom';

// Message de test
$subject = 'Test SMTP Camydia Agency';
$message = "Ceci est un test d'envoi d'email depuis le serveur Camydia Agency.\n\n";
$message .= "Si vous recevez ce message, cela signifie que la configuration SMTP fonctionne correctement.";

// En-têtes de l'email
$headers = "From: $smtp_name <$smtp_from>\r\n";
$headers .= "Reply-To: $smtp_from\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Tentative d'envoi avec les fonctions mail() de PHP
echo "Tentative d'envoi d'email avec mail() de PHP...\n";
$mail_result = mail($to_email, $subject, $message, $headers);

if ($mail_result) {
    echo "✅ Email envoyé avec succès via mail() de PHP.\n";
} else {
    echo "❌ Échec de l'envoi d'email via mail() de PHP.\n";
}

// Fonction pour tester un serveur SMTP directement
function testSMTPConnection($host, $port, $user, $pass, $secure = 'ssl') {
    echo "\nTest de connexion SMTP direct à $host:$port avec sécurité $secure...\n";
    
    // Création du contexte de flux pour SSL/TLS
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);
    
    // Tentative de connexion au serveur SMTP
    $errno = 0;
    $errstr = '';
    
    if ($secure == 'ssl') {
        $socket = @stream_socket_client(
            "ssl://$host:$port",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );
    } else {
        $socket = @fsockopen($host, $port, $errno, $errstr, 30);
    }
    
    if (!$socket) {
        echo "❌ Impossible de se connecter au serveur SMTP: $errstr ($errno)\n";
        return false;
    }
    
    // Lire la réponse du serveur
    $response = fgets($socket, 515);
    echo "Réponse du serveur: $response";
    
    // Tester le processus SMTP
    fputs($socket, "EHLO camydia-agency.site\r\n");
    $response = fgets($socket, 515);
    echo "EHLO réponse: $response";
    
    // Si TLS est demandé et que nous ne sommes pas déjà en SSL
    if ($secure == 'tls' && strpos($response, "STARTTLS") !== false) {
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        echo "STARTTLS réponse: $response";
        
        // Mettre à niveau la connexion vers TLS
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        // Renvoyer EHLO après TLS
        fputs($socket, "EHLO camydia-agency.site\r\n");
        $response = fgets($socket, 515);
        echo "EHLO après TLS: $response";
    }
    
    // Tenter l'authentification
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    echo "AUTH LOGIN réponse: $response";
    
    fputs($socket, base64_encode($user) . "\r\n");
    $response = fgets($socket, 515);
    echo "Username réponse: $response";
    
    fputs($socket, base64_encode($pass) . "\r\n");
    $response = fgets($socket, 515);
    echo "Password réponse: $response";
    
    if (strpos($response, '235') === 0) {
        echo "✅ Authentification réussie!\n";
        
        // Tester MAIL FROM
        fputs($socket, "MAIL FROM: <$user>\r\n");
        $response = fgets($socket, 515);
        echo "MAIL FROM réponse: $response";
        
        // Fermer la connexion
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    } else {
        echo "❌ Échec de l'authentification\n";
        
        // Fermer la connexion
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return false;
    }
}

// Tester la connexion SMTP directement
testSMTPConnection($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure);

echo "\n\n--------------------------------------------------------------\n";
echo "INSTRUCTIONS POUR UTILISER CE SCRIPT:\n";
echo "1. Modifiez d'abord le fichier pour définir votre adresse email de test\n";
echo "2. Depuis le terminal cPanel, exécutez: php SMTP_TEST.php\n";
echo "3. Vérifiez si un email de test est bien reçu dans votre boîte\n";
echo "--------------------------------------------------------------\n";

echo "\nPour utiliser ce script en ligne de commande avec des paramètres spécifiques:\n";
echo "php SMTP_TEST.php email@test.com smtp.host.com 465 utilisateur motdepasse ssl\n";

// Permettre de passer des arguments en ligne de commande pour faciliter les tests
if (isset($argv) && count($argv) >= 2) {
    $cmd_to_email = $argv[1] ?? $to_email;
    $cmd_smtp_host = $argv[2] ?? $smtp_host;
    $cmd_smtp_port = $argv[3] ?? $smtp_port;
    $cmd_smtp_user = $argv[4] ?? $smtp_user;
    $cmd_smtp_pass = $argv[5] ?? $smtp_pass;
    $cmd_smtp_secure = $argv[6] ?? $smtp_secure;
    
    echo "\nTest avec paramètres en ligne de commande...\n";
    testSMTPConnection($cmd_smtp_host, $cmd_smtp_port, $cmd_smtp_user, $cmd_smtp_pass, $cmd_smtp_secure);
    
    // Tentative d'envoi
    $mail_result = mail($cmd_to_email, $subject, $message, $headers);
    echo $mail_result ? "✅ Email envoyé à $cmd_to_email\n" : "❌ Échec de l'envoi à $cmd_to_email\n";
}