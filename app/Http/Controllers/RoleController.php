<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::with('permissions')->get();

            $data = $roles->map(function ($role) {
                $usuariosCount = DB::table(config('permission.table_names.model_has_roles'))
                    ->where('role_id', $role->id)
                    ->count();

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permisos' => $role->permissions->pluck('name'),
                    'usuarios' => $usuariosCount,
                    'created_at' => $role->created_at,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener roles'], 500);
        }
    }

    public function permissions()
    {
        try {
            $perms = Permission::all()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'guard_name' => $p->guard_name
                ];
            });
            return response()->json(['success' => true, 'data' => $perms]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener permisos'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:roles,name',
            'permisos' => 'nullable|array'
        ]);

        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => $request->get('guard_name', 'web')]);

            if ($request->filled('permisos')) {
                $role->syncPermissions($request->permisos);
            }

            return response()->json(['status' => 'success', 'data' => $role], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al crear rol', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:150|unique:roles,name,' . $id,
            'permisos' => 'nullable|array'
        ]);

        try {
            $role = Role::findOrFail($id);
            if ($request->filled('name')) {
                $role->name = $request->name;
                $role->save();
            }

            if ($request->has('permisos')) {
                $role->syncPermissions($request->permisos ?? []);
            }

            return response()->json(['status' => 'success', 'data' => $role]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar rol', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $usuariosCount = DB::table(config('permission.table_names.model_has_roles'))
                ->where('role_id', $role->id)
                ->count();

            if ($usuariosCount > 0) {
                return response()->json(['status' => 'error', 'message' => 'No se puede eliminar el rol porque tiene usuarios asignados'], 422);
            }

            $role->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar rol', 'error' => $e->getMessage()], 500);
        }
    }
}
