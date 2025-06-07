<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/TaskPriority.php';

class TaskPriorityController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new TaskPriority($db), 'priority');
    }

    public function createTaskPriority($data)
    {
        $requiredFields = ['name', 'color'];
        parent::store($data, $requiredFields);
    }

    public function updateTaskPriority($id, $data)
    {
        $requiredFields = ['name', 'color'];
        parent::update($id, $data, $requiredFields);
    }
}
