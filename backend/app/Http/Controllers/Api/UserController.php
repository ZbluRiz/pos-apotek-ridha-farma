<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Users\ManageUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly ManageUsers $useCase) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return response()->json([
            'data' => UserResource::collection($this->useCase->list($request->integer('per_page', 10))),
        ]);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $user = $this->useCase->create($request->safe()->except('password_confirmation'));

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $user = $this->useCase->update($user, $request->safe()->except('password_confirmation'));

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $this->useCase->delete($request->user(), $user);

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }
}
