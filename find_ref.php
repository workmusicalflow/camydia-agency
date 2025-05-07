#!/usr/bin/env php
<?php

// --- Gestion des arguments avec getopt() ---
$shortOptions = "hc:i:e:l:";
$longOptions = [
    "help",
    "context:",    // Nombre de lignes de contexte avant/après
    "include:",    // Extensions à inclure, séparées par des virgules
    "exclude:",    // Répertoires à exclure, séparés par des virgules
    "limit:"       // Limite de taille des fichiers (en MB)
];

$options = getopt($shortOptions, $longOptions);

// Afficher l'aide si demandé ou si aucun terme de recherche n'est fourni
if (
    isset($options['h']) || isset($options['help']) || $argc < 2 ||
    (count($options) > 0 && !isset($argv[$argc - 1]))
) {
    displayHelp();
    exit(0);
}

// Récupération des options
$contextLines = isset($options['c']) ? (int)$options['c'] : (isset($options['context']) ? (int)$options['context'] : 0);
$fileLimit = isset($options['l']) ? (float)$options['l'] : (isset($options['limit']) ? (float)$options['limit'] : 10); // Par défaut 10MB

// --- Configuration ---
// Répertoires à exclure de la recherche (chemins relatifs depuis la racine)
$excludedDirs = [
    '.git',
    'node_modules',
    'vendor',
    'storage', // Commun dans les projets Laravel/Symfony
    'cache',
    'logs',
    '.idea', // PHPStorm/IntelliJ
    '.vscode', // VSCode
    'build',
    'dist',
];

// Si des exclusions sont spécifiées via les options
if (isset($options['e']) || isset($options['exclude'])) {
    $customExcludes = isset($options['e']) ? $options['e'] : $options['exclude'];
    $additionalExcludes = explode(',', $customExcludes);
    $excludedDirs = array_merge($excludedDirs, array_map('trim', $additionalExcludes));
}

// Extensions de fichiers à inclure dans la recherche
$includedExtensions = [
    'php',
    'js',
    'ts',
    'jsx',
    'tsx',
    'vue',
    'svelte',
    'html',
    'htm',
    'css',
    'scss',
    'sass',
    'less',
    'json',
    'yaml',
    'yml',
    'md',
    'txt',
    'env', // Important pour votre cas d'usage VITE_
    'sh',
    'bash',
    'py',
    'rb', // Ajoutez d'autres extensions si nécessaire
];

// Si des inclusions sont spécifiées via les options
if (isset($options['i']) || isset($options['include'])) {
    $customIncludes = isset($options['i']) ? $options['i'] : $options['include'];
    $includedExtensions = explode(',', $customIncludes);
    $includedExtensions = array_map('trim', $includedExtensions);
}

// Ne pas chercher dans ce script lui-même
$excludedFiles = [
    basename(__FILE__),
];
// --- Fin Configuration ---

// --- Couleurs ANSI pour la sortie ---
define('ANSI_RESET', "\033[0m");
define('ANSI_YELLOW', "\033[1;33m"); // Fichier
define('ANSI_CYAN', "\033[0;36m");   // Ligne numéro
define('ANSI_RED', "\033[0;31m");     // Erreur / Référence trouvée
define('ANSI_GREEN', "\033[0;32m");   // Succès / Info
define('ANSI_GRAY', "\033[0;90m");    // Contexte
define('ANSI_BG_RED', "\033[41m");    // Fond rouge pour mise en évidence
// --- Fin Couleurs ---

// --- Récupération du terme à rechercher ---
// Le dernier argument est le terme à rechercher
$searchTerm = $argv[$argc - 1];
$startDir = getcwd(); // Commence depuis le répertoire où le script est lancé
// --- Fin Récupération ---

