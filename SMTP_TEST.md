# Test de la Configuration SMTP

Pour tester la configuration SMTP de votre site après le déploiement, vous pouvez utiliser ce script PHP simple. Placez-le dans un fichier temporaire sur votre serveur (par exemple `smtp-test.php`), exécutez-le, puis supprimez-le pour des raisons de sécurité.

```php
<?php
// Script de test SMTP pour Camydia Agency

// Récupérer l'environnement de production
$dotenv = file_get_contents('../private/.env');
$lines = explode("\n", $dotenv);
$envVars = [];

foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

// Paramètres SMTP
$smtp_host = $envVars['MAIL_HOST'] ?? 'mail.camydia-agency.site';
$smtp_port = $envVars['MAIL_PORT'] ?? 465;
$smtp_user = $envVars['MAIL_USERNAME'] ?? 'contact@camydia-agency.site';
$smtp_pass = $envVars['MAIL_PASSWORD'] ?? '';
$smtp_encrypt = $envVars['MAIL_ENCRYPTION'] ?? 'ssl';
$from_email = $envVars['MAIL_FROM_ADDRESS'] ?? 'contact@camydia-agency.site';
$from_name = $envVars['MAIL_FROM_NAME'] ?? 'Camydia Agency';

// Fonction pour envoyer un e-mail de test
function send_test_email($to, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_encrypt, $from_email, $from_name) {
    $subject = 'Test de la Configuration SMTP - Camydia Agency';
    $message = "Ceci est un test de la configuration SMTP du site Camydia Agency.\n\n";
    $message .= "Si vous recevez cet e-mail, cela signifie que la configuration SMTP fonctionne correctement.\n\n";
    $message .= "Détails de la configuration:\n";
    $message .= "- Hôte: $smtp_host\n";
    $message .= "- Port: $smtp_port\n";
    $message .= "- Utilisateur: $smtp_user\n";
    $message .= "- Chiffrement: $smtp_encrypt\n";
    $message .= "- De: $from_email ($from_name)\n\n";
    $message .= "Date et heure: " . date('Y-m-d H:i:s') . "\n";
    
    $headers = "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Configuration de l'utilisation SMTP pour mail()
    ini_set('SMTP', $smtp_host);
    ini_set('smtp_port', $smtp_port);
    ini_set('sendmail_from', $from_email);
    
    // Tentative d'envoi
    $result = mail($to, $subject, $message, $headers);
    
    return $result;
}

// Interface HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMTP - Camydia Agency</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #ed1e79;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #ed1e79;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background-color: #e2e3e5;
            border: 1px solid #d6d8db;
            color: #383d41;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Test de la Configuration SMTP - Camydia Agency</h1>
    
    <div class="info">
        <p><strong>Note:</strong> Ce script est conçu pour tester la configuration SMTP. Pour des raisons de sécurité, supprimez ce fichier après utilisation.</p>
    </div>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $to = $_POST['to_email'] ?? '';
        $custom_smtp_user = $_POST['smtp_user'] ?? $smtp_user;
        $custom_smtp_pass = $_POST['smtp_pass'] ?? $smtp_pass;
        
        if (empty($to)) {
            echo '<div class="result error">Veuillez spécifier une adresse e-mail de destination.</div>';
        } else {
            $result = send_test_email($to, $smtp_host, $smtp_port, $custom_smtp_user, $custom_smtp_pass, $smtp_encrypt, $from_email, $from_name);
            
            if ($result) {
                echo '<div class="result success">
                    <h3>E-mail envoyé avec succès!</h3>
                    <p>Un e-mail de test a été envoyé à ' . htmlspecialchars($to) . '.</p>
                    <p>Vérifiez votre boîte de réception (et dossier spam) pour confirmer la réception.</p>
                </div>';
            } else {
                echo '<div class="result error">
                    <h3>Échec de l\'envoi de l\'e-mail</h3>
                    <p>L\'e-mail n\'a pas pu être envoyé. Vérifiez les paramètres SMTP et assurez-vous que le serveur autorise l\'envoi d\'e-mails.</p>
                    <p>Erreur PHP: ' . error_get_last()['message'] . '</p>
                </div>';
            }
        }
    }
    ?>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="to_email">Adresse e-mail de destination:</label>
            <input type="email" id="to_email" name="to_email" required placeholder="destinataire@exemple.com">
        </div>
        
        <h2>Paramètres SMTP actuels:</h2>
        <ul>
            <li><strong>Hôte:</strong> <?php echo htmlspecialchars($smtp_host); ?></li>
            <li><strong>Port:</strong> <?php echo htmlspecialchars($smtp_port); ?></li>
            <li><strong>Chiffrement:</strong> <?php echo htmlspecialchars($smtp_encrypt); ?></li>
            <li><strong>Utilisateur:</strong> <?php echo htmlspecialchars($smtp_user); ?></li>
        </ul>
        
        <div class="form-group">
            <label for="smtp_user">Nom d'utilisateur SMTP (optionnel, si différent):</label>
            <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($smtp_user); ?>">
        </div>
        
        <div class="form-group">
            <label for="smtp_pass">Mot de passe SMTP:</label>
            <input type="password" id="smtp_pass" name="smtp_pass" placeholder="Entrez le mot de passe SMTP">
        </div>
        
        <button type="submit">Envoyer un e-mail de test</button>
    </form>
</body>
</html>
```

## Comment utiliser ce script

1. Téléchargez le fichier sur votre serveur, dans le dossier public (par exemple `public_html/smtp-test.php`)
2. Accédez au script via votre navigateur (par exemple `https://www.camydia-agency.site/smtp-test.php`)
3. Entrez votre adresse e-mail pour recevoir le test
4. Entrez le mot de passe SMTP (il n'est pas stocké dans le fichier pour des raisons de sécurité)
5. Cliquez sur "Envoyer un e-mail de test"
6. Vérifiez votre boîte de réception pour confirmer que l'e-mail a été reçu
7. **Important**: Supprimez le fichier après avoir terminé le test

## Dépannage

Si le test échoue, vérifiez les points suivants :

1. **Crédentials SMTP** : Vérifiez que le nom d'utilisateur et le mot de passe sont corrects
2. **Port et chiffrement** : Assurez-vous que le port (465) et le mode de chiffrement (SSL) sont pris en charge
3. **Restrictions du serveur** : Certains hébergeurs limitent l'envoi d'e-mails via PHP
4. **Fonction mail()** : Vérifiez que la fonction `mail()` de PHP est activée sur le serveur

Si vous rencontrez toujours des problèmes, vous pouvez envisager d'utiliser une bibliothèque comme PHPMailer qui offre plus de flexibilité et de fonctionnalités pour l'envoi d'e-mails.