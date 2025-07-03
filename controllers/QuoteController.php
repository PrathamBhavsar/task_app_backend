<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Quote.php';

class QuoteController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Quote($db), 'quote');
    }

    public function getAllByTaskId($taskId)
    {
        parent::getAllByTaskId($taskId);
    }

    public function createQuote($data)
    {
        $requiredFields = ['subtotal', 'tax', 'total', 'task_id'];
        parent::store($data, $requiredFields);
    }

    public function updateQuote($id, $data)
    {
        $requiredFields = ['subtotal', 'tax', 'total', 'task_id'];
        parent::update($id, $data, $requiredFields);
    }


    public function updateQuoteMeasurements($data)
    {
        // if (empty($data['measurements'])) {
        //     sendError("Missing required fields: name, default_rate", 400);
        // }
        $result = $this->model->updateQuoteMeasurements($data);

        if ($result) {
            sendJson($result, 201);
        } else {
            sendError("Failed to update quote measurements", 500);
        }
    }
}
