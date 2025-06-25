<?php
class Task
{
    private $conn;
    private $table = 'tasks';
    private $id = 'task_id';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT {$this->id} FROM {$this->table}");
        $stmt->execute();
        $taskIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $detailedTasks = [];
        foreach ($taskIds as $taskId) {
            $detailed = $this->getDetailedById($taskId);
            if ($detailed) {
                $detailedTasks[] = $detailed;
            }
        }

        return $detailedTasks;
    }


    public function updateStatus($taskId, $statusId, $userId, $agencyId)
    {
        try {

            $this->conn->beginTransaction();

            $updateQuery = "UPDATE {$this->table} SET status = :status, agency_id = :agency_id WHERE {$this->id} = :task_id";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->execute([
                ':status' => $statusId,
                ':task_id' => $taskId,
                ':agency_id' => $agencyId
            ]);

            $timelineQuery = "INSERT INTO task_timelines (user_id, task_id, status) VALUES (:user_id, :task_id, :status)";
            $stmt = $this->conn->prepare($timelineQuery);
            $stmt->execute([
                ':user_id' => $userId,
                ':task_id' => $taskId,
                ':status' => $statusId
            ]);

            $this->conn->commit();

            return $this->getDetailedById($taskId);
        } catch (PDOException $e) {

            $this->conn->rollBack();
            throw new Exception("Failed to update task status: " . $e->getMessage());
        }
    }


    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDetailedById($id)
    {
        $query = "
        SELECT 
            t.*,

            u.user_id AS created_by_user_id,
            u.name AS created_by_name,
            u.email AS created_by_email,
            u.user_type AS created_by_role,
            u.address AS created_by_address,
            u.profile_bg_color AS created_by_profile_bg_color,

            c.client_id AS client_id,
            c.name AS client_name,
            c.email AS client_email,
            c.contact_no AS client_contact_no,
            c.address AS client_address,

            d.designer_id AS designer_id,
            d.name AS designer_name,
            d.firm_name AS designer_firm,
            d.contact_no AS designer_contact,
            d.address AS designer_address,
            d.profile_bg_color AS designer_color,

            agency.user_id AS agency_id,
            agency.name AS agency_name,
            agency.created_at AS agency_created_at,
            agency.email AS agency_email,
            agency.contact_no AS agency_contact,
            agency.address AS agency_address,
            agency.user_type AS agency_user_type,
            agency.profile_bg_color AS agency_color

        FROM tasks t
        LEFT JOIN users u ON t.created_by = u.user_id
        LEFT JOIN clients c ON t.client_id = c.client_id
        LEFT JOIN designers d ON t.designer_id = d.designer_id
        LEFT JOIN users agency ON t.agency_id = agency.user_id
        WHERE t.task_id = :id
        LIMIT 1
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $userQuery = "
        SELECT u.user_id, u.name, u.email, u.contact_no, u.user_type, u.address, u.profile_bg_color
        FROM task_users tu
        JOIN users u ON tu.user_id = u.user_id
        WHERE tu.task_id = :task_id
    ";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->bindParam(':task_id', $id, PDO::PARAM_INT);
        $userStmt->execute();
        $assignedUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "task_id" => $row['task_id'],
            "deal_no" => $row['deal_no'],
            "name" => $row['name'],
            "created_at" => $row['created_at'],
            "start_date" => $row['start_date'],
            "due_date" => $row['due_date'],
            "remarks" => $row['remarks'],
            "status" => $row['status'],
            "priority" => $row['priority'],

            "created_by" => [
                "user_id" => $row['created_by_user_id'],
                "name" => $row['created_by_name'],
                "email" => $row['created_by_email'],
                "role" => $row['created_by_role'],
                "address" => $row['created_by_address'],
                "profile_bg_color" => $row['created_by_profile_bg_color'],
            ],

            "client" => [
                "client_id" => $row['client_id'],
                "name" => $row['client_name'],
                "email" => $row['client_email'],
                "contact_no" => $row['client_contact_no'],
                "address" => $row['client_address']
            ],

            "agency" => $row['agency_id'] ? [
                "user_id" => $row['agency_id'],
                "created_at" => $row['agency_created_at'],
                "name" => $row['agency_name'],
                "email" => $row['agency_email'],
                "contact_no" => $row['agency_contact'],
                "address" => $row['agency_address'],
                "user_type" => $row['agency_user_type'],
                "profile_bg_color" => $row['agency_color']
            ] : null,

            "designer" => [
                "designer_id" => $row['designer_id'],
                "name" => $row['designer_name'],
                "firm_name" => $row['designer_firm'],
                "contact_no" => $row['designer_contact'],
                "address" => $row['designer_address'],
                "profile_bg_color" => $row['designer_color']
            ],

            "assigned_users" => $assignedUsers
        ];
    }


    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
        (deal_no, name, start_date, due_date, priority, remarks, status, created_by, client_id, designer_id, agency_id)
        VALUES 
        (:deal_no, :name, :start_date, :due_date, :priority, :remarks, :status, :created_by, :client_id, :designer_id, :agency_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':deal_no', $data['deal_no']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':remarks', $data['remarks']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':client_id', $data['client_id']);
        $stmt->bindParam(':agency_id', $data['agency_id']);
        $stmt->bindParam(':designer_id', $data['designer_id']);

        if ($stmt->execute()) {
            $taskId = $this->conn->lastInsertId();

            // Insert into task_users
            if (!empty($data['assigned_users']) && is_array($data['assigned_users'])) {
                foreach ($data['assigned_users'] as $userId) {
                    $insertUser = $this->conn->prepare("INSERT INTO task_users (task_id, user_id) VALUES (:task_id, :user_id)");
                    $insertUser->bindParam(':task_id', $taskId, PDO::PARAM_INT);
                    $insertUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $insertUser->execute();
                }
            }

            // Insert into timeline
            $timelineInsert = $this->conn->prepare("
            INSERT INTO task_timelines (task_id, status, user_id)
            VALUES (:task_id, :status, :user_id)
        ");
            $timelineInsert->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $timelineInsert->bindValue(':status', 'Created', PDO::PARAM_INT);
            $timelineInsert->bindParam(':user_id', $data['created_by'], PDO::PARAM_INT);
            $timelineInsert->execute();

            return $this->getDetailedById($taskId);
        }

        return false;
    }




    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET 
        deal_no = :deal_no,
        name = :name,
        start_date = :start_date,
        due_date = :due_date,
        priority = :priority,
        remarks = :remarks,
        status = :status,
        created_by = :created_by,
        client_id = :client_id,
        designer_id = :designer_id,
        agency_id = :agency_id
        WHERE {$this->id} = :id";


        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':deal_no', $data['deal_no']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':remarks', $data['remarks']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':client_id', $data['client_id']);
        $stmt->bindParam(':agency_id', $data['agency_id']);
        $stmt->bindParam(':designer_id', $data['designer_id']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->conn->prepare("DELETE FROM task_users WHERE task_id = :task_id")
                ->execute([':task_id' => $id]);

            if (!empty($data['assigned_users']) && is_array($data['assigned_users'])) {
                foreach ($data['assigned_users'] as $userId) {
                    $insertUser = $this->conn->prepare("INSERT INTO task_users (task_id, user_id) VALUES (:task_id, :user_id)");
                    $insertUser->bindParam(':task_id', $id, PDO::PARAM_INT);
                    $insertUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $insertUser->execute();
                }
            }

            return $this->getDetailedById($id);
        }

        return false;
    }



    public function delete($id)
    {
        $this->conn->prepare("DELETE FROM task_users WHERE task_id = :task_id")
            ->execute([':task_id' => $id]);

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
