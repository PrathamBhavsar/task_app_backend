<?php

namespace Interface\Controller;

use Application\UseCase\Quote\{
    CreateQuoteUseCase,
    GetAllQuotesUseCase,
    GetQuoteByTaskIdUseCase,
    GetQuoteByIdUseCase,
    UpdateQuoteUseCase,
    DeleteQuoteUseCase
};
use Interface\Http\JsonResponse;

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

    public function index()
    {
        $quotes = $this->getAll->execute();
        return JsonResponse::ok($quotes);
    }

    public function show(int $id)
    {
        $quote = $this->getById->execute($id);
        return $quote
            ? JsonResponse::ok($quote)
            : JsonResponse::error("Quote not found", 404);
    }

    public function getByTaskId(int $taskId)
    {
        $quote = $this->getByTaskId->execute($taskId);
        return JsonResponse::ok($quote);
    }

    public function store(array $data)
    {
        $quote = $this->create->execute($data);
        return JsonResponse::ok($quote);
    }

    public function update(int $id, array $data)
    {
        $quote = $this->update->execute($id, $data);
        return $quote
            ? JsonResponse::ok($quote)
            : JsonResponse::error("Quote not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
