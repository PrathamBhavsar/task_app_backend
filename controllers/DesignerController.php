<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Designer.php';


class DesignerController extends BaseController
{
    public function __construct($db)
    {
        parent::__construct(new Designer($db), 'designer');
    }

    public function createDesigner($data)
    {
        $requiredFields = ['name', 'contact_no', 'address', 'firm_name', 'profile_bg_color'];
        parent::store($data, $requiredFields);
    }

    public function updateDesigner($id, $data)
    {
        $requiredFields = ['name', 'contact_no', 'address', 'firm_name', 'profile_bg_color'];
        parent::update($id, $data, $requiredFields);
    }
}


?>