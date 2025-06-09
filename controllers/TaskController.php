<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Task.php';

class TaskController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Task($db), 'task');
    }

    public function createTask($data)
    {
        $requiredFields = [
            'deal_no', 'name', 'start_date', 'due_date',
            'priority_id', 'remarks', 'status_id',
            'created_by', 'client_id', 'designer_id'
        ];
        parent::store($data, $requiredFields);
    }

    public function updateTask($id, $data)
    {
        $requiredFields = [
            'deal_no', 'name', 'start_date', 'due_date',
            'priority_id', 'remarks', 'status_id',
            'created_by', 'client_id', 'designer_id'
        ];
        parent::update($id, $data, $requiredFields);
    }

    public function detailedTask($id)
    {
        $task = $this->model->getDetailedById($id);
        $task ? sendJson($task) : sendError("Detailed task not found", 404);
    }
}
