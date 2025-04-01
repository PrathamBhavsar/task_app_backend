<?php
require_once 'models/Status.php';
require_once 'errorHandler.php';
require_once 'helpers/response.php';

class StatusController
{
    private $statusModel;

    public function __construct()
    {
        $this->statusModel = new Status();
    }

    /**
     * Get all statuses
     */
    public function getStatuses()
    {
        try {
            $statuses = $this->statusModel->getAllStatuses();

            if (!$statuses) {
                sendError("No statuses found", 404);
            }

            sendResponse("Statuses retrieved successfully", $statuses);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch statuses", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

    /**
     * Create a new status
     */
    public function createStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'])) {
            sendError("Missing required field: name", 400);
        }

        try {
            if ($this->statusModel->createStatus($data)) {
                sendResponse("Status created successfully", [], 201);
            } else {
                sendError("Failed to create status", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to create status", 500);
        }
    }

    /**
     * Update an existing status
     */
    public function updateStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['name'])) {
            sendError("Missing required fields", 400);
        }

        $status = $this->statusModel->findById($data['id']);
        if (!$status) {
            sendError("Status not found", 404);
        }

        try {
            if ($this->statusModel->updateStatusById($data['id'], $data)) {
                sendResponse("Status updated successfully");
            } else {
                sendError("No changes made or failed to update status", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to update status", 500);
        }
    }

    /**
     * Delete a status
     */
    public function deleteStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            sendError("Status ID is required", 400);
        }

        $status = $this->statusModel->findById($data['id']);
        if (!$status) {
            sendError("Status not found", 404);
        }

        try {
            if ($this->statusModel->deleteStatusById($data['id'])) {
                sendResponse("Status deleted successfully");
            } else {
                sendError("Failed to delete status", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to delete status", 500);
        }
    }
}
?>
