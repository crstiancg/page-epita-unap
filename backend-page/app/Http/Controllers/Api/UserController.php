<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRolesRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::with('roles')->get());
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('roles.permissions'));
    }

    public function assignRoles(AssignRolesRequest $request, User $user): JsonResponse
    {
        $user->syncRoles($request->validated('roles'));

        return response()->json($user->load('roles'));
    }
}
