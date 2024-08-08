<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    protected $emailVerificationService;

    public function __construct(AuthService $service, EmailVerificationService $emailVerificationService)
    {
        $this->service = $service;
        $this->emailVerificationService = $emailVerificationService;
    }

    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    public function store(RegisterRequest $request)
    {
        $user = $this->service->register($request->validated());

        $this->emailVerificationService->sendVerificationLink($user);

        return new UserResource($user);
    }

    public function update(RegisterRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());
        $this->emailVerificationService->sendVerificationLink($user);
        return new UserResource($user);
    }

    public function destroy($id)
    {
        $this->authorize('delete', \App\Models\Role::class);

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

}
