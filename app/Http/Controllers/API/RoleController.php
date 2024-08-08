<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Role::class);


        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        $request->validate([
            'title' => 'required|unique:roles,title|max:255',
        ]);

        $role = Role::create($request->only('title'));

        return response()->json($role, 201);
    }

    public function show($id)
    {
        $this->authorize('view', Role::class);
        $role = Role::find($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Role::class);

        $request->validate([
            'title' => 'required|max:255|unique:roles,title',
        ]);
        $role = Role::find($id);
        $role->update($request->only('title'));

        return response()->json($role);
    }

    public function destroy($id)
    {
        $this->authorize('delete', Role::class);

        $role = Role::find($id);
        $role->delete();

        return response()->json(null, 204);
    }
}
