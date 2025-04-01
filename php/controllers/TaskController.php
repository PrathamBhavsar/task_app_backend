<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../helpers/response.php';

class TaskController
{
    private $taskModel;

    public function __construct()
    {
        $this->taskModel = new Task();
    }


    /**
     * Get all Tasks
     */
    public function getTasks()
    {
        try {
            $tasks = $this->taskModel->getAllTasks();

            if (!$tasks) {
                sendError("No tasks found", 404);
            }

            sendResponse("Tasks retrieved successfully", $tasks);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch tasks", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }


    /**
     * Get tasks where salesperson_id or agency_id matches the given ID
     */
    public function getSpecificTasks($id)
    {
        try {
            $tasks = $this->taskModel->getTasksBySalespersonOrAgency($id);

            if (!$tasks) {
                sendError("No tasks found for the given ID", 404);
            }

            sendResponse("Tasks retrieved successfully", $tasks);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch tasks", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

    /**
     * Create a new task
     */
    public function createTask($data)
    {
        if (!$this->validateTaskData($data)) {
            return;
        }

        try {
            $result = $this->taskModel->createTask($data);

            if ($result && isset($result['id'])) {
                sendResponse("Task created successfully", ["task_id" => $result['id']], 201);
            } else {
                sendError("Failed to create task", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to create task", 500);
        }
    }

    /**
     * Update an existing task
     */
    public function updateTask($data)
    {
        if (!isset($data['id'])) {
            sendError("Task ID is required", 400);
            return;
        }

        if (!$this->validateTaskData($data)) {
            return;
        }

        try {
            $updatedRows = $this->taskModel->updateTask($data);

            if ($updatedRows > 0) {
                sendResponse("Task updated successfully");
            } else {
                sendError("No changes made or failed to update task", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to update task", 500);
        }
    }

    /**
     * Delete a task
     */
    public function deleteTask($id)
    {
        try {
            $deletedRows = $this->taskModel->deleteTask($id);

            if ($deletedRows > 0) {
                sendResponse("Task deleted successfully");
            } else {
                sendError("Failed to delete task or task not found", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to delete task", 500);
        }
    }

    /**
     * Validate required task data fields
     */
    private function validateTaskData($data)
    {
        $requiredFields = ['deal_no', 'name', 'start_date', 'due_date', 'priority', 'created_by', 'status'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendError("Missing or empty field: $field", 400);
                return false;
            }
        }
        return true;
    }
}
?>