echo ANSI_GREEN . "Recherche de '" . ANSI_RED . $searchTerm . ANSI_GREEN . "' dans : " . ANSI_YELLOW . $startDir . ANSI_RESET . "\n";
echo ANSI_GREEN . "Exclusion des répertoires : " . implode(', ', $excludedDirs) . ANSI_RESET . "\n";
if (!empty($includedExtensions)) {
    echo ANSI_GREEN . "Inclusion des extensions : " . implode(', ', $includedExtensions) . ANSI_RESET . "\n";
}
if ($contextLines > 0) {
    echo ANSI_GREEN . "Affichage de " . $contextLines . " lignes de contexte avant/après" . ANSI_RESET . "\n";
}
echo "--------------------------------------------------\n";

$foundCount = 0;
$fileCount = 0;
$filesSearched = 0;

// Utilisation de RecursiveIteratorIterator pour parcourir les répertoires
$directoryIterator = new RecursiveDirectoryIterator($startDir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
$recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

foreach ($recursiveIterator as $fileInfo) {
    /** @var SplFileInfo $fileInfo */
    $pathname = $fileInfo->getPathname();
    $filename = $fileInfo->getFilename();

    // 1. Vérifier si le chemin contient un répertoire exclu
    $isExcluded = false;
    foreach ($excludedDirs as $excludedDir) {
        // Vérifie si le chemin commence par le nom du répertoire exclu (à la racine)
        // ou s'il contient /nom_repertoire_exclu/
        if (
            strpos($pathname, $startDir . '/' . $excludedDir . '/') === 0 ||
            strpos($pathname, '/' . $excludedDir . '/') !== false
        ) {
            $isExcluded = true;
            break;
        }
        // Gérer le cas où l'exclu est à la racine même
        if ($fileInfo->isDir() && $pathname === $startDir . '/' . $excludedDir) {
            $isExcluded = true;
            break;
        }
    }

    if ($isExcluded) {
        continue; // Passe au fichier/répertoire suivant
    }

    // 2. Traiter uniquement les fichiers
    if ($fileInfo->isFile()) {
        $filesSearched++;

        // 3. Vérifier si le nom de fichier est exclu
        if (in_array($filename, $excludedFiles)) {
            continue;
        }

        // 4. Vérifier l'extension (si une liste est fournie)
        $extension = strtolower($fileInfo->getExtension());
        if (!empty($includedExtensions) && !in_array($extension, $includedExtensions)) {
            continue;
        }

        // 5. Vérifier si le fichier est lisible
        if (!$fileInfo->isReadable()) {
            echo ANSI_RED . "Erreur: Impossible de lire le fichier " . ANSI_YELLOW . $pathname . ANSI_RESET . "\n";
            continue;
        }

        // Vérifier la taille du fichier
        $fileSizeMB = $fileInfo->getSize() / (1024 * 1024);
        if ($fileSizeMB > $fileLimit) {
            echo ANSI_YELLOW . "Avertissement: Fichier ignoré car trop volumineux (" . number_format($fileSizeMB, 2) . " MB): " . $pathname . ANSI_RESET . "\n";
            continue;
        }

        // 6. Analyse du fichier - avec gestion de mémoire améliorée
        $fileHasMatch = false;
        $matches = [];
        $lineNumber = 0;

        // Utiliser fopen/fgets pour économiser la mémoire
        $handle = @fopen($pathname, "r");
        if ($handle === false) {
            echo ANSI_RED . "Erreur: Échec de l'ouverture du fichier " . ANSI_YELLOW . $pathname . ANSI_RESET . "\n";
            continue;
        }

        // Garder un tampon des dernières lignes pour le contexte
        $lineBuffer = [];

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            // Ajouter la ligne au tampon pour le contexte
            $lineBuffer[$lineNumber] = $line;

            // Nettoyer le tampon pour ne garder que ce qui est nécessaire
            if (count($lineBuffer) > $contextLines * 2 + 1) {
                $keysToRemove = array_keys($lineBuffer);
                $keysToRemove = array_slice($keysToRemove, 0, count($keysToRemove) - $contextLines - 1);
                foreach ($keysToRemove as $key) {
                    unset($lineBuffer[$key]);
                }
            }

            // Vérifier si la ligne contient le terme recherché
            if (strpos($line, $searchTerm) !== false) {
                if (!$fileHasMatch) {
                    $fileHasMatch = true;
                    $fileCount++;
                }
                $matches[$lineNumber] = $line;
                $foundCount++;
            }
        }

        fclose($handle);

        // Afficher les résultats pour ce fichier
        if ($fileHasMatch) {
            echo ANSI_YELLOW . $pathname . ANSI_RESET . "\n";

            // Trier les numéros de lignes où des correspondances ont été trouvées
            $matchLineNumbers = array_keys($matches);
            sort($matchLineNumbers);

            $lastLineDisplayed = 0;

            foreach ($matchLineNumbers as $matchLineNumber) {
                $line = $matches[$matchLineNumber];

                // Déterminer les lignes de contexte à afficher
                $startContext = max(1, $matchLineNumber - $contextLines);
                $endContext = $matchLineNumber + $contextLines;

                // Afficher un séparateur si nous avons sauté des lignes
                if ($lastLineDisplayed > 0 && $startContext > $lastLineDisplayed + 1) {
                    echo ANSI_GRAY . "  ..." . ANSI_RESET . "\n";
                }

                // Afficher les lignes de contexte avant
                for ($i = $startContext; $i < $matchLineNumber; $i++) {
                    if (isset($lineBuffer[$i]) && $i > $lastLineDisplayed) {
                        echo ANSI_GRAY . "  Ligne " . $i . ": " . rtrim($lineBuffer[$i]) . ANSI_RESET . "\n";
                    }
                }

                // Afficher la ligne avec la correspondance
                // Mettre en évidence le terme recherché
                $highlightedLine = str_replace(
                    $searchTerm,
                    ANSI_BG_RED . $searchTerm . ANSI_RESET . ANSI_RED,
                    rtrim($line)
                );
                echo "  " . ANSI_CYAN . "Ligne " . $matchLineNumber . ":" . ANSI_RESET . " " . ANSI_RED . $highlightedLine . ANSI_RESET . "\n";

                // Afficher les lignes de contexte après
                for ($i = $matchLineNumber + 1; $i <= $endContext; $i++) {
                    if (isset($lineBuffer[$i])) {
                        echo ANSI_GRAY . "  Ligne " . $i . ": " . rtrim($lineBuffer[$i]) . ANSI_RESET . "\n";
                    }
                }

                $lastLineDisplayed = max($lastLineDisplayed, $endContext);
            }

            echo "\n"; // Ligne vide entre les fichiers
        }
    }
}

