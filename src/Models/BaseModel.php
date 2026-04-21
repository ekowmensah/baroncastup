<?php

namespace SmartCast\Models;

use SmartCast\Core\Database;

/**
 * Base Model Class
 */
abstract class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }
    
    public function findAll($conditions = [], $orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->select($sql, $params);
    }
    
    public function create($data)
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            // Check if timestamp columns exist in the table
            $columns = $this->getTableColumns();
            
            if (in_array('created_at', $columns)) {
                $filteredData['created_at'] = date('Y-m-d H:i:s');
            }
            
            if (in_array('updated_at', $columns)) {
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->db->insert($this->table, $filteredData);
    }
    
    public function update($id, $data)
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $columns = $this->getTableColumns();
            if (in_array('updated_at', $columns)) {
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->db->update(
            $this->table,
            $filteredData,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->selectOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    protected function getTableColumns()
    {
        static $columnCache = [];
        
        if (!isset($columnCache[$this->table])) {
            try {
                $sql = "DESCRIBE {$this->table}";
                $columns = $this->db->select($sql);
                $columnCache[$this->table] = array_column($columns, 'Field');
            } catch (\Exception $e) {
                // If we can't get columns, return empty array
                $columnCache[$this->table] = [];
            }
        }
        
        return $columnCache[$this->table];
    }
    
    public function getDatabase()
    {
        return $this->db;
    }
}
