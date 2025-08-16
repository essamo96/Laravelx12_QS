<?php

namespace App\Http\Controllers\Admin;

use Route;
use App\Models\Roles;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\PermissionsGroup;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\PermissionRequest;
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
        $Permission = new Permission();
        $info = $Permission->getAllPermissions($name);
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
    public function postAdd(PermissionRequest $request)
    {
        try {

            Permission::create($request->validated());
            Cache::forget('spatie.permission.cache');

            $request->session()->flash('success', "نجاح، تم الإضافة بنجاح");
        } catch (\Exception $e) {
            $request->session()->flash('danger', self::EXECUTION_ERROR);
            return redirect()->back()->withInput();
        }

        return redirect(route($this->path . '.view'));
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
        $Permission = new Permission();
        $obj = new PermissionsGroup();
        $info = $Permission->getPermissions($id);
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
    public function postEdit(PermissionRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
            $permission = Permission::findOrFail($decryptedId);

            // يتم التحقق من البيانات تلقائياً عبر PermissionRequest.
            $permissionData = $request->validated();

            // تحديث الصلاحية.
            $permission->update($permissionData);

            Cache::forget('spatie.permission.cache');

            $request->session()->flash('success', "نجاح، تم التعديل بنجاح");
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
        } catch (\Exception $e) {
            $request->session()->flash('danger', self::EXECUTION_ERROR);
        }

        return redirect(route($this->path . '.view'));
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
        $permissions = new Permission();
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
