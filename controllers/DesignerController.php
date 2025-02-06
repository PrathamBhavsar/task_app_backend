<?php
require_once 'models/Designer.php';

class DesignerController {
    private $designerModel;

    public function __construct() {
        $this->designerModel = new Designer();
    }

    public function getDesigners() {
        echo json_encode(["status" => "success", "data" => $this->designerModel->getAllDesigners()]);
    }

    public function createDesigner() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->designerModel->createDesigner($data)) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Designer created"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create Designer"]);
        }
    }

    public function updateDesigner() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->designerModel->updateDesigner($data)) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Designer updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update Designer"]);
        }
    }

    public function deleteDesigner() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->designerModel->deleteDesigner($data['id'])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Designer deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete Designer"]);
        }
    }
}
?>
