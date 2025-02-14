<?php
require_once __DIR__ . '/../models/Designer.php';
require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';

class DesignerController
{
    private $designerModel;

    public function __construct()
    {
        $this->designerModel = new Designer();
    }

    /**
     * Get all designers
     */
    public function getDesigners()
    {
        try {
            $designers = $this->designerModel->getAllDesigners();

            if (!$designers) {
                sendError("No designers found", 404);
            }

            sendResponse("Designers retrieved successfully", $designers);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch designers", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

    /**
     * Create a new designer
     */
    public function createDesigner()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'], $data['email'])) {
            sendError("Missing required fields", 400);
        }

        try {
            if ($this->designerModel->createDesigner($data)) {
                sendResponse("Designer created successfully", [], 201);
            } else {
                sendError("Failed to create designer", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to create designer", 500);
        }
    }

    /**
     * Update designer details
     */
    public function updateDesigner()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['name'], $data['email'])) {
            sendError("Missing required fields", 400);
        }

        $designer = $this->designerModel->findById($data['id']);
        if (!$designer) {
            sendError("Designer not found", 404);
        }

        try {
            if ($this->designerModel->updateDesignerById($data['id'], $data)) {
                sendResponse("Designer updated successfully");
            } else {
                sendError("No changes made or failed to update designer", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to update designer", 500);
        }
    }

    /**
     * Delete a designer
     */
    public function deleteDesigner()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            sendError("Designer ID is required", 400);
        }

        $designer = $this->designerModel->findById($data['id']);
        if (!$designer) {
            sendError("Designer not found", 404);
        }

        try {
            if ($this->designerModel->deleteDesignerById($data['id'])) {
                sendResponse("Designer deleted successfully");
            } else {
                sendError("Failed to delete designer", 500);
            }
        } catch (Exception $e) {
            sendError("Database error: Unable to delete designer", 500);
        }
    }
}
?>
