<?php
class Measurement {
    private $conn;
    private $table = 'measurements';
    private $id = 'measurement_id';

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
        FROM measurements m
        JOIN task_measurements tm ON m.measurement_id = tm.measurement_id
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
    $query = "INSERT INTO {$this->table} (location, width, height, notes) 
              VALUES (:location, :width, :height, :notes)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':location', $data['location']);
    $stmt->bindParam(':width', $data['width']);
    $stmt->bindParam(':height', $data['height']);
    $stmt->bindParam(':notes', $data['notes']);

    if ($stmt->execute()) {
        $id = $this->conn->lastInsertId();

        if (!empty($data['task_id'])) {
            $link = $this->conn->prepare("INSERT INTO task_measurements (measurement_id, task_id) VALUES (:mid, :tid)");
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
              SET location = :location, width = :width, height = :height, notes = :notes
              WHERE {$this->id} = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':location', $data['location']);
    $stmt->bindParam(':width', $data['width']);
    $stmt->bindParam(':height', $data['height']);
    $stmt->bindParam(':notes', $data['notes']);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // If task_id is passed, update the mapping
        if (!empty($data['task_id'])) {
            $delete = $this->conn->prepare("DELETE FROM task_measurements WHERE measurement_id = :mid");
            $delete->bindParam(':mid', $id, PDO::PARAM_INT);
            $delete->execute();

            $insert = $this->conn->prepare("INSERT INTO task_measurements (measurement_id, task_id) VALUES (:mid, :tid)");
            $insert->bindParam(':mid', $id, PDO::PARAM_INT);
            $insert->bindParam(':tid', $data['task_id'], PDO::PARAM_INT);
            $insert->execute();
        }

        return $this->getById($id);
    }

    return false;
}


public function delete($id) {
    
    $unlink = $this->conn->prepare("DELETE FROM task_measurements WHERE measurement_id = :mid");
    $unlink->bindParam(':mid', $id, PDO::PARAM_INT);
    $unlink->execute();

    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

}
?>
