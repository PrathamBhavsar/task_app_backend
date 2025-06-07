<?php
require_once __DIR__ . '/../models/Designer.php';

class DesignerController
{
    private $designerModel;

    public function __construct($db)
    {
        $this->designerModel = new Designer($db);
    }

    public function index()
    {
        $designers = $this->designerModel->getAll();
        sendJson($designers);
    }

    public function show($id)
    {
        $designer = $this->designerModel->getById($id);
        $designer ? sendJson($designer) : sendError("Designer not found", 404);
    }

    public function store($data)
    {
        if (empty($data['name']) || empty($data['contact_no']) || empty($data['address']) || empty($data['firm_name']) || empty($data['profile_bg_color'])) {
            sendError("Name, contact_no, firm_name, profile_bg_color and address are required", 400);
        }

        $createdDesigner = $this->designerModel->create($data);

        if ($createdDesigner) {
            sendJson(["designer" => $createdDesigner]);
        } else {
            sendError("Designer creation failed", 400);
        }
    }


public function update($id, $data)
{
    if (empty($data['name']) || empty($data['contact_no']) || empty($data['address']) || empty($data['firm_name']) || empty($data['profile_bg_color'])) {
        sendError("Name, contact_no, firm_name, profile_bg_color and address are required", 400);
    }

    // Check if the designer exists
    $existingDesigner = $this->designerModel->getById($id);
    if (!$existingDesigner) {
        sendError("Designer with ID $id does not exist", 404);
    }

    // Proceed with update
    $updatedDesigner = $this->designerModel->update($id, $data);

    if ($updatedDesigner) {
        sendJson(["designer" => $updatedDesigner]);
    } else {
        sendError("Update failed", 400);
    }
}


    public function delete($id)
    {
        $designer = $this->designerModel->getById($id);

        if (!$designer) {
            sendError("Designer not found", 404);
        }

        $success = $this->designerModel->delete($id);

        if ($success) {
            sendJson(["designer" => $designer]);
        } else {
            sendError("Delete failed", 400);
        }
    }
}
?>