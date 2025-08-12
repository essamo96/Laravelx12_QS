<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\PermissionsGroup;
use App\Models\RoleHasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;

class RolesController extends AdminController
{

    const INSERT_SUCCESS_MESSAGE = "نجاح، تم الإضافة بتجاح";
    const UPDATE_SUCCESS = "نجاح، تم التعديل بنجاح";
    const DELETE_SUCCESS = "نجاح، تم الحذف بنجاح";
    const PASSWORD_SUCCESS = "نجاح، تم تغيير كلمة المرور بنجاح";
    const EXECUTION_ERROR = "عذراً، حدث خطأ أثناء تنفيذ العملية";
    const NOT_FOUND = "عذراً،لا يمكن العثور على البيانات";
    const ACTIVATION_SUCCESS = "نجاح، تم التفعيل بنجاح";
    const DISABLE_SUCCESS = "نجاح، تم التعطيل بنجاح";

    protected $path;

    //////////////////////////////////////////////

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'roles';
        $this->path = 'roles';
    }

    //////////////////////////////////////////////
    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }
    //////////////////////////////////////////////

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

    //////////////////////////////////////////////
    public function getAdd()
    {
        parent::$data['info'] = NULL;
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    //////////////////////////////////////////////
    public function postAdd(Request $request)
    {
        $data = $request->only('name', 'status', 'is_user');

        $data['status'] = $request->has('status') && $request->input('status') === '1' ? 1 : 0;
        $data['is_user'] = $request->has('is_user') && $request->input('is_user') === '1' ? 1 : 0;

        $data['guard_name'] = 'admin';

        $validator = Validator::make($data, [
            'name' => 'required|unique:roles,name',
            'status' => 'in:0,1',
            'is_user' => 'in:0,1',
        ]);

        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            return redirect(route($this->path . '.add'))->withInput();
        }

        // استخدام طريقة create() لإنشاء سجل جديد
        $role = Role::create($data);

        if ($role) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', self::INSERT_SUCCESS_MESSAGE);
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', self::EXECUTION_ERROR);
            return redirect(route($this->path . '.add'))->withInput();
        }
    }

    //////////////////////////////////////////////
    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
        if ($id == 1) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }

        $role = new Role();
        $info = $role->getRole($id);
        if ($info) {
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
    }
    //////////////////////////////////////////////

    public function postEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }

        $role = Role::findOrFail($decryptedId);

        if ($role->id == 1) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'is_user' => 'nullable|numeric|in:0,1',
            'status' => 'nullable|numeric|in:0,1',
        ]);

        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            return redirect(route($this->path . '.edit', ['id' => $id]))->withInput();
        }

        $validatedData = $validator->validated();

        $validatedData['status'] = $request->has('status') ? 1 : 0;
        $validatedData['is_user'] = $request->has('is_user') ? 1 : 0;

        $update = $role->update($validatedData);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', self::UPDATE_SUCCESS);
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', self::EXECUTION_ERROR);
            return redirect(route($this->path . '.edit', ['id' => $id]))->withInput();
        }
    }

    //////////////////////////////////////////////
    public function getPermissions(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
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
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
    }

    //////////////////////////////////////////////
    public function postPermissions(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
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

            $request->session()->flash('success', self::UPDATE_SUCCESS);
            return redirect(route($this->path . '.permissions', ['id' => Crypt::encrypt($id)]));
        } else {
            $role_has_permissions = new RoleHasPermissions();
            $role_has_permissions->deleteRoleHasPermissionsByRoleId($id);
            $request->session()->flash('success', self::UPDATE_SUCCESS);
            return redirect(route($this->path . '.permissions', ['id' => Crypt::encrypt($id)]));
        }
    }

    //////////////////////////////////////////////
    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }



        $roles = new Role();
        $info = $roles->getRole($id);
        if ($info) {
            $status = $info->status;
            if ($status == 0) {
                $delete = $roles->updateStatus($id, 1);
                if ($delete) {
                    Cache::forget('spatie.permission.cache');
                    return response()->json(['status' => 'success', 'message' => self::ACTIVATION_SUCCESS, 'type' => 'yes']);
                } else {
                    return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
                }
            } else {
                $delete = $roles->updateStatus($id, 0);
                if ($delete) {
                    return response()->json(['status' => 'success', 'message' => self::DISABLE_SUCCESS, 'type' => 'no']);
                } else {
                    return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
                }
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
    }
    //////////////////////////////////////////////

    public function postDelete(Request $request)
    {
        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error, invalid ID format.']);
        }
        try {
            $role = Role::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
        if ($role->id == 1) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete the main administrator role.']);
        }
        if ($role->delete()) {
            Cache::forget('spatie.permission.cache');
            return response()->json(['status' => 'success', 'message' => self::DELETE_SUCCESS]);
        } else {
            return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
        }
    }
}
