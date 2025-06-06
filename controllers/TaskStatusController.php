<?php
require_once __DIR__ . '/../models/TaskStatus.php';

class TaskStatusController {
    private $taskStatusModel;

    public function __construct($db) {
        $this->taskStatusModel = new TaskStatus($db);
    }

    public function index() {
        $users = $this->taskStatusModel->getAll();
        sendJson($users);
    }

    public function show($id) {
        $user = $this->taskStatusModel->getById($id);
        $user ? sendJson($user) : sendError("Task Status not found", 404);
    }

public function store($data) {
    if (empty($data['name']) || empty($data['color']) || empty($data['slug'])) {
        sendError("Name, color and slug are required", 400);
    }

    $createdStatus = $this->taskStatusModel->create($data);

    if ($createdStatus) {
        sendJson(["status" => $createdStatus]);
    } else {
        sendError("Task Status creation failed", 400);
    }
}


public function update($id, $data) {
       if (empty($data['name']) || empty($data['color']) || empty($data['slug'])) {
        sendError("Name, color and slug are required", 400);
    }

    $updatedStatus = $this->taskStatusModel->update($id, $data);

    if ($updatedStatus) {
        sendJson(["status" => $updatedStatus]);
    } else {
        sendError("Update failed", 400);
    }
}

public function delete($id) {
    $status = $this->taskStatusModel->getById($id);

    if (!$status) {
        sendError("Task Status not found", 404);
    }

    $success = $this->taskStatusModel->delete($id);

    if ($success) {
        sendJson(["status" => $status]);
    } else {
        sendError("Delete failed", 400);
    }
}
}
?>
