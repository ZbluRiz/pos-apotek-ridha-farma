<?php

namespace App\Application\UseCases\Users;

use App\Domain\Contracts\UserRepository;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ManageUsers
{
    public function __construct(private readonly UserRepository $users) {}

    public function list(int $perPage): LengthAwarePaginator
    {
        return $this->users->paginate(min(max($perPage, 1), 100));
    }

    public function create(array $data): User
    {
        return $this->users->create($data);
    }

    public function update(User $user, array $data): User
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $this->users->update($user, $data);
    }

    public function delete(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'user' => 'User aktif tidak dapat menghapus akunnya sendiri.',
            ]);
        }

        $this->users->delete($target);
    }
}
