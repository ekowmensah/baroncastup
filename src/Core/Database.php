<?php

namespace SmartCast\Core;

use PDO;
use PDOException;

/**
 * Database Connection and Query Builder
 */
class Database
{
    private $connection;
    private static $instance = null;
    
    public function __construct()
    {
        $this->connect();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect()
    {
        try {
            $dsn = "mysql:host=" . \DB_HOST . ";dbname=" . \DB_NAME . ";charset=" . \DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, \DB_USER, \DB_PASS, $options);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
    
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            
            // Bind parameters individually to handle null values properly
            foreach ($params as $key => $value) {
                if (is_null($value)) {
                    $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                } elseif (is_int($value)) {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
                } elseif (is_bool($value)) {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
                } else {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function select($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :set_{$column}";
        }
        $setClause = implode(', ', $set);
        
        // Prefix data parameters to avoid conflicts with where parameters
        $setParams = [];
        foreach ($data as $key => $value) {
            $setParams["set_{$key}"] = $value;
        }
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($setParams, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    public function commit()
    {
        return $this->connection->commit();
    }
    
    public function rollback()
    {
        return $this->connection->rollback();
    }
    
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }
    
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
}
