<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Bill\{
    CreateBillUseCase,
    GetAllBillsUseCase,
    GetBillByTaskIdUseCase,
    GetBillByIdUseCase,
    UpdateBillUseCase,
    DeleteBillUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateBillRequest;
use Interface\Http\DTO\Request\UpdateBillRequest;
use Interface\Http\DTO\Response\BillResponse;

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

    /**
     * Get all bills
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $bills = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $billResponses = array_map(
            fn($bill) => BillResponse::fromEntity($bill),
            $bills
        );
        
        return ApiResponse::collection($billResponses, 'bills');
    }

    /**
     * Get a single bill by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $bill = $this->getById->execute($id);
        
        if (!$bill) {
            return ApiResponse::notFound('Bill not found');
        }
        
        $billResponse = BillResponse::fromEntity($bill);
        return ApiResponse::success($billResponse);
    }

    /**
     * Get bill by task ID
     * 
     * @param Request $request
     * @return Response
     */
    public function getByTaskId(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $bill = $this->getByTaskId->execute($taskId);
        
        if (!$bill) {
            return ApiResponse::notFound('Bill not found for this task');
        }
        
        $billResponse = BillResponse::fromEntity($bill);
        return ApiResponse::success($billResponse);
    }

    /**
     * Create a new bill
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateBillRequest) {
            // Fallback: create DTO from request body
            $dto = CreateBillRequest::fromArray($request->body);
        }
        
        $bill = $this->create->execute($dto->toArray());
        $billResponse = BillResponse::fromEntity($bill);
        
        return ApiResponse::success($billResponse, 201);
    }

    /**
     * Update an existing bill
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateBillRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateBillRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $bill = $this->update->execute($id, $data);
        
        if (!$bill) {
            return ApiResponse::notFound('Bill not found');
        }
        
        $billResponse = BillResponse::fromEntity($bill);
        return ApiResponse::success($billResponse);
    }

    /**
     * Delete a bill
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if bill exists before deleting
        $bill = $this->getById->execute($id);
        
        if (!$bill) {
            return ApiResponse::notFound('Bill not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Bill deleted successfully'
        ]);
    }
}
