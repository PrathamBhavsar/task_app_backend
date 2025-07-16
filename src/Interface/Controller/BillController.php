<?php

namespace Interface\Controller;

use Application\UseCase\Bill\{
    CreateBillUseCase,
    GetAllBillsUseCase,
    GetBillByTaskIdUseCase,
    GetBillByIdUseCase,
    UpdateBillUseCase,
    DeleteBillUseCase
};
use Interface\Http\JsonResponse;

class BillController
{
    public function __construct(
        private GetAllBillsUseCase $getAll,
        private GetBillByTaskIdUseCase $getByTaskId,
        private GetBillByIdUseCase $getById,
        private CreateBillUseCase $create,
        private UpdateBillUseCase $update,
        private DeleteBillUseCase $delete
    ) {}

    public function index()
    {
        $bills = $this->getAll->execute();
        return JsonResponse::ok($bills);
    }

    public function show(int $id)
    {
        $bill = $this->getById->execute($id);
        return $bill
            ? JsonResponse::ok($bill)
            : JsonResponse::error("Bill not found", 404);
    }

    public function getByTaskId(int $taskId)
    {
        $bill = $this->getByTaskId->execute($taskId);
        return JsonResponse::ok($bill);
    }

    public function store(array $data)
    {
        $bill = $this->create->execute($data);
        return JsonResponse::ok($bill);
    }

    public function update(int $id, array $data)
    {
        $bill = $this->update->execute($id, $data);
        return $bill
            ? JsonResponse::ok($bill)
            : JsonResponse::error("Bill not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
