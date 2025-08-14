<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\PermissionsGroup;
use App\Models\RoleHasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\RoleRequest; // استدعاء الـ Request

class RolesController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'roles';
        $this->path = 'roles';
    }

    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name');
        $role = new Role();
        $roles = $role->getRoles($name);

        return Datatables::of($roles)
            ->editColumn('status', function ($role) {
                $data['id'] = $role->id;
                $data['status'] = $role->status;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->addColumn('actions', function ($role) {
                $data['active_menu'] = $this->path;
                $data['id'] = $role->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    // هنا يتم استخدام RoleRequest بدلاً من Request
    public function postAdd(RoleRequest $request)
    {
        $data = $request->only('name', 'status', 'is_user');

        $data['status'] = $request->has('status') && $request->input('status') === '1' ? 1 : 0;
        $data['is_user'] = $request->has('is_user') && $request->input('is_user') === '1' ? 1 : 0;
        $data['guard_name'] = 'admin';

        $role = Role::create($data);

        if ($role) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', __('app.insert_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.add'))->withInput();
        }
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
        if ($id == 1) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $role = new Role();
        $info = $role->getRole($id);
        if ($info) {
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    // هنا يتم استخدام RoleRequest بدلاً من Request
    public function postEdit(RoleRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $role = Role::findOrFail($decryptedId);

        if ($role->id == 1) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $validatedData = $request->validated();
        $validatedData['status'] = $request->has('status') ? 1 : 0;
        $validatedData['is_user'] = $request->has('is_user') ? 1 : 0;

        $update = $role->update($validatedData);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', __('app.update_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.edit', ['id' => $id]))->withInput();
        }
    }

    public function getPermissions(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $roles = new Role();
        $info = $roles->getRole($id);
        if ($info) {
            parent::$data['btn_primary'] = 'btn-success';
            $permission_group = new PermissionsGroup();
            parent::$data['permission_group'] = $permission_group->getAllPermissionGroup();
            $role_has_permissions = new RoleHasPermissions();
            parent::$data['role_permissions'] = $role_has_permissions->getRoleHasPermissionsByRoleId($id);
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.permissions', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    public function postPermissions(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $permissions = $request->get('permissions');

        if (sizeof($permissions) > 0) {
            $role_has_permissions = new RoleHasPermissions();
            $role_has_permissions->deleteRoleHasPermissionsByRoleId($id);

            foreach ($permissions as $permission_id) {
                $role_has_permissions = new RoleHasPermissions();
                $add = $role_has_permissions->addRoleHasPermissions($permission_id, $id);
            }
            Cache::forget('spatie.permission.cache');

            $request->session()->flash('success', __('app.update_success'));
            return redirect(route($this->path . '.permissions', ['id' => Crypt::encrypt($id)]));
        } else {
            $role_has_permissions = new RoleHasPermissions();
            $role_has_permissions->deleteRoleHasPermissionsByRoleId($id);
            $request->session()->flash('success', __('app.update_success'));
            return redirect(route($this->path . '.permissions', ['id' => Crypt::encrypt($id)]));
        }
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $roles = new Role();
        $info = $roles->getRole($id);
        if ($info) {
            $status = $info->status;
            if ($status == 0) {
                $delete = $roles->updateStatus($id, 1);
                if ($delete) {
                    Cache::forget('spatie.permission.cache');
                    return response()->json(['status' => 'success', 'message' => __('app.activation_success'), 'type' => 'yes']);
                } else {
                    return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                }
            } else {
                $delete = $roles->updateStatus($id, 0);
                if ($delete) {
                    return response()->json(['status' => 'success', 'message' => __('app.disable_success'), 'type' => 'no']);
                } else {
                    return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                }
            }
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
        try {
            $role = Role::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
        if ($role->id == 1) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete the main administrator role.']);
        }
        if ($role->delete()) {
            Cache::forget('spatie.permission.cache');
            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }
}
