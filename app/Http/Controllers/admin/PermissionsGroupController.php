<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\PermissionsGroup;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
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
        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->editColumn('parent_id', function ($row) {
                $record = PermissionsGroup::find($row->parent_id);
                if ($record) {
                    return '<div class="badge badge-warning fw-bold">' . $record->name_ar . '</div>';
                } else {
                    return '<div class="badge badge-danger fw-bold">لايوجد</div>';
                }
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'parent_id', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }
    //////////////////////////////////////////////

    public function postAdd(Request $request)
    {
        $data['status'] = $request->has('status') && $request->input('status') === '1' ? 1 : 0;

        $validatedData = $request->validate([
            'name' => 'required|string|unique:permissions_group,name',
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'color' => [
                'required',
                'string',
                Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'])
            ],
            'icon' => 'nullable|string',
            'sort' => 'required|numeric',
            'status' => 'numeric|in:0,1',
            'parent_id' => 'required|numeric'
        ]);

        $validatedData['guard_name'] = 'web';

        $permissionsGroup = PermissionsGroup::create($validatedData);

        if ($permissionsGroup) {
            Cache::forget('spatie.permission.cache');
            return redirect(route($this->path . '.view'))->with('success', self::INSERT_SUCCESS_MESSAGE);
        } else {
            return redirect(route($this->path . '.add'))->withInput()->with('danger', self::EXECUTION_ERROR);
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
            parent::$data['permissions'] =  $user->getAllPermissionGroupSearch($name = null);
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

        $permissionsGroup = PermissionsGroup::findOrFail($decryptedId);
        $data['status'] = $request->has('status') && $request->input('status') === '1' ? 1 : 0;

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions_group,name,' . $permissionsGroup->id,
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'color' => 'required|string|alpha',
            'icon' => 'nullable|string',
            'sort' => 'required|numeric',
            'status' => 'numeric|in:0,1',
            'parent_id' => 'required|numeric',
        ]);

        $update = $permissionsGroup->update($validatedData);

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
