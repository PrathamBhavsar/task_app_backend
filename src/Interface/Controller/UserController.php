<?php

namespace Interface\Controller;

use Application\UseCase\User\{
    CreateUserUseCase,
    GetAllUsersUseCase,
    GetUserByIdUseCase,
    UpdateUserUseCase,
    DeleteUserUseCase
};
use Interface\Http\JsonResponse;

class UserController
{
    public function __construct(
        private GetAllUsersUseCase $getAll,
        private GetUserByIdUseCase $getById,
        private CreateUserUseCase $create,
        private UpdateUserUseCase $update,
        private DeleteUserUseCase $delete
    ) {}

    public function index()
    {
        $users = $this->getAll->execute();
        return JsonResponse::ok($users);
    }

    public function show(int $id)
    {
        $user = $this->getById->execute($id);
        return $user
            ? JsonResponse::ok($user)
            : JsonResponse::error("User not found", 404);
    }

    public function store(array $data)
    {
        $user = $this->create->execute($data);
        return JsonResponse::ok($user);
    }

    public function update(int $id, array $data)
    {
        $user = $this->update->execute($id, $data);
        return $user
            ? JsonResponse::ok($user)
            : JsonResponse::error("User not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
