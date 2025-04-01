<?php
require_once __DIR__ . '/../models/TaskSalesperson.php';
require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';

class TaskSalespersonController
{
    private $taskSalespersonModel;

    public function __construct()
    {
        $this->taskSalespersonModel = new TaskSalesperson();
    }

    public function getTaskSalespersons()
    {
        try {
            $taskSalespersons = $this->taskSalespersonModel->getAllTaskSalespersons();

            if (!$taskSalespersons) {
                sendError("No taskSalespersons found", 404);
            }

            sendResponse("TaskSalespersons retrieved successfully", $taskSalespersons);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch taskSalespersons", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

}
?>