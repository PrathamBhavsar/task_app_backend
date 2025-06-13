<?php

class BaseController
{
    protected $model;
    protected $resourceName;

    public function __construct($model, $resourceName)
    {
        $this->model = $model;
        $this->resourceName = $resourceName;
    }

    public function index()
    {
        $resources = $this->model->getAll();

        $resourceKey = $this->resourceName . 's';

        sendJson([
            $resourceKey => $resources
        ]);
    }


    public function show($id)
    {
        $resource = $this->model->getById($id);
        $resource ? sendJson($resource) : sendError(ucfirst($this->resourceName) . " not found", 404);
    }

    public function store($data, $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                sendError("Field '$field' is required", 400);
            }
        }

        $created = $this->model->create($data);
        $created ? sendJson([$this->resourceName => $created]) : sendError(ucfirst($this->resourceName) . " creation failed", 400);
    }

    public function update($id, $data, $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                sendError("Field '$field' is required", 400);
            }
        }

        $existing = $this->model->getById($id);
        if (!$existing) {
            sendError(ucfirst($this->resourceName) . " with ID $id does not exist", 404);
        }

        $updated = $this->model->update($id, $data);
        $updated ? sendJson([$this->resourceName => $updated]) : sendError("Update failed", 400);
    }

    public function delete($id)
    {
        $existing = $this->model->getById($id);
        if (!$existing) {
            sendError(ucfirst($this->resourceName) . " not found", 404);
        }

        $success = $this->model->delete($id);
        $success ? sendJson([$this->resourceName => $existing]) : sendError("Delete failed", 400);
    }
}
