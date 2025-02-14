<?php
require_once 'models/Priority.php';
require_once 'errorHandler.php';
require_once 'helpers/response.php';

class PriorityController
{
    private $priorityModel;

    public function __construct()
    {
        $this->priorityModel = new Priority();
    }

    /**
     * Get all priorities
     */
    public function getPriorities()
    {
        try {
            $priorities = $this->priorityModel->getAllPriorities();

            if (!$priorities) {
                sendError("No priorities found", 404);
            }

            sendResponse("Priorities retrieved successfully", $priorities);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch priorities", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

    /**
     * Create a new priority
     */
    public function createPriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'])) {
            sendError("Missing required field: name", 400);
        }

        try {
            if ($this->priorityModel->createPriority($data)) {
                sendResponse("Priority created successfully", [], 201);
            } else {
                sendError("Failed to create priority", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to create priority", 500);
        }
    }

    /**
     * Update priority details
     */
    public function updatePriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['name'])) {
            sendError("Missing required fields", 400);
        }

        $priority = $this->priorityModel->findById($data['id']);
        if (!$priority) {
            sendError("Priority not found", 404);
        }

        try {
            if ($this->priorityModel->updatePriorityById($data['id'], $data)) {
                sendResponse("Priority updated successfully");
            } else {
                sendError("No changes made or failed to update priority", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to update priority", 500);
        }
    }

    /**
     * Delete a priority
     */
    public function deletePriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            sendError("Priority ID is required", 400);
        }

        $priority = $this->priorityModel->findById($data['id']);
        if (!$priority) {
            sendError("Priority not found", 404);
        }

        try {
            if ($this->priorityModel->deletePriorityById($data['id'])) {
                sendResponse("Priority deleted successfully");
            } else {
                sendError("Failed to delete priority", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to delete priority", 500);
        }
    }
}
?>
