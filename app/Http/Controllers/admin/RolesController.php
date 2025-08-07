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

class RolesController extends AdminController {

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

    public function __construct() {
        parent::__construct();
        parent::$data['active_menu'] = 'roles';
        $this->path = 'roles';
    }

    //////////////////////////////////////////////
    public function getIndex() {
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request) {
        $name = $request->get('name');
        $role = new Role();
        $info = $role->getRoles($name);
        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', $data)->render();
        });
        $path = $this->path;
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', $data)->render();
        });
        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }

    //////////////////////////////////////////////
    public function getAdd(Request $request) {
        parent::$data['info'] = NULL;
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    //////////////////////////////////////////////
    public function postAdd(Request $request) {
        $name = $request->get('name');
        $is_user = $request->get('is_user');
        $status = $request->has('status') && $request->input('status') === '1' ? 1 : 0;
        $validator = Validator::make([
            'name' => $name,
            'is_user' => $is_user,
            'status' => $status
        ], [
            'name' => 'required',
            'status' => 'required|numeric|in:0,1',
            // 'is_user' => 'required|numeric|in:0,1'
        ]);

        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            return redirect(route($this->path . '.add'))->withInput();
        } else {
            $role = new Role();
            $add = $role->addRole($name, $status,$is_user);

            if ($add) {
                Cache::forget('spatie.permission.cache');
                $request->session()->flash('success', self::INSERT_SUCCESS_MESSAGE);
                return redirect(route($this->path . '.view'));
            } else {
                $request->session()->flash('danger', self::EXECUTION_ERROR);
                return redirect(route($this->path . '.add'))->withInput();
            }
        }
    }

    //////////////////////////////////////////////
    public function getEdit(Request $request, $id) {
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
    public function postEdit(Request $request, $id) {
        try {
            $encrypted_id = $id;
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
            $name = $request->get('name');
            $is_user = $request->get('is_user');
            $status = $request->has('status') && $request->input('status') === '1' ? 1 : 0;
            $validator = Validator::make([
                'name' => $name,
                'is_user' => $is_user,
                'status' => $status
            ], [
                'name' => 'required',
                'status' => 'required|numeric|in:0,1',
                // 'is_user' => 'required|numeric|in:0,1'
            ]);
            if ($validator->fails()) {
                $request->session()->flash('danger', $validator->messages());
                return redirect(route($this->path . '.edit', ['id' => $encrypted_id]))->withInput();
            } else {
                $update = $role->updateRole($info, $name, $status,$is_user);
                if ($update) {
                    Cache::forget('spatie.permission.cache');
                    $request->session()->flash('success', self::UPDATE_SUCCESS);
                    return redirect(route($this->path . '.view'));
                } else {
                    $request->session()->flash('danger', self::EXECUTION_ERROR);
                    return redirect(route($this->path . '.edit', ['id' => $encrypted_id]))->withInput();
                }
            }
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
    }

    //////////////////////////////////////////////
    public function getPermissions(Request $request, $id) {
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
    public function postPermissions(Request $request, $id) {
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
    public function postStatus(Request $request) {
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
    public function postDelete(Request $request) {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }

        if ($id == 1) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return response()->json(['status' => 'error', 'message' => 'Error, Data not found']);
        }
        $roles = new Role();
        $info = $roles->getRole($id);
        if ($info) {
            $delete = $roles->deleteRole($info);
            if ($delete) {
                Cache::forget('spatie.permission.cache');
                return response()->json(['status' => 'success', 'message' => self::DELETE_SUCCESS]);
            } else {
                return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
    }

}
