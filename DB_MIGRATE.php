<?php
/**
 * Script de migration de SQLite vers MySQL pour Camydia Agency
 * 
 * Ce script aide à migrer une base de données SQLite vers MySQL sur l'hébergement
 * Il extrait les données de SQLite et génère un script SQL pour MySQL
 * 
 * Usage:
 * 1. Placer ce fichier dans le dossier 'private'
 * 2. Exécuter via le terminal cPanel: php DB_MIGRATE.php
 * 3. Le fichier SQL généré peut être importé via phpMyAdmin
 */

// Configuration - à adapter selon votre environnement
$sqliteFile = 'database.sqlite'; // Chemin vers le fichier SQLite
$mysqlDumpFile = 'mysql_import.sql'; // Fichier SQL de sortie

// ANSI colors for terminal output
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$NC = "\033[0m"; // No Color

echo "{$YELLOW}=== Migration SQLite vers MySQL pour Camydia Agency ==={$NC}\n\n";

// Vérifier si le fichier SQLite existe
if (!file_exists($sqliteFile)) {
    echo "{$RED}Erreur: Fichier SQLite '$sqliteFile' non trouvé!{$NC}\n";
    echo "{$YELLOW}Vérifiez le chemin du fichier et réessayez.{$NC}\n";
    exit(1);
}

// Ouvrir la connexion SQLite
try {
    $sqlite = new PDO('sqlite:' . $sqliteFile);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "{$GREEN}✓ Connexion à la base SQLite établie{$NC}\n";
} catch (PDOException $e) {
    echo "{$RED}Erreur: Impossible de se connecter à la base SQLite: {$NC}\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

// Récupérer la liste des tables
try {
    $tables = [];
    $result = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $tables[] = $row['name'];
    }
    
    if (empty($tables)) {
        echo "{$YELLOW}Attention: Aucune table trouvée dans la base SQLite!{$NC}\n";
        exit(1);
    }
    
    echo "{$GREEN}✓ " . count($tables) . " tables trouvées: " . implode(', ', $tables) . "{$NC}\n";
} catch (PDOException $e) {
    echo "{$RED}Erreur lors de la récupération des tables: {$NC}\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

// Ouvrir le fichier SQL pour écriture
$sqlFile = fopen($mysqlDumpFile, 'w');
if (!$sqlFile) {
    echo "{$RED}Erreur: Impossible de créer le fichier SQL de sortie!{$NC}\n";
    exit(1);
}

// Écrire l'en-tête du fichier SQL
fwrite($sqlFile, "-- Migration SQLite vers MySQL pour Camydia Agency\n");
fwrite($sqlFile, "-- Généré le " . date('Y-m-d H:i:s') . "\n\n");
fwrite($sqlFile, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($sqlFile, "SET AUTOCOMMIT = 0;\n");
fwrite($sqlFile, "START TRANSACTION;\n");
fwrite($sqlFile, "SET time_zone = \"+00:00\";\n\n");

// Fonction pour mapper les types SQLite vers MySQL
function mapSqliteTypeToMysql($type) {
    $type = strtolower($type);
    if (strpos($type, 'int') !== false) return 'INT';
    if (strpos($type, 'char') !== false || strpos($type, 'clob') !== false || strpos($type, 'text') !== false) return 'TEXT';
    if (strpos($type, 'blob') !== false) return 'BLOB';
    if (strpos($type, 'real') !== false || strpos($type, 'floa') !== false || strpos($type, 'doub') !== false) return 'DOUBLE';
    if (strpos($type, 'bool') !== false) return 'TINYINT(1)';
    if (strpos($type, 'date') !== false || strpos($type, 'time') !== false) return 'DATETIME';
    return 'TEXT'; // Type par défaut
}

// Pour chaque table
foreach ($tables as $table) {
    echo "{$YELLOW}Traitement de la table '$table'...{$NC}\n";
    
    // Obtenir la structure de la table
    $pragma = $sqlite->query("PRAGMA table_info($table)");
    $columns = $pragma->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "{$RED}  Erreur: Impossible d'obtenir la structure de la table '$table'!{$NC}\n";
        continue;
    }
    
    // Écrire l'instruction CREATE TABLE
    fwrite($sqlFile, "-- Structure de la table `$table`\n");
    fwrite($sqlFile, "CREATE TABLE IF NOT EXISTS `$table` (\n");
    
    $primaryKeys = [];
    $columnsSql = [];
    
    foreach ($columns as $column) {
        $name = $column['name'];
        $type = mapSqliteTypeToMysql($column['type']);
        $notNull = $column['notnull'] ? ' NOT NULL' : '';
        $default = $column['dflt_value'] !== null ? " DEFAULT " . ($type == 'TEXT' ? "'" . addslashes($column['dflt_value']) . "'" : $column['dflt_value']) : '';
        
        $columnsSql[] = "  `$name` $type$notNull$default";
        
        if ($column['pk']) {
            $primaryKeys[] = $name;
        }
    }
    
    // Ajouter la clé primaire si elle existe
    if (!empty($primaryKeys)) {
        $columnsSql[] = "  PRIMARY KEY (`" . implode('`, `', $primaryKeys) . "`)";
    }
    
    fwrite($sqlFile, implode(",\n", $columnsSql) . "\n");
    fwrite($sqlFile, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");
    
    // Récupérer les données
    $dataResult = $sqlite->query("SELECT * FROM $table");
    $data = $dataResult->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($data)) {
        fwrite($sqlFile, "-- Déchargement des données de la table `$table`\n");
        
        // Pour chaque ligne
        foreach ($data as $row) {
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . addslashes($value) . "'";
                }
            }
            
            fwrite($sqlFile, "INSERT INTO `$table` (`" . implode('`, `', array_keys($row)) . "`) VALUES (" . implode(", ", $values) . ");\n");
        }
        
        fwrite($sqlFile, "\n");
        echo "{$GREEN}  ✓ " . count($data) . " lignes exportées{$NC}\n";
    } else {
        echo "{$YELLOW}  ℹ Table vide - aucune donnée à exporter{$NC}\n";
    }
}

// Finaliser le fichier SQL
fwrite($sqlFile, "COMMIT;\n");
fclose($sqlFile);

echo "\n{$GREEN}✓ Migration terminée! Fichier généré: $mysqlDumpFile{$NC}\n";
echo "{$YELLOW}Vous pouvez maintenant importer ce fichier dans MySQL via phpMyAdmin ou la ligne de commande.{$NC}\n\n";

// Instructions pour l'importation
echo "{$YELLOW}Pour importer dans MySQL via cPanel:{$NC}\n";
echo "1. Accédez à phpMyAdmin depuis cPanel\n";
echo "2. Créez une nouvelle base de données si nécessaire\n";
echo "3. Sélectionnez la base de données\n";
echo "4. Cliquez sur 'Importer' et sélectionnez le fichier $mysqlDumpFile\n";
echo "5. Cliquez sur 'Exécuter'\n\n";

echo "{$YELLOW}Pour importer via ligne de commande (si disponible):{$NC}\n";
echo "mysql -u username -p database_name < $mysqlDumpFile\n\n";

echo "{$YELLOW}N'oubliez pas de mettre à jour votre fichier .env avec les nouveaux paramètres MySQL!{$NC}\n";
echo "DB_CONNECTION=mysql\n";
echo "DB_HOST=localhost\n";
echo "DB_PORT=3306\n";
echo "DB_DATABASE=nom_base_donnees\n";
echo "DB_USERNAME=utilisateur_mysql\n";
echo "DB_PASSWORD=mot_de_passe_mysql\n";