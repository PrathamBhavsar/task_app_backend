<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Timeline.php';

class TimelineController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Timeline($db), 'timeline');
    }

    public function createTimeline($data)
    {
        $requiredFields = ['task_id', 'status_id', 'user_id'];
        parent::store($data, $requiredFields);
    }

    public function getAllByTaskId($taskId)
    {
        parent::getAllByTaskId($taskId);
    }

    public function updateTimeline($id, $data)
    {
        $requiredFields = ['task_id', 'status_id', 'user_id'];
        parent::update($id, $data, $requiredFields);
    }
}
