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

    private function serializeUser($user): array
    {
        return [
            'user_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'contact_no' => $user->getContactNo(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'user_type' => $user->getUserType(),
            'address' => $user->getAddress(),
            'profile_bg_color' => $user->getProfileBgColor(),

        ];
    }

    public function index()
    {
        $users = $this->getAll->execute();
        $data = array_map([$this, 'serializeUser'], $users);
        return JsonResponse::ok($data);
    }

    public function show(int $id)
    {
        $user = $this->getById->execute($id);
        return $user
            ? JsonResponse::ok($this->serializeUser($user))
            : JsonResponse::error("User not found", 404);
    }

    public function store(array $data)
    {
        $user = $this->create->execute($data);
        return JsonResponse::ok($this->serializeUser($user));
    }

    public function update(int $id, array $data)
    {
        $user = $this->update->execute($id, $data);
        return $user
            ? JsonResponse::ok($this->serializeUser($user))
            : JsonResponse::error("User not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
