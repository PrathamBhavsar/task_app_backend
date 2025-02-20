<?php
require_once 'config/database.php';

class Task
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all tasks from the database
     */
    public function getAllTasks()
    {
        try {
            $query = "
                SELECT HEX(id) AS id, deal_no, name, created_at, start_date, due_date, priority, 
                       HEX(created_by) AS created_by, HEX(salesperson_id) AS salesperson_id, 
                       HEX(agency_id) AS agency_id, HEX(client_id) AS client_id, 
                       HEX(designer_id) AS designer_id, remarks, status 
                FROM tasks
                ORDER BY created_at DESC
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $tasks ?: [];
        } catch (PDOException $e) {
            error_log("Database Query Error (getAllTasks): " . $e->getMessage());
            throw new Exception("Database error while fetching tasks");
        }
    }

    /**
     * Get tasks where salesperson_id or agency_id matches the given ID
     */
    public function getTasksBySalespersonOrAgency($id)
    {
        try {
            $query = "
                SELECT HEX(id) AS id, deal_no, name, created_at, start_date, due_date, priority, 
                       HEX(created_by) AS created_by, HEX(salesperson_id) AS salesperson_id, 
                       HEX(agency_id) AS agency_id, HEX(client_id) AS client_id, 
                       HEX(designer_id) AS designer_id, remarks, status 
                FROM tasks
                WHERE salesperson_id = UNHEX(?) OR agency_id = UNHEX(?)
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id, $id]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $tasks ?: [];
        } catch (PDOException $e) {
            error_log("Database Query Error (getTasksBySalespersonOrAgency): " . $e->getMessage());
            throw new Exception("Database error while fetching specific tasks");
        }
    }

    /**
     * Create a new task
     */
    public function createTask($data)
    {
        try {
            $query = "INSERT INTO tasks (id, deal_no, name, created_at, start_date, due_date, priority, created_by, 
                                         salesperson_id, agency_id, client_id, designer_id, remarks, status)
                      VALUES (UNHEX(?), ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, UNHEX(?), 
                              UNHEX(?), UNHEX(?), UNHEX(?), UNHEX(?), ?, ?)";

            $stmt = $this->conn->prepare($query);
            $taskId = bin2hex(random_bytes(16));

            $stmt->execute([
                $taskId,
                $data['deal_no'],
                $data['name'],
                $data['start_date'],
                $data['due_date'],
                $data['priority'],
                $data['created_by'],
                $data['salesperson_id'] ?? null,
                $data['agency_id'] ?? null,
                $data['client_id'] ?? null,
                $data['designer_id'] ?? null,
                $data['remarks'] ?? '',
                $data['status']
            ]);

            return ["id" => $taskId];
        } catch (Exception $e) {
            error_log("Database Transaction Error (createTask): " . $e->getMessage());
            throw new Exception("Database error while creating task");
        }
    }

    /**
     * Update an existing task
     */
    public function updateTask($data)
    {
        try {
            $query = "UPDATE tasks 
                      SET deal_no=?, name=?, start_date=?, due_date=?, priority=?, created_by=UNHEX(?), 
                          salesperson_id=UNHEX(?), agency_id=UNHEX(?), client_id=UNHEX(?), designer_id=UNHEX(?), 
                          remarks=?, status=? 
                      WHERE id=UNHEX(?)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['deal_no'],
                $data['name'],
                $data['start_date'],
                $data['due_date'],
                $data['priority'],
                $data['created_by'],
                $data['salesperson_id'] ?? null,
                $data['agency_id'] ?? null,
                $data['client_id'] ?? null,
                $data['designer_id'] ?? null,
                $data['remarks'] ?? '',
                $data['status'],
                $data['id']
            ]);

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Database Transaction Error (updateTask): " . $e->getMessage());
            throw new Exception("Database error while updating task");
        }
    }

    /**
     * Delete a task
     */
    public function deleteTask($id)
    {
        try {
            $query = "DELETE FROM tasks WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Database Transaction Error (deleteTask): " . $e->getMessage());
            throw new Exception("Database error while deleting task");
        }
    }
}
?>