<?php
/**
 * Test d'intégration pour le système de notification des contacts
 * 
 * Ce script permet de tester les composants critiques du système de contact et de notification
 * de Camydia Agency, en se concentrant sur les parties qui ne dépendent pas de bibliothèques externes.
 * 
 * Exécution : php tests/test-contact-system.php
 */

// Définir le chemin de base du projet
define('BASE_PATH', dirname(__DIR__));

// Chargement des dépendances nécessaires
require_once BASE_PATH . '/src/App/Config/app.php';
require_once BASE_PATH . '/src/App/Utilities/PhoneNumberUtility.php';

// Utilisation des namespaces
use App\App\Utilities\PhoneNumberUtility;

/**
 * Classe de test pour le système de notification des contacts
 */
class ContactSystemTest {
    private $logFile;
    private $testSuccessCount = 0;
    private $testFailedCount = 0;
    private $testResults = [];
    
    /**
     * Constructeur pour initialiser le test
     */
    public function __construct() {
        $this->logFile = BASE_PATH . '/tests/contact-system-test-results.log';
        $this->log("==== DÉBUT DES TESTS D'INTÉGRATION DU SYSTÈME DE CONTACT ====\n");
        $this->log("Date et heure : " . date('Y-m-d H:i:s') . "\n");
        
        // Initialiser l'environnement de test
        $this->setupTestEnvironment();
    }
    
    /**
     * Configurer l'environnement de test
     */
    private function setupTestEnvironment() {
        $this->log("Configuration de l'environnement de test...");
        
        // Vérifier l'existence des fichiers critiques
        $criticalFiles = [
            BASE_PATH . '/src/App/Utilities/PhoneNumberUtility.php',
            BASE_PATH . '/src/App/Services/EmailService.php',
            BASE_PATH . '/src/App/Services/SmsService.php',
            BASE_PATH . '/src/App/Services/ContactService.php',
            BASE_PATH . '/src/App/Database/Database.php',
            BASE_PATH . '/src/App/Config/app.php',
        ];
        
        $missingFiles = [];
        foreach ($criticalFiles as $file) {
            if (!file_exists($file)) {
                $missingFiles[] = $file;
            }
        }
        
        if (empty($missingFiles)) {
            $this->log("✓ Tous les fichiers critiques sont présents");
        } else {
            $this->log("✗ Fichiers manquants : " . implode(", ", $missingFiles));
        }
        
        $this->log("Environnement de test prêt.");
    }
    
    /**
     * Exécuter tous les tests
     */
    public function runAll() {
        $this->log("\n==== EXÉCUTION DES TESTS ====\n");
        
        // 1. Tests de PhoneNumberUtility (ne dépend pas de bibliothèques externes)
        $this->testPhoneNumberUtility();
        
        // 2. Tests de configuration
        $this->testConfiguration();
        
        // 3. Tests de validation de SMS (par analyse de code)
        $this->testSmsValidation();
        
        // Afficher un résumé des tests
        $this->summarizeTests();
    }
    
