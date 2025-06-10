<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Bill.php';

class BillController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Bill($db), 'bill');
    }

    public function createBill($data)
    {
        $requiredFields = ['due_date', 'subtotal', 'tax', 'total', 'additional_notes', 'status'];
        parent::store($data, $requiredFields);
    }

    public function updateBill($id, $data)
    {
        $requiredFields = ['due_date', 'subtotal', 'tax', 'total', 'additional_notes', 'status'];
        parent::update($id, $data, $requiredFields);
    }
}
