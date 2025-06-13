<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Quote.php';

class ServiceController extends BaseController
{
    public function __construct($db)
    {
        $bill = new Bill($db);
        $quote = new Quote($db);
        $service = new Service($db, $bill, $quote);
        parent::__construct($service, 'service');
    }

    public function getAllByTaskId($taskId)
    {
        $services = $this->model->getAllByTaskId($taskId);
        sendJson($services);
    }

    public function createService($data)
    {
        $requiredFields = ['task_id', 'service_master_id', 'quantity', 'unit_price', 'total_amount'];
        parent::store($data, $requiredFields);
    }

    public function updateService($id, $data)
    {
        $requiredFields = ['task_id', 'service_master_id', 'quantity', 'unit_price', 'total_amount'];
        parent::update($id, $data, $requiredFields);
    }

    public function createMasterService($data)
    {
        if (empty($data['name']) || !isset($data['default_rate'])) {
            sendError("Missing required fields: name, default_rate", 400);
        }

        $result = $this->model->createMasterService($data);

        if ($result) {
            sendJson($result, 201);
        } else {
            sendError("Failed to create service master entry", 500);
        }
    }
}
