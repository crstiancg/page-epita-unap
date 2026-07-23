<?php

use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function (): void {
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    });

    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.ver');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.ver');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.crear');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.editar');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.eliminar');

    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permisos.ver');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->middleware('permission:permisos.ver');
    Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:permisos.crear');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permisos.editar');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permisos.eliminar');

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:usuarios.ver');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:usuarios.ver');
    Route::put('/users/{user}/roles', [UserController::class, 'assignRoles'])->middleware('permission:usuarios.editar');
});
