<?php

namespace App\Http\Controllers\Admin;

use Route;
use App\Models\Permissions;
use App\Models\PermissionsGroup;
use App\Models\Roles;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;

class permissionsController extends AdminController
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
        parent::$data['active_menu'] = 'permissions';
        $this->path = 'permissions';
    }
    //////////////////////////////////////////////
    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }
    public function getAdd()
    {
        $obj = new PermissionsGroup();
        parent::$data['permissions'] = $obj->getAllPermissionGroup();
        parent::$data['info'] = NULL;

        $guards = config('auth.guards');
        $guardNames = [];
        foreach ($guards as $guardName => $guardConfig) {
            $guardNames[] = $guardName;
        }
        parent::$data['guards'] = $guardNames;

        return view('admin.' . $this->path . '.add', parent::$data);
    }
    //////////////////////////////////////////////
    public function getList(Request $request)
    {
        $name = $request->get('name');
        $Permissions = new Permissions();
        $info = $Permissions->getAllPermissions($name);
        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        $path = $this->path;
        $datatable->editColumn('group_id', function ($row) {
            $data['x'] = 2;
            $data['name'] = $row->permission_group? $row->permission_group->{'name_' . trans('app.lang')}:'-';
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        });
        $datatable->editColumn('guard_name', function ($row) {
            $data['x'] = 1;
            $data['guard_name'] = $row->guard_name;
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        });
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', $data)->render();
        });
        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }
    //////////////////////////////////////////////
    public function postAdd(Request $request)
    {
        $name = $request->get('name');
        $guard_name = $request->get('guard_name');
        $group_id = (int)$request->get('group_id');

        $validator = Validator::make([
            'name' => $name,
            'guard_name' => $guard_name,
            'group_id' => $group_id,
        ], [
            'name' => 'required|unique:permissions',
            'guard_name' => 'required',
            'group_id' => 'required|numeric',
        ]);
        //////////////////////////////////////////////////////////
        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            $firstError = $validator->errors()->first();
            return redirect(route($this->path . '.add'))->withInput();
        } else {
            $user = new Permissions();
            $add = $user->addPermission($name, $group_id, $guard_name);
            if ($add) {
                Cache::forget('spatie.permission.cache');
                $request->session()->flash('success', self::INSERT_SUCCESS_MESSAGE);
                return redirect(route($this->path . '.add'));
            } else {
                $request->session()->flash('danger', self::EXECUTION_ERROR);
                return redirect(route($this->path . '.add'))->withInput();
            }
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
        $Permissions = new Permissions();
        $obj = new PermissionsGroup();
        $info = $Permissions->getPermissions($id);
        if ($info) {
            parent::$data['permissions'] = $obj->getAllPermissionGroup();
            $guards = config('auth.guards');
            $guardNames = [];
            foreach ($guards as $guardName => $guardConfig) {
                $guardNames[] = $guardName;
            }
            parent::$data['guards'] = $guardNames;

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
            $encrypted_id = $id;
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }

        $user = new Permissions();
        $info = $user->getPermissions($id);
        if ($info) {
            $name = $request->get('name');
            $guard_name = $request->get('guard_name');
            $group_id = (int)$request->get('group_id');

            $validator = Validator::make([
                'name' => $name,
                'guard_name' => $guard_name,
                'group_id' => $group_id,
            ], [
                'name' => 'required|unique:permissions',
                'guard_name' => 'required',
                'group_id' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $request->session()->flash('danger', $validator->messages());
                return redirect(route($this->path . '.edit', ['id' => $encrypted_id]))->withInput();
            } else {
                $update = $user->updatePermission($info, $name, $group_id, $guard_name);
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
    public function postDelete(Request $request)
    {
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }
        $permissions = new Permissions();
        $info = $permissions->getPermissions($id);
        if ($info) {
            $delete = $permissions->deletePermission($info);
            if ($delete) {
                return response()->json(['status' => 'success', 'message' => self::DELETE_SUCCESS]);
            } else {
                return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
    }
}
