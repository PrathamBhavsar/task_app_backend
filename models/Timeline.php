<?php
class Timeline
{
    private $conn;
    private $table = 'task_timelines';
    private $id = 'timeline_id';

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

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllByTaskId($taskId)
    {
        $query = "
        SELECT 
            tt.timeline_id,
            tt.task_id,
            tt.created_at,
            
            u.user_id AS u_user_id,
            u.name AS u_name,
            u.email AS u_email,
            u.user_type AS u_role,
            u.address AS u_address,
            u.contact_no AS u_contact,
            u.profile_bg_color AS u_profile_bg_color,
            
            ts.status_id AS s_status_id,
            ts.name AS s_name,
            ts.slug AS s_slug,
            ts.color AS s_color

        FROM task_timelines tt
        JOIN users u ON tt.user_id = u.user_id
        JOIN task_statuses ts ON tt.status_id = ts.status_id
        WHERE tt.task_id = :task_id
        ORDER BY tt.created_at ASC
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'timeline_id' => $row['timeline_id'],
                'task_id' => $row['task_id'],
                'created_at' => $row['created_at'],
                'user' => [
                    'user_id' => $row['u_user_id'],
                    'name' => $row['u_name'],
                    'email' => $row['u_email'],
                    'user_type' => $row['u_role'],
                    'address' => $row['u_address'],
                    'contact_no' => $row['u_contact'],
                    'profile_bg_color' => $row['u_profile_bg_color'],
                ],
                'status' => [
                    'status_id' => $row['s_status_id'],
                    'name' => $row['s_name'],
                    'slug' => $row['s_slug'],
                    'color' => $row['s_color'],
                ],
            ];
        }

        return $result;
    }



    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (task_id, status_id, user_id) VALUES (:task_id, :status_id, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':status_id', $data['status_id']);
        $stmt->bindParam(':user_id', $data['user_id']);

        if ($stmt->execute()) {
            $id = $this->conn->lastInsertId();
            return $this->getById($id);
        }

        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
              SET task_id = :task_id, status_id = :status_id, user_id = :user_id
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':status_id', $data['status_id']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return $this->getById($id);
        }

        return false;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
