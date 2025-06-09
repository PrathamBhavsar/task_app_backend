<?php
class Service {
    private $conn;
    private $table = 'services';
    private $id = 'service_id';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByTaskId($taskId) {
    $query = "
        SELECT m.*
        FROM services m
        JOIN task_services tm ON m.service_id = tm.service_id
        WHERE tm.task_id = :task_id
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
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
    $query = "INSERT INTO {$this->table} (service_type, quantity, rate, amount) 
              VALUES (:service_type, :quantity, :rate, :amount)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':service_type', $data['service_type']);
    $stmt->bindParam(':quantity', $data['quantity']);
    $stmt->bindParam(':rate', $data['rate']);
    $stmt->bindParam(':amount', $data['amount']);

    if ($stmt->execute()) {
        $id = $this->conn->lastInsertId();

        if (!empty($data['task_id'])) {
            $link = $this->conn->prepare("INSERT INTO task_services (service_id, task_id) VALUES (:mid, :tid)");
            $link->bindParam(':mid', $id, PDO::PARAM_INT);
            $link->bindParam(':tid', $data['task_id'], PDO::PARAM_INT);
            $link->execute();
        }

        return $this->getById($id);
    }

    return false;
}


    public function update($id, $data) {
    $query = "UPDATE {$this->table} 
              SET service_type = :service_type, quantity = :quantity, rate = :rate, amount = :amount
              WHERE {$this->id} = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':service_type', $data['service_type']);
    $stmt->bindParam(':quantity', $data['quantity']);
    $stmt->bindParam(':rate', $data['rate']);
    $stmt->bindParam(':amount', $data['amount']);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
    
        if (!empty($data['task_id'])) {
            $delete = $this->conn->prepare("DELETE FROM task_services WHERE service_id = :mid");
            $delete->bindParam(':mid', $id, PDO::PARAM_INT);
            $delete->execute();

            $insert = $this->conn->prepare("INSERT INTO task_services (service_id, task_id) VALUES (:mid, :tid)");
            $insert->bindParam(':mid', $id, PDO::PARAM_INT);
            $insert->bindParam(':tid', $data['task_id'], PDO::PARAM_INT);
            $insert->execute();
        }

        return $this->getById($id);
    }

    return false;
}


public function delete($id) {
    
    $unlink = $this->conn->prepare("DELETE FROM task_services WHERE service_id = :mid");
    $unlink->bindParam(':mid', $id, PDO::PARAM_INT);
    $unlink->execute();

    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

}
?>
