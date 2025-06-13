<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Measurement.php';

class MeasurementController extends BaseController
{
    public function __construct($db)
    {
        $quote = new Quote($db);
        $measurement = new Measurement($db, $quote);
        parent::__construct($measurement, 'measurement');
    }

    public function getAllByTaskId($taskId)
    {
        $measurements = $this->model->getAllByTaskId($taskId);
        sendJson($measurements);
    }

    public function createMeasurement($data)
    {
        $requiredFields = ['location', 'width', 'height', 'notes', 'task_id'];
        parent::store($data, $requiredFields);
    }

    public function updateMeasurement($id, $data)
    {
        $requiredFields = ['location', 'width', 'height', 'notes', 'task_id'];
        parent::update($id, $data, $requiredFields);
    }
}