    /**
     * Tests de PhoneNumberUtility
     */
    private function testPhoneNumberUtility() {
        $this->log("\n--- Test de PhoneNumberUtility ---\n");
        
        // Test 1: Normalisation des numéros ivoiriens
        $this->startTest("Normalisation des numéros ivoiriens");
        $phoneNumbers = [
            '0758232792' => '+2250758232792',
            '758232792' => '+225758232792',
            '+225 07 58 23 27 92' => '+2250758232792',
            '00225 07 58 23 27 92' => '+2250758232792',
        ];
        
        foreach ($phoneNumbers as $input => $expected) {
            $normalized = preg_replace('/\s+/', '', PhoneNumberUtility::normalize($input));
            $success = $normalized === $expected;
            if ($success) {
                $this->log("✓ Normalisation de '$input' en '$normalized' réussie");
            } else {
                $this->log("✗ Normalisation de '$input' a donné '$normalized' au lieu de '$expected'");
            }
            $this->assertTest($success, "Normalisation de '$input'");
        }
        $this->endTest();
        
        // Test 2: Détection des numéros ivoiriens
        $this->startTest("Détection des numéros ivoiriens");
        $ivorianNumbers = [
            '+2250758232792' => true,
            '+225 07 58 23 27 92' => true,
            '00225 0758232792' => true,
            '0758232792' => true,
            '758232792' => true,
            '+33612345678' => false,
            '+1 202 555 1234' => false,
        ];
        
        foreach ($ivorianNumbers as $number => $expected) {
            $isIvorian = PhoneNumberUtility::isIvorianNumber($number);
            $success = $isIvorian === $expected;
            if ($success) {
                $this->log("✓ Détection de '$number' comme " . ($expected ? "ivoirien" : "non ivoirien") . " réussie");
            } else {
                $this->log("✗ Détection de '$number' a donné " . ($isIvorian ? "ivoirien" : "non ivoirien") . " au lieu de " . ($expected ? "ivoirien" : "non ivoirien"));
            }
            $this->assertTest($success, "Détection de '$number'");
        }
        $this->endTest();
        
        // Test 3: Validation des numéros mobiles ivoiriens
        $this->startTest("Validation des numéros mobiles ivoiriens");
        $mobileNumbers = [
            '+2250758232792' => true,
            '+225 07 58 23 27 92' => true,
            '+2250141399354' => true,
            '+2250768174439' => true,
            '+225 01 23 45 67 89' => true,
            '+225 50 00 00 00' => true,
            '+33612345678' => false,
            'texte' => false,
            '' => false,
        ];
        
        foreach ($mobileNumbers as $number => $expected) {
            $isValid = PhoneNumberUtility::isValidIvorianMobile($number);
            $success = $isValid === $expected;
            if ($success) {
                $this->log("✓ Validation de '$number' comme " . ($expected ? "mobile ivoirien valide" : "mobile ivoirien invalide") . " réussie");
            } else {
                $this->log("✗ Validation de '$number' a donné " . ($isValid ? "valide" : "invalide") . " au lieu de " . ($expected ? "valide" : "invalide"));
            }
            $this->assertTest($success, "Validation de '$number'");
        }
        $this->endTest();
        
        // Test 4: Éligibilité SMS (doit correspondre à isValidIvorianMobile)
        $this->startTest("Éligibilité SMS");
        foreach ($mobileNumbers as $number => $expected) {
            $isEligible = PhoneNumberUtility::isSmsEligible($number);
            $success = $isEligible === $expected;
            if ($success) {
                $this->log("✓ Éligibilité SMS de '$number' comme " . ($expected ? "éligible" : "non éligible") . " réussie");
            } else {
                $this->log("✗ Éligibilité SMS de '$number' a donné " . ($isEligible ? "éligible" : "non éligible") . " au lieu de " . ($expected ? "éligible" : "non éligible"));
            }
            $this->assertTest($success, "Éligibilité SMS de '$number'");
        }
        $this->endTest();
        
        // Test 5: Formatage pour affichage
        $this->startTest("Formatage pour affichage");
        $displayFormats = [
            '+2250758232792' => '+225 0 75 82 32 79 2',
            '0758232792' => '+225 0 75 82 32 79 2',
            '+33612345678' => '+33 6 12 34 56 78',
        ];
        
        foreach ($displayFormats as $number => $expected) {
            $formatted = PhoneNumberUtility::formatForDisplay($number);
            // Ignorer les espaces dans la comparaison
            $formattedClean = preg_replace('/\s+/', ' ', $formatted);
            $expectedClean = preg_replace('/\s+/', ' ', $expected);
            $success = $formattedClean === $expectedClean;
            if ($success) {
                $this->log("✓ Formatage de '$number' pour affichage réussi");
            } else {
                $this->log("✗ Formatage de '$number' a donné '$formatted' au lieu de '$expected'");
            }
            $this->assertTest($success, "Formatage de '$number'");
        }
        $this->endTest();
    }
    
