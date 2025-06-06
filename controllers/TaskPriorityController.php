<?php
require_once __DIR__ . '/../models/TaskPriority.php';

class TaskPriorityController {
    private $taskPriorityModel;

    public function __construct($db) {
        $this->taskPriorityModel = new TaskPriority($db);
    }

    public function index() {
        $users = $this->taskPriorityModel->getAll();
        sendJson($users);
    }

    public function show($id) {
        $user = $this->taskPriorityModel->getById($id);
        $user ? sendJson($user) : sendError("Task Priority not found", 404);
    }

public function store($data) {
    if (empty($data['name']) || empty($data['color'])) {
        sendError("Name and color are required", 400);
    }

    $createdPriority = $this->taskPriorityModel->create($data);

    if ($createdPriority) {
        sendJson(["priority" => $createdPriority]);
    } else {
        sendError("Task Priority creation failed", 400);
    }
}


public function update($id, $data) {
    if (empty($data['name']) || empty($data['color'])) {
        sendError("Name and color are required", 400);
    }

    $updatedPriority = $this->taskPriorityModel->update($id, $data);

    if ($updatedPriority) {
        sendJson(["priority" => $updatedPriority]);
    } else {
        sendError("Update failed", 400);
    }
}

public function delete($id) {
    $priority = $this->taskPriorityModel->getById($id);

    if (!$priority) {
        sendError("Task Priority not found", 404);
    }

    $success = $this->taskPriorityModel->delete($id);

    if ($success) {
        sendJson(["priority" => $priority]);
    } else {
        sendError("Delete failed", 400);
    }
}
}
?>
