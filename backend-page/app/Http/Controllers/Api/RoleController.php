<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Role::with('permissions')->get());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'api',
        ]);

        $role->syncPermissions($request->validated('permissions', []));

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->safe()->only('name'));

        if ($request->has('permissions')) {
            $role->syncPermissions($request->validated('permissions'));
        }

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json(status: 204);
    }
}
