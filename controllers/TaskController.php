<?php
require_once 'models/Task.php';

class TaskController {
    private $taskModel;

    public function __construct() {
        $this->taskModel = new Task();
    }

    /**
     * Get all tasks
     */
    public function getTasks() {
        $tasks = $this->taskModel->getAllTasks();
        echo json_encode(["status" => "success", "data" => $tasks]);
    }

    /**
     * Create a new task
     */
    public function createTask() {
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (!$this->validateTaskData($data)) return;
    
        $result = $this->taskModel->createTask($data);
    
        if ($result && isset($result['id'])) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Task created", "task_id" => $result['id']]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create Task"]);
        }
    }

    /**
     * Update an existing task
     */
    public function updateTask() {
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing task ID"]);
            return;
        }
    
        if (!$this->validateTaskData($data)) return;
    
        $updatedRows = $this->taskModel->updateTask($data);
    
        if ($updatedRows > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Task updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update Task"]);
        }
    }

    /**
     * Delete a task and its related records
     */
    public function deleteTask() {
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing task ID"]);
            return;
        }
    
        $deletedRows = $this->taskModel->deleteTask($data['id']);
    
        if ($deletedRows > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Task deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete Task"]);
        }
    }
    

    /**
     * Validate required task data fields
     */
    private function validateTaskData($data) {
        $requiredFields = ['deal_no', 'name', 'start_date', 'due_date', 'priority', 'created_by', 'status'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing or empty field: $field"]);
                return false;
            }
        }
        return true;
    }
}
?>
