<?php
class Measurement
{
    private $conn;
    private $table = 'measurements';
    private $id = 'measurement_id';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByTaskId($taskId)
    {
        $query = "
        SELECT *
        FROM measurements 
        WHERE task_id = :task_id
    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (location, width, height, notes, task_id) 
              VALUES (:location, :width, :height, :notes, :task_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':task_id', $data['task_id']);

        if ($stmt->execute()) {
            $id = $this->conn->lastInsertId();

            return $this->getById($id);
        }

        return false;
    }


    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
              SET location = :location, width = :width, height = :height, notes = :notes, task_id = :task_id
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return $this->getById($id);
        }

        return false;
    }


    public function delete($id)
    {

        $unlink = $this->conn->prepare("DELETE FROM task_measurements WHERE measurement_id = :mid");
        $unlink->bindParam(':mid', $id, PDO::PARAM_INT);
        $unlink->execute();

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
