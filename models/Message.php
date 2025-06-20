<?php
class Message
{
    private $conn;
    private $table = 'task_messages';
    private $id = 'message_id';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "
        SELECT m.*, u.*
        FROM {$this->table} m
        JOIN users u ON m.user_id = u.user_id
        ORDER BY m.created_at ASC
    ";
        $stmt = $this->conn->prepare($query);
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
            m.message_id,
            m.task_id,
            m.message,
            m.created_at AS message_created_at,
            u.user_id,
            u.created_at AS user_created_at,
            u.name,
            u.email,
            u.contact_no,
            u.address,
            u.user_type,
            u.profile_bg_color
        FROM {$this->table} m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.task_id = :task_id
        ORDER BY m.created_at ASC
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];

        foreach ($results as $row) {
            $formatted[] = [
                'message_id' => $row['message_id'],
                'task_id' => $row['task_id'],
                'message' => $row['message'],
                'user' => [
                    'user_id' => $row['user_id'],
                    'created_at' => $row['user_created_at'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'contact_no' => $row['contact_no'],
                    'address' => $row['address'],
                    'user_type' => $row['user_type'],
                    'profile_bg_color' => $row['profile_bg_color'],
                ]
            ];
        }

        return ['data' => $formatted];
    }



    public function getDetailedMessageById($id)
    {
        $query = "
        SELECT 
            m.message_id,
            m.task_id,
            m.message,
            m.created_at AS message_created_at,

            u.user_id,
            u.created_at AS user_created_at,
            u.name,
            u.email,
            u.contact_no,
            u.address,
            u.user_type,
            u.profile_bg_color
        FROM {$this->table} m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.{$this->id} = :id
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['data' => []];
        }

        $message = [
            "message_id" => $row["message_id"],
            "task_id" => $row["task_id"],
            "message" => $row["message"],
            "user" => [
                "user_id" => $row["user_id"],
                "created_at" => $row["user_created_at"],
                "name" => $row["name"],
                "email" => $row["email"],
                "contact_no" => $row["contact_no"],
                "address" => $row["address"],
                "user_type" => $row["user_type"],
                "profile_bg_color" => $row["profile_bg_color"],
            ]
        ];

        return ["data" => [$message]];
    }


    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (task_id, message, user_id) VALUES (:task_id, :message, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':user_id', $data['user_id']);

        if ($stmt->execute()) {
            $id = $this->conn->lastInsertId();
            return $this->getDetailedMessageById($id);
        }

        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} 
              SET task_id = :task_id, message = :message, user_id = :user_id
              WHERE {$this->id} = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return $this->getDetailedMessageById($id);
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
