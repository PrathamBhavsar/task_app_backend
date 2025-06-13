<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ServiceMaster.php';

class ServiceMasterController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new ServiceMaster($db), 'service master');
    }

    public function createServiceMaster($data)
    {
        $requiredFields = ['name', 'default_rate'];
        parent::store($data, $requiredFields);
    }

    public function updateServiceMaster($id, $data)
    {
        $requiredFields = ['name', 'default_rate'];
        parent::update($id, $data, $requiredFields);
    }
}
