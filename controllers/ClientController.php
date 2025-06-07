<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Client.php';

class ClientController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Client($db), 'client');
    }

    public function createClient($data)
    {
        $requiredFields = ['name', 'contact_no', 'email', 'address'];
        parent::store($data, $requiredFields);
    }

    public function updateClient($id, $data)
    {
        $requiredFields = ['name', 'contact_no', 'email', 'address'];
        parent::update($id, $data, $requiredFields);
    }
}
