<?php
class ServiceMaster {
    private $conn;
    private $table = 'service_master';
    private $id = 'service_master_id';

public function __construct($db) {
    $this->conn = $db;
}

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} (name, default_rate)
                  VALUES (:name, :default_rate)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':default_rate', $data['default_rate']);

        if ($stmt->execute()) {
            $id = $this->conn->lastInsertId();
            return $this->getById($id);
        }

        return false;
    }

        public function update($id, $data) {
    $query = "UPDATE {$this->table} 
              SET name = :name, default_rate = :default_rate 
              WHERE {$this->id} = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':default_rate', $data['default_rate']);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return $this->getById($id);
    }

    return false;
}

public function delete($id) {
    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
    
}
?>