echo "--------------------------------------------------\n";
echo ANSI_GREEN . "Recherche terminée." . ANSI_RESET . "\n";
echo "Fichiers scannés (correspondant aux critères) : " . $filesSearched . "\n";
echo "Résultat : " . ANSI_RED . $foundCount . ANSI_RESET . " occurrence(s) trouvée(s) dans " . ANSI_YELLOW . $fileCount . ANSI_RESET . " fichier(s).\n";

function displayHelp()
{
    $scriptName = basename(__FILE__);
    echo <<<HELP
Usage: php {$scriptName} [OPTIONS] <terme_recherche>

OPTIONS:
  -h, --help           Affiche cette aide
  -c, --context=NOMBRE Affiche N lignes de contexte avant/après chaque correspondance
  -i, --include=LISTE  Liste d'extensions à inclure (séparées par des virgules)
  -e, --exclude=LISTE  Liste de répertoires supplémentaires à exclure (séparés par des virgules)
  -l, --limit=NOMBRE   Limite de taille des fichiers en MB (défaut: 10)

Exemples:
  php {$scriptName} VITE_GRAPHQL_ENDPOINT
  php {$scriptName} --context=2 "function getUsers"
  php {$scriptName} -i="php,js" -e="tests,fixtures" -c=3 "api.call"

HELP;
}

exit(0);
