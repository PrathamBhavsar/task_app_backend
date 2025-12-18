<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Quote\{
    CreateQuoteUseCase,
    GetAllQuotesUseCase,
    GetQuoteByTaskIdUseCase,
    GetQuoteByIdUseCase,
    UpdateQuoteUseCase,
    DeleteQuoteUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateQuoteRequest;
use Interface\Http\DTO\Request\UpdateQuoteRequest;
use Interface\Http\DTO\Response\QuoteResponse;

class QuoteController
{
    public function __construct(
        private GetAllQuotesUseCase $getAll,
        private GetQuoteByTaskIdUseCase $getByTaskId,
        private GetQuoteByIdUseCase $getById,
        private CreateQuoteUseCase $create,
        private UpdateQuoteUseCase $update,
        private DeleteQuoteUseCase $delete
    ) {}

    /**
     * Get all quotes
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $quotes = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $quoteResponses = array_map(
            fn($quote) => QuoteResponse::fromEntity($quote),
            $quotes
        );
        
        return ApiResponse::collection($quoteResponses, 'quotes');
    }

    /**
     * Get a single quote by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $quote = $this->getById->execute($id);
        
        if (!$quote) {
            return ApiResponse::notFound('Quote not found');
        }
        
        $quoteResponse = QuoteResponse::fromEntity($quote);
        return ApiResponse::success($quoteResponse);
    }

    /**
     * Get quote by task ID
     * 
     * @param Request $request
     * @return Response
     */
    public function getByTaskId(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $quote = $this->getByTaskId->execute($taskId);
        
        if (!$quote) {
            return ApiResponse::notFound('Quote not found for this task');
        }
        
        $quoteResponse = QuoteResponse::fromEntity($quote);
        return ApiResponse::success($quoteResponse);
    }

    /**
     * Create a new quote
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateQuoteRequest) {
            // Fallback: create DTO from request body
            $dto = CreateQuoteRequest::fromArray($request->body);
        }
        
        $quote = $this->create->execute($dto->toArray());
        $quoteResponse = QuoteResponse::fromEntity($quote);
        
        return ApiResponse::success($quoteResponse, 201);
    }

    /**
     * Update an existing quote
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateQuoteRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateQuoteRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $quote = $this->update->execute($id, $data);
        
        if (!$quote) {
            return ApiResponse::notFound('Quote not found');
        }
        
        $quoteResponse = QuoteResponse::fromEntity($quote);
        return ApiResponse::success($quoteResponse);
    }

    /**
     * Delete a quote
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if quote exists before deleting
        $quote = $this->getById->execute($id);
        
        if (!$quote) {
            return ApiResponse::notFound('Quote not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Quote deleted successfully'
        ]);
    }
}
