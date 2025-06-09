<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Service.php';

class ServiceController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Service($db), 'service');
    }

    public function getAllByTaskId($taskId)
    {
        $service = $this->model->getAllByTaskId($taskId);
        sendJson($service);
    }

    public function createService($data)
    {
        $requiredFields = ['service_type', 'quantity', 'rate', 'amount', 'task_id'];
        parent::store($data, $requiredFields);
    }

    public function updateService($id, $data)
    {
        $requiredFields = ['service_type', 'quantity', 'rate', 'amount', 'task_id'];
        parent::update($id, $data, $requiredFields);
    }
}
