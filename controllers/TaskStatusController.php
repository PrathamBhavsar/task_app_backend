<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/TaskStatus.php';

class TaskStatusController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new TaskStatus($db), 'priority');
    }

    public function createTaskStatus($data)
    {
        $requiredFields = ['name', 'color', 'slug'];
        parent::store($data, $requiredFields);
    }

    public function updateTaskStatus($id, $data)
    {
        $requiredFields = ['name', 'color', 'slug'];
        parent::update($id, $data, $requiredFields);
    }
}
