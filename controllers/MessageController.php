<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Message.php';

class MessageController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Message($db), 'message');
    }

    public function createMessage($data)
    {
        $requiredFields = ['task_id', 'message', 'user_id'];
        parent::store($data, $requiredFields);
    }

    public function getAllByTaskId($taskId)
    {
        $taskTimeslines = $this->model->getAllByTaskId($taskId);
        sendJson($taskTimeslines);
    }

    public function updateMessage($id, $data)
    {
        $requiredFields = ['task_id', 'message', 'user_id'];
        parent::update($id, $data, $requiredFields);
    }
}
