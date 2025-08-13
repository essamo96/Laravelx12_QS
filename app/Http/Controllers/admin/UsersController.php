<?php

namespace App\Http\Controllers\Admin;

use Route;
use App\Models\User;
use App\Models\Cities;
use App\Models\Governorates;
use App\Models\Role;
use App\Models\Street;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;

class usersController extends AdminController
{
    protected $path;

    //////////////////////////////////////////////

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'users';
        $this->path = 'users';
    }
    protected function saveUser(Request $request, $id = null)
    {
        $isUpdate = $id !== null;

        // القواعد المشتركة
        $rules = [
            'name'       => 'required|string|max:255',
            'username'   => ['required', 'string', 'max:255', 'unique:users,username' . ($isUpdate ? ",$id" : '')],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email' . ($isUpdate ? ",$id" : '')],
            'role_id'    => 'required|numeric|exists:roles,id',
            'status'     => 'nullable|in:0,1',
        ];

        // كلمة المرور إلزامية إذا كان إضافة
        if ($isUpdate) {
            $rules['password'] = 'nullable|between:6,16|confirmed';
        } else {
            $rules['password'] = 'required|between:6,16|confirmed';
        }

        $request->validate($rules);

        // إذا تعديل نجيب المستخدم، إذا إضافة ننشئ جديد
        $user = $isUpdate ? User::find($id) : new User();

        if (!$user) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        // تعبئة البيانات
        $user->name       = $request->name;
        $user->username   = $request->username;
        $user->email      = $request->email;
        $user->role_id    = $request->role_id;
        $user->status     = $request->status ? 1 : 0;
        $user->created_by = Auth::guard('admin')->id();

        // كلمة المرور
        if (!$isUpdate || $request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($user->save()) {
            // تحديث أو تعيين الرتبة
            $role = Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([$role->name]);
            }

            Cache::forget('spatie.permission.cache');

            $message = $isUpdate ? __('app.update_success') : __('app.insert_success');

            return redirect()
                ->route($this->path . '.view')
                ->with('success', $message);
        }

        return back()
            ->withInput()
            ->with('danger', __('app.execution_error'));
    }

    //////////////////////////////////////////////
    public function getIndex()
    {

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getAdd()
    {
        $roles = new Role();
        parent::$data['roles'] = $roles->getAllRolesActive();
        parent::$data['info'] = new User();
        parent::$data['users'] = User::where('status', 1)->get();
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    //////////////////////////////////////////////
    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $emp_id = Auth::guard('admin')->user()->emp_type != 1 ? 0 :  Auth::guard('admin')->user()->id;
        $role = new User();
        $info = $role->getSearchUsers($name);
        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', $data)->render();
        });
        $datatable->editColumn('updated_at', function ($row) {
            return '<div class="badge badge-info fw-bold">' . $row->updated_at->diffForHumans() . '</div>';
        });
        $datatable->editColumn('created_by', function ($row) {
            $x = $row->admin ? $row->creator->username : __('app.system');

            return '<div class="badge badge-warning fw-bold">' . $x . '</div>';
        });
        $datatable->editColumn('role_id', function ($row) {
            $x = $row->role ? $row->role->name : '--';
            $countpermissions = $row->role ? $row->role->permissions->count() : 0;
            return '<div class="badge badge-warning fw-bold">' . $x . ' (' . $countpermissions . ')</div>';
        });
        $path = $this->path;
        // $datatable->editColumn('permission', function ($row) {
        //     return '<a href="' . route('roles.permissions', ['id' => Crypt::encrypt($row->id)]) . '" class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success btn-sm">الصلاحيات</a>';
        // });
        // $datatable->editColumn('name', function ($row) {
        //     return ' <div class="symbol symbol-circle symbol-50px overflow-hidden me-3"><a href="'.url('assets/images/blank.png').'"><div class="symbol-label fs-3 bg-light-danger text-danger">M</div></a></div>
        //     <div class="d-flex flex-column"><a class="text-gray-800 text-hover-primary mb-1">'. $row->name . '</a><span> ' . $row->email . '  </span></div>';
        // });
        $datatable->editColumn('name', function ($row) {
            $data['x'] = 3;
            $data['name'] = $row->name;
            return '<a href="javascript:void(0)" class="btn btn-light-primary mt-0 fs-5 btn-xs">' . $data['name'] . '</a>';
        });
        $datatable->editColumn('username', function ($row) {
            $data['x'] = 3;
            $data['name'] = $row->username ?? '---';
            return '<a href="javascript:void(0)" class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary fs-5 btn-xs">' . $data['name'] . '</a>';
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
        return $this->saveUser($request);
    }

    //////////////////////////////////////////////
    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $user = new User();
        $roles = new Role();

        $info = $user->getUser($id);
        if ($info) {
            parent::$data['users'] = User::where('status', 1)->get();
            parent::$data['roles'] = $roles->getAllRolesActive();
            parent::$data['info'] = $info;

            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }


    //////////////////////////////////////////////
    public function postEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        return $this->saveUser($request, $id);
    }


    //////////////////////////////////////////////
    public function postStatus(Request $request)
    {
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => __('app.execution_error') // أو رسالة مخصصة لفشل فك التشفير
            ]);
        }

        $users = new User();
        $info = $users->getUser($id);

        if (!$info) {
            return response()->json([
                'status'  => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $newStatus = $info->status == 0 ? 1 : 0;
        $update    = $users->updateStatus($id, $newStatus);

        if ($update) {
            return response()->json([
                'status'  => 'success',
                'message' => $newStatus == 1
                    ? __('app.activation_success')
                    : __('app.disable_success'),
                'type'    => $newStatus == 1 ? 'yes' : 'no'
            ]);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }


    //////////////////////////////////////////////
    public function getPassword(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $user = new User();
        $info = $user->getUser($id);

        if (!$info) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        parent::$data['info'] = $info;
        return view('admin.'.$this->path.'.password', parent::$data);
    }

    //////////////////////////////////////////////
    public function postPassword(Request $request, $id)
    {
        try {
            $encrypted_id = $id;
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
        $user = new User();
        $info = $user->getUser($id);

        if (!$info) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
        $validator = Validator::make($request->all(), [
            'password' => 'required|between:6,16|confirmed',
            'password_confirmation' => 'required|between:6,16'
        ]);
        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            return redirect(route($this->path . '.password', ['id' => $encrypted_id]))->withInput();
        }

        $update = $user->updatePassword($id, Hash::make($request->get('password')));

        if ($update) {
            $request->session()->flash('success', __('app.password_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.password', ['id' => $encrypted_id]))->withInput();
        }
    }

    //////////////////////////////////////////////
    public function postDelete(Request $request)
    {
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }

        $users = new User();
        $info = $users->getUser($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $delete = $info->deleteAdmin($info);

        if ($delete) {
            return response()->json([
                'status' => 'success',
                'message' => __('app.delete_success')
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }
}
