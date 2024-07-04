<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $with_responsibilities = $request->input('with_responsibilities', 0);

        $roleQuery = Role::query();

        // Get single data
        if ($id) {
            $role = $roleQuery->with('responsibilities')->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role found');
            }

            return ResponseFormatter::error('Role not found', 404);
        }

        // Get multiple data
        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if ($with_responsibilities) {
            $roles->with('responsibilities');   
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles found'
        );
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            //create role
            $role = role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if (!$role) {
                throw new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {

            $role = Role::find($id);

            if (!$role) {
                throw new Exception('role not found');
            }

            //create role
            $role->update([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            return ResponseFormatter::success($role, 'Role Updated Successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Check if role exists
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('role not found');
            }

            // Delete the role
            $role->delete();

            return ResponseFormatter::success($role, 'Role deleted successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