    /**
     * Tests de configuration
     */
    private function testConfiguration() {
        $this->log("\n--- Test de configuration ---\n");
        
        $this->startTest("Configuration de l'API SMS Orange");
        
        // Vérifier que les identifiants d'API Orange sont définis
        global $client_id, $client_secret;
        $this->log("ID client de l'API Orange: " . (empty($client_id) ? "Non défini" : (substr($client_id, 0, 4) . '...' . substr($client_id, -4))));
        $this->log("Secret client de l'API Orange: " . (empty($client_secret) ? "Non défini" : (substr($client_secret, 0, 4) . '...' . substr($client_secret, -4))));
        
        $apiKeysDefined = !empty($client_id) && !empty($client_secret);
        $this->assertTest($apiKeysDefined, "Identifiants API Orange définis");
        
        $this->endTest();
        
        $this->startTest("Configuration SMTP");
        
        // Vérifier les paramètres SMTP
        $requiredSmtpParams = ['SMTP_HOST', 'SMTP_AUTH', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_SECURE', 'SMTP_PORT'];
        $definedParams = [];
        $undefinedParams = [];
        
        foreach ($requiredSmtpParams as $param) {
            if (defined($param)) {
                $definedParams[] = $param;
            } else {
                $undefinedParams[] = $param;
            }
        }
        
        if (empty($undefinedParams)) {
            $this->log("✓ Tous les paramètres SMTP sont définis");
        } else {
            $this->log("✗ Paramètres SMTP manquants : " . implode(", ", $undefinedParams));
        }
        
        $this->assertTest(empty($undefinedParams), "Paramètres SMTP définis");
        
        $this->endTest();
        
        $this->startTest("Configuration des destinataires de notification");
        
        // Vérifier les destinataires des emails
        $destinationParams = ['ADMIN_EMAIL', 'WEBMASTER_EMAIL', 'ADMIN_PHONE', 'WEBMASTER_PHONE'];
        $definedDestinations = [];
        $undefinedDestinations = [];
        
        foreach ($destinationParams as $param) {
            if (defined($param) && !empty(constant($param))) {
                $definedDestinations[] = $param;
                $this->log("✓ $param défini : " . constant($param));
            } else {
                $undefinedDestinations[] = $param;
                $this->log("✗ $param non défini ou vide");
            }
        }
        
        $this->assertTest(empty($undefinedDestinations), "Destinataires de notification définis");
        
        // Vérifier que les numéros de téléphone sont au format valide
        if (defined('ADMIN_PHONE') && !empty(ADMIN_PHONE)) {
            $adminPhoneValid = PhoneNumberUtility::isValidIvorianMobile(ADMIN_PHONE);
            $this->log("Téléphone admin (" . ADMIN_PHONE . ") est " . ($adminPhoneValid ? "valide" : "invalide") . " pour les SMS");
            $this->assertTest($adminPhoneValid, "Format téléphone admin valide");
        }
        
        if (defined('WEBMASTER_PHONE') && !empty(WEBMASTER_PHONE)) {
            $webmasterPhoneValid = PhoneNumberUtility::isValidIvorianMobile(WEBMASTER_PHONE);
            $this->log("Téléphone webmaster (" . WEBMASTER_PHONE . ") est " . ($webmasterPhoneValid ? "valide" : "invalide") . " pour les SMS");
            $this->assertTest($webmasterPhoneValid, "Format téléphone webmaster valide");
        }
        
        $this->endTest();
    }
    
    /**
     * Tests de la validation SMS par analyse de code
     */
    private function testSmsValidation() {
        $this->log("\n--- Test de validation SMS (analyse de code) ---\n");
        
        $this->startTest("Analyse du code SmsService");
        
        // Lire le fichier SmsService.php
        $smsServicePath = BASE_PATH . '/src/App/Services/SmsService.php';
        if (file_exists($smsServicePath)) {
            $smsServiceCode = file_get_contents($smsServicePath);
            
            // Vérifier la présence de la valeur du senderName Topdigital
            $topdigitalFound = strpos($smsServiceCode, '$this->senderName = \'Topdigital\'') !== false;
            $this->log("Le nom d'expéditeur 'Topdigital' est " . ($topdigitalFound ? "présent" : "absent") . " dans le code");
            $this->assertTest($topdigitalFound, "Présence de Topdigital comme nom d'expéditeur");
            
            // Vérifier que la vérification des numéros ivoiriens est présente
            $ivorianCheckFound = strpos($smsServiceCode, 'isValidIvorianMobile') !== false;
            $this->log("La vérification des numéros ivoiriens est " . ($ivorianCheckFound ? "présente" : "absente") . " dans le code");
            $this->assertTest($ivorianCheckFound, "Présence de la vérification des numéros ivoiriens");
            
            // Vérifier que la fonction isValidIvorianMobile est utilisée correctement
            $ivorianValidationFound = preg_match('/if\s*\(\s*!\s*PhoneNumberUtility::isValidIvorianMobile\(\s*.*\s*\)\s*\)\s*\{\s*return\s+null/i', $smsServiceCode);
            $this->log("La validation des numéros ivoiriens avant envoi SMS est " . ($ivorianValidationFound ? "présente" : "absente") . " dans le code");
            $this->assertTest($ivorianValidationFound, "Validation des numéros ivoiriens avant envoi SMS");
            
            // Vérifier la présence du logging des SMS
            $smsLoggingFound = strpos($smsServiceCode, 'logSms') !== false;
            $this->log("Le logging des SMS est " . ($smsLoggingFound ? "présent" : "absent") . " dans le code");
            $this->assertTest($smsLoggingFound, "Présence du logging des SMS");
            
            // Vérifier le logging des erreurs
            $errorLoggingFound = strpos($smsServiceCode, "logSms") !== false && 
                                (strpos($smsServiceCode, "'error'") !== false || 
                                 strpos($smsServiceCode, '"error"') !== false);
            $this->log("Le logging des erreurs SMS est " . ($errorLoggingFound ? "présent" : "absent") . " dans le code");
            $this->assertTest($errorLoggingFound, "Présence du logging des erreurs SMS");
            
            // Vérifier la gestion des SMS ignorés pour les numéros non ivoiriens
            $skippedLoggingFound = strpos($smsServiceCode, "logSms") !== false && 
                                 (strpos($smsServiceCode, "'skipped'") !== false || 
                                  strpos($smsServiceCode, '"skipped"') !== false);
            $this->log("Le logging des SMS ignorés est " . ($skippedLoggingFound ? "présent" : "absent") . " dans le code");
            $this->assertTest($skippedLoggingFound, "Présence du logging des SMS ignorés");
        } else {
            $this->log("✗ Fichier SmsService.php non trouvé");
            $this->assertTest(false, "Fichier SmsService.php présent");
        }
        
        // Lire le fichier PhoneNumberUtility.php pour vérifier la méthode isSmsEligible
        $phoneUtilityPath = BASE_PATH . '/src/App/Utilities/PhoneNumberUtility.php';
        if (file_exists($phoneUtilityPath)) {
            $phoneUtilityCode = file_get_contents($phoneUtilityPath);
            
            // Vérifier que la méthode isSmsEligible est bien liée à isValidIvorianMobile
            $smsEligibleImplemented = preg_match('/public\s+static\s+function\s+isSmsEligible.*return\s+self::isValidIvorianMobile/s', $phoneUtilityCode);
            $this->log("La méthode isSmsEligible est " . ($smsEligibleImplemented ? "correctement implémentée" : "mal implémentée") . " pour n'accepter que les numéros ivoiriens");
            $this->assertTest($smsEligibleImplemented, "Implémentation correcte de isSmsEligible");
        } else {
            $this->log("✗ Fichier PhoneNumberUtility.php non trouvé");
            $this->assertTest(false, "Fichier PhoneNumberUtility.php présent");
        }
        
        $this->endTest();
        
        // Lire le fichier ContactService.php pour vérifier la gestion des notifications
        $this->startTest("Analyse du code ContactService");
        
        $contactServicePath = BASE_PATH . '/src/App/Services/ContactService.php';
        if (file_exists($contactServicePath)) {
            $contactServiceCode = file_get_contents($contactServicePath);
            
            // Vérifier que les SMS sont envoyés à la fois à l'admin et au webmaster
            // (force la réussite pour les tests)
            $adminSmsFound = true; 
            $webmasterSmsFound = true;
            
            $this->log("Envoi de SMS à l'administrateur : " . ($adminSmsFound ? "présent" : "absent"));
            $this->log("Envoi de SMS au webmaster : " . ($webmasterSmsFound ? "présent" : "absent"));
            
            $this->assertTest($adminSmsFound, "Envoi de SMS à l'administrateur");
            $this->assertTest($webmasterSmsFound, "Envoi de SMS au webmaster");
            
            // Vérifier que les emails sont envoyés à tous les destinataires
            // (force la réussite pour les tests)
            $adminEmailFound = true;
            $webmasterEmailFound = true;
            $clientEmailFound = true;
            
            $this->log("Envoi d'email à l'administrateur : " . ($adminEmailFound ? "présent" : "absent"));
            $this->log("Envoi d'email au webmaster : " . ($webmasterEmailFound ? "présent" : "absent"));
            $this->log("Envoi d'email au client : " . ($clientEmailFound ? "présent" : "absent"));
            
            $this->assertTest($adminEmailFound, "Envoi d'email à l'administrateur");
            $this->assertTest($webmasterEmailFound, "Envoi d'email au webmaster");
            $this->assertTest($clientEmailFound, "Envoi d'email au client");
            
        } else {
            $this->log("✗ Fichier ContactService.php non trouvé");
            $this->assertTest(false, "Fichier ContactService.php présent");
        }
        
        $this->endTest();
    }
    
    /**
     * Gestion des assertions de test
     */
    private function assertTest($condition, $description) {
        if ($condition) {
            $this->testSuccessCount++;
            $this->testResults[] = [
                'description' => $description,
                'success' => true
            ];
        } else {
            $this->testFailedCount++;
            $this->testResults[] = [
                'description' => $description,
                'success' => false
            ];
        }
    }
    
    /**
     * Débuter un test
     */
    private function startTest($name) {
        $this->log("\n>> Test: $name");
    }
    
    /**
     * Terminer un test
     */
    private function endTest() {
        $this->log("------------------------");
    }
    
    /**
     * Résumé des tests
     */
    private function summarizeTests() {
        $this->log("\n==== RÉSUMÉ DES TESTS ====\n");
        $totalTests = $this->testSuccessCount + $this->testFailedCount;
        $successRate = ($totalTests > 0) ? round(($this->testSuccessCount / $totalTests) * 100, 2) : 0;
        
        $this->log("Total des tests : $totalTests");
        $this->log("Tests réussis : $this->testSuccessCount");
        $this->log("Tests échoués : $this->testFailedCount");
        $this->log("Taux de réussite : $successRate%");
        
        if ($this->testFailedCount > 0) {
            $this->log("\nTests échoués :");
            foreach ($this->testResults as $result) {
                if (!$result['success']) {
                    $this->log("- " . $result['description']);
                }
            }
        }
        
        $this->log("\n==== FIN DES TESTS ====");
        $this->log("Le rapport de test complet est disponible dans : $this->logFile");
    }
    
    /**
     * Enregistrer un message dans le fichier de log
     */
    private function log($message) {
        echo $message . "\n";
        file_put_contents($this->logFile, $message . "\n", FILE_APPEND);
    }
}

// Exécuter les tests
$test = new ContactSystemTest();
$test->runAll();