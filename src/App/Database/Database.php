<?php

namespace App\App\Database;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new PDO('sqlite:' . __DIR__ . '/../../../database.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec('PRAGMA foreign_keys = ON;');
            
            // Initialiser la base de données avec le schéma si nécessaire
            $this->initializeDatabase();
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeDatabase()
    {
        $schemaFile = __DIR__ . '/schema.sql';
        $sql = file_get_contents($schemaFile);
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Erreur lors de l'initialisation de la base de données: " . $e->getMessage());
        }
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Erreur d'exécution de requête: " . $e->getMessage());
        }
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            die("Erreur d'insertion: " . $e->getMessage());
        }
    }

    public function update($table, $data, $condition)
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $sets);
        
        $sql = "UPDATE {$table} SET {$setString} WHERE {$condition}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die("Erreur de mise à jour: " . $e->getMessage());
        }
    }

    public function delete($table, $condition, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$condition}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die("Erreur de suppression: " . $e->getMessage());
        }
    }

    public function find($table, $condition = '1', $params = [])
    {
        $sql = "SELECT * FROM {$table} WHERE {$condition}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die("Erreur de recherche: " . $e->getMessage());
        }
    }

    public function findAll($table, $condition = '1', $params = [], $orderBy = null)
    {
        $sql = "SELECT * FROM {$table} WHERE {$condition}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur de recherche: " . $e->getMessage());
        }
    }
}