<?php

namespace Interface\Controller;

use Application\UseCase\QuoteMeasurement\{
    CreateQuoteMeasurementUseCase,
    GetAllQuoteMeasurementsByQuoteIdUseCase,
    GetAllQuoteMeasurementsUseCase,
    GetQuoteMeasurementByIdUseCase,
    UpdateQuoteMeasurementUseCase,
    DeleteQuoteMeasurementUseCase
};
use Interface\Http\JsonResponse;

class QuoteMeasurementController
{
    public function __construct(
        private GetAllQuoteMeasurementsUseCase $getAll,
        private GetAllQuoteMeasurementsByQuoteIdUseCase $getAllByQuoteId,
        private GetQuoteMeasurementByIdUseCase $getById,
        private CreateQuoteMeasurementUseCase $create,
        private UpdateQuoteMeasurementUseCase $update,
        private DeleteQuoteMeasurementUseCase $delete
    ) {}

    public function index()
    {
        $quoteMeasurements = $this->getAll->execute();
        return JsonResponse::ok($quoteMeasurements);
    }

    public function getAllByQuoteId(int $quoteId)
    {
        $measurements = $this->getAllByQuoteId->execute($quoteId);
        return JsonResponse::ok($measurements);
    }

    public function show(int $id)
    {
        $quoteMeasurement = $this->getById->execute($id);
        return $quoteMeasurement
            ? JsonResponse::ok($quoteMeasurement)
            : JsonResponse::error("QuoteMeasurement not found", 404);
    }

    public function store(array $data)
    {
        $quoteMeasurement = $this->create->execute($data);
        return JsonResponse::ok($quoteMeasurement);
    }

    public function update(int $id, array $data)
    {
        $quoteMeasurement = $this->update->execute($id, $data);
        return $quoteMeasurement
            ? JsonResponse::ok($quoteMeasurement)
            : JsonResponse::error("QuoteMeasurement not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
