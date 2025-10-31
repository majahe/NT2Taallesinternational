<?php
/**
 * QueryBuilder - Secure database query abstraction layer
 * Prevents SQL injection by using prepared statements
 */

class QueryBuilder {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Select records from database
     */
    public function select($table, $columns = '*', $where = [], $orderBy = null, $limit = null) {
        $sql = "SELECT $columns FROM $table";
        
        $params = [];
        $types = '';
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $types .= $this->getParamType($value);
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        }
        
        return $this->conn->query($sql);
    }
    
    /**
     * Count records
     */
    public function count($table, $where = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        
        $params = [];
        $types = '';
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $types .= $this->getParamType($value);
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        }
        
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['count'];
    }
    
    /**
     * Insert record
     */
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $types = '';
        $params = [];
        foreach ($data as $value) {
            $types .= $this->getParamType($value);
            $params[] = $value;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update record
     */
    public function update($table, $data, $where) {
        $sets = [];
        $types = '';
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $types .= $this->getParamType($value);
            $params[] = $value;
        }
        
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $types .= $this->getParamType($value);
            $params[] = $value;
        }
        
        $sql = "UPDATE $table SET " . implode(", ", $sets) . " WHERE " . implode(" AND ", $conditions);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Delete record
     */
    public function delete($table, $where) {
        $conditions = [];
        $types = '';
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $types .= $this->getParamType($value);
            $params[] = $value;
        }
        
        $sql = "DELETE FROM $table WHERE " . implode(" AND ", $conditions);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Execute raw query safely (when needed)
     */
    public function query($sql, $params = []) {
        if (empty($params)) {
            return $this->conn->query($sql);
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                $types .= $this->getParamType($param);
            }
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    /**
     * Get parameter type for bind_param
     */
    private function getParamType($value) {
        if (is_int($value)) {
            return 'i';
        } elseif (is_float($value)) {
            return 'd';
        } else {
            return 's';
        }
    }
}

