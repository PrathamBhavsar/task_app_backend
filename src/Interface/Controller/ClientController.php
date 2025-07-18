<?php

namespace Interface\Controller;

use Application\UseCase\Client\{
    CreateClientUseCase,
    GetAllClientsUseCase,
    GetClientByIdUseCase,
    UpdateClientUseCase,
    DeleteClientUseCase
};
use Interface\Http\JsonResponse;

class ClientController
{
    public function __construct(
        private GetAllClientsUseCase $getAll,
        private GetClientByIdUseCase $getById,
        private CreateClientUseCase $create,
        private UpdateClientUseCase $update,
        private DeleteClientUseCase $delete
    ) {}

    public function index()
    {
        $clients = $this->getAll->execute();
        return JsonResponse::list($clients, 'clients');
    }

    public function show(int $id)
    {
        $client = $this->getById->execute($id);
        return $client
            ? JsonResponse::ok($client)
            : JsonResponse::error("Client not found", 404);
    }

    public function store(array $data)
    {
        $client = $this->create->execute($data);
        return JsonResponse::ok($client);
    }

    public function update(int $id, array $data)
    {
        $client = $this->update->execute($id, $data);
        return $client
            ? JsonResponse::ok($client)
            : JsonResponse::error("Client not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
