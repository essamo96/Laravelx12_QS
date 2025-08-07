<?php

namespace App\Http\Controllers\Admin;

use Route;
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

class PermissionsGroupController extends AdminController
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
        parent::$data['active_menu'] = 'permissions_group';
        $this->path = 'permissions_group';
    }
    //////////////////////////////////////////////
    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }
    public function getAdd()
    {
    $name = null;
        $roles = new PermissionsGroup();
        parent::$data['permissions'] = $roles->getAllPermissionGroupSearch($name);
        parent::$data['info'] = NULL;
        return view('admin.' . $this->path . '.add', parent::$data);
    }
    //////////////////////////////////////////////
    public function getList(Request $request)
    {
        $name = $request->get('name');
        $role = new PermissionsGroup();
        $info = $role->getPermissionGroupSearch($name);
        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', $data)->render();
        });
        $path = $this->path;

        $datatable->editColumn('parent_id', function ($row) {
            $record = PermissionsGroup::find($row->parent_id);
            if($record){

                return '<div class="badge badge-warning fw-bold">' . $record->name_ar . '</div>';          
            }else{
                return '<div class="badge badge-danger fw-bold">لايوجد</div>';          
               
            }
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
        $name_ar = $request->get('name_ar');
        $name_en = $request->get('name_en');
        $icon = $request->get('icon');
        $sort = (int)$request->get('sort');
        $status = (int)$request->get('status');
        $parent_id = (int)$request->get('parent_id');

        $validator = Validator::make([
            'name' => $name,
            'status' => $status,
            'name_ar' => $name_ar,
            'name_en' => $name_en,
            'icon' => $icon,
            'sort' => $sort,
            'parent_id' => $parent_id,
        ], [
            'name' => 'required',
            'name_ar' => 'required',
            'name_en' => 'required',
            // 'icon' => 'required',
            'status' => 'required|numeric|in:0,1',
            'sort' => 'required|numeric',
            'parent_id' => 'required|numeric',
        ]);
        //////////////////////////////////////////////////////////
        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            // $firstError = $validator->errors()->first();
            return redirect(route($this->path . '.add'))->withInput();
        } else {
            $user = new PermissionsGroup();
            $add = $user->addPermissionsGroup($name, $name_ar, $name_en, $icon, $sort, $status, $parent_id);
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
    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
        $user = new PermissionsGroup();
        $info = $user->getPermissionsGroup($id);
        if ($info) {
            parent::$data['permissions'] =  $user->getAllPermissionGroupSearch($name=null);
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

        $user = new PermissionsGroup();
        $info = $user->getPermissionsGroup($id);
        if ($info) {
            $name = $request->get('name');
            $name_ar = $request->get('name_ar');
            $name_en = $request->get('name_en');
            $icon = $request->get('icon');
            $sort = (int)$request->get('sort');
            $status = (int)$request->get('status');
            $parent_id = (int)$request->get('parent_id');

            $validator = Validator::make([
                'name' => $name,
                'status' => $status,
                'name_ar' => $name_ar,
                'name_en' => $name_en,
                'icon' => $icon,
                'sort' => $sort,
                'parent_id' => $parent_id
            ], [
                'name' => 'required',
                'name_ar' => 'required',
                'name_en' => 'required',
                // 'icon' => 'required',
                'status' => 'required|numeric|in:0,1',
                'sort' => 'required|numeric',
                'parent_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $request->session()->flash('danger', $validator->messages());
                return redirect(route($this->path . '.edit', ['id' => $encrypted_id]))->withInput();
            } else {
                $update = $user->updatePermissionsGroup($info, $name, $name_ar, $name_en, $icon, $sort, $status, $parent_id);
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
    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }
        $permissions_group = new PermissionsGroup();
        $info = $permissions_group->getPermissionsGroup($id);
        if ($info) {
            $status = $info->status;
            if ($status == 0) {
                $delete = $permissions_group->updateStatus($id, 1);
                if ($delete) {
                    return response()->json(['status' => 'success', 'message' => self::ACTIVATION_SUCCESS, 'type' => 'yes']);
                } else {
                    return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
                }
            } else {
                $delete = $permissions_group->updateStatus($id, 0);
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
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }
        $permissions_group = new PermissionsGroup();
        $info = $permissions_group->getPermissionsGroup($id);
        if ($info) {
            $delete = $permissions_group->deletePermissionsGroup($info);
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
