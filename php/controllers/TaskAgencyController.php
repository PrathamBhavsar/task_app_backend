<?php
require_once __DIR__ . '/../models/TaskAgency.php';
require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';

class TaskAgencyController
{
    private $taskAgencyModel;

    public function __construct()
    {
        $this->taskAgencyModel = new TaskAgency();
    }

    public function getTaskAgencies()
    {
        try {
            $taskAgencies = $this->taskAgencyModel->getAllTaskAgencies();

            if (!$taskAgencies) {
                sendError("No taskAgencies found", 404);
            }

            sendResponse("TaskAgencies retrieved successfully", $taskAgencies);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            sendError("Database error: Unable to fetch taskAgencies", 500);
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            sendError("An unexpected error occurred. Please try again later.", 500);
        }
    }

}
?>