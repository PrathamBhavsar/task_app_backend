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
        parent::getAllByTaskId($taskId);
    }

    public function getQuoteMeasurementsByTaskId($taskId)
    {
        $result = $this->model->getQuoteMeasurementsByTaskId($taskId);

        if ($result) {
            sendJson($result, 200);
        } else {
            sendError("Failed to fetch quote measurements", 500);
        }
    }

    public function createBulk($measurements)
    {
        $measurementsById = $this->model->createBulk($measurements);
        sendJson($measurementsById);
    }

    public function createMeasurement($data)
    {
        $requiredFields = ['location', 'width', 'height', 'notes', 'task_id', 'area'];
        parent::store($data, $requiredFields);
    }

    public function updateMeasurement($id, $data)
    {
        $requiredFields = ['location', 'width', 'height', 'notes', 'task_id', 'area'];
        parent::update($id, $data, $requiredFields);
    }
}
