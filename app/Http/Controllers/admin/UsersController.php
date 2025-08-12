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

class usersController extends AdminController{

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
        parent::$data['active_menu'] = 'users';
        $this->path = 'users';
    }

    //////////////////////////////////////////////
    public function getIndex() {

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getAdd() {
        $roles = new Role();
        parent::$data['roles'] = $roles->getAllRolesActive();
        parent::$data['info'] = new User();
        parent::$data['users'] = User::where('status', 1)->get();
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    //////////////////////////////////////////////
    public function getList(Request $request) {
        $name = $request->get('name') ?? '';
        $emp_id = Auth::guard('admin')->user()->emp_type != 1 ? 0 :  Auth::guard('admin')->user()->id;
        $role = new User();
        $info = $role->getUsers();
        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        // $datatable->editColumn('status', function ($row) {
        //     $data['id'] = $row->id;
        //     $data['status'] = $row->status;
        //     return view('admin.' . $this->path . '.parts.status', $data)->render();
        // });
        $datatable->editColumn('updated_at', function ($row) {
            return '<div class="badge badge-info fw-bold">' . $row->updated_at->diffForHumans() . '</div>';
        });
        $datatable->editColumn('created_by', function ($row) {
            $x = $row->admin ? $row->creator->username : '--';
            return '<div class="badge badge-warning fw-bold">' . $x . '</div>';
        });
        $datatable->editColumn('role_id', function ($row) {
            $x = $row->role ? $row->role_id : '--';
            return '<div class="badge badge-warning fw-bold">' . $x . '</div>';
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
            return $data['name'];
        });
        $datatable->editColumn('username', function ($row) {
            $data['x'] = 3;
            $data['name'] = $row->username ?? '---';
            return $data['name'];
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
    public function postAdd(Request $request) {
        $save_data = $request->all();
        $save_data['status'] = (int) $request->get('status');
        $save_data['created_by'] = Auth::guard('admin')->user()->id;
        $save_data['password_confirmation'] = $request->get('password_confirmation');
        $role = $save_data['role_id'] = (int) $request->get('role_id');

        $validator = Validator::make($save_data, [
                    'username' => 'required|unique:users,username',
                    'mobile' => 'required|unique:users,mobile|min:10',
                    'id_no' => 'required|unique:users,id_no|min:9',
                    'full_name_ar' => 'required',
                    'full_name_en' => 'required',
                    'supervisor_id' => 'required_if:emp_type,0',
                    'email' => 'required|email|unique:users',
                    'role_id' => 'required|numeric',
                    'password' => 'required|between:6,16|confirmed',
                    'password_confirmation' => 'required|between:6,16',
                    'status' => 'required|numeric|in:0,1',
                    'street_id' => 'required',
                    'gove_id' => 'required',
                    'city_id' => 'required',
        ]);

        //////////////////////////////////////////////////////////
        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            $firstError = $validator->errors()->first();
            return redirect(route($this->path . '.add'))->withInput()->with('error', $firstError);
        } else {
            $add = User::create($save_data);
            if ($add) {
                $roles = new Role();
                $new_info = $roles->getRole($role);
                if ($new_info) {
                    $new_role_name = $new_info->name;
                    $add->syncRoles([$new_role_name]);
                }
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
        $user = new User();
        $roles = new Role();

        $info = $user->getAdmin($id);
        if ($info) {
            parent::$data['users'] = User::where('status', 1)->get();
            parent::$data['roles'] = $roles->getAllRolesActive();
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

        $user = new User();
        $info = $user->getAdmin($id);
        if ($info) {
            $save_data = $request->all();
            $save_data['status'] = (int) $request->get('status');
            $role = $save_data['role_id'] = (int) $request->get('role_id');
            $save_data['created_by'] = Auth::guard('admin')->user()->id;
            $save_data['password_confirmation'] = $request->get('password_confirmation');

            $validator = Validator::make($save_data, [
                        "username" => "required|unique:users,username," . $id,
                        "mobile" => "required|min:10|unique:users,mobile," . $id,
                        "id_no" => "required|unique:users,id_no,$id|min:9",
                        "full_name_ar" => "required",
                        "full_name_en" => "required",
                        "role_id" => "required|numeric",
                        "status" => "required|numeric|in:0,1",
                        "street_id" => "required",
                        "gove_id" => "required",
                        "city_id" => "required",
                        "supervisor_id" => "required_if:emp_type,0",
                        "email" => "required|email|unique:users,email,$id",
            ]);

            if ($validator->fails()) {
                $request->session()->flash('danger', $validator->messages());
                return redirect(route($this->path . '.edit', ['id' => $encrypted_id]))->withInput();
            } else {
                $update = $info->update($save_data);
                if ($update) {
                    $roles = new Role();
                    $new_info = $roles->getRole($role);
                    if ($new_info) {
                        $new_role_name = $new_info->name;
                        $info->syncRoles([$new_role_name]);
                    }
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
    public function postStatus(Request $request) {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }
        $users = new User();
        $info = $users->getAdmin($id);
        if ($info) {
            $status = $info->status;
            if ($status == 0) {
                $delete = $users->updateStatus($id, 1);
                if ($delete) {
                    return response()->json(['status' => 'success', 'message' => self::ACTIVATION_SUCCESS, 'type' => 'yes']);
                } else {
                    return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
                }
            } else {
                $delete = $users->updateStatus($id, 0);
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
    public function getPassword(Request $request, $id) {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
        // if ($id == 1) {
        //     $request->session()->flash('danger', self::NOT_FOUND);
        //     return redirect(route($this->path . '.view'));
        // }
        /////////////////////////////
        $user = new User();
        $info = $user->getAdmin($id);
        if ($info) {
            parent::$data['info'] = $info;
            return view('admin.users.password', parent::$data);
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
    }

    //////////////////////////////////////////////
    public function postPassword(Request $request, $id) {
        try {
            $encrypted_id = $id;
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
        // if ($id == 1) {
        //     $request->session()->flash('danger', self::NOT_FOUND);
        //     return redirect(route($this->path . '.view'));
        // }

        $user = new User();
        $info = $user->getAdmin($id);
        if ($info) {
            $password = $request->get('password');
            $password_confirmation = $request->get('password_confirmation');

            $validator = Validator::make([
                        'password' => $password,
                        'password_confirmation' => $password_confirmation
                            ], [
                        'password' => 'required|between:6,16|confirmed',
                        'password_confirmation' => 'required|between:6,16'
            ]);

            if ($validator->fails()) {
                $request->session()->flash('danger', $validator->messages());
                return redirect(route($this->path . '.password', ['id' => $encrypted_id]))->withInput();
            } else {
                $update = $user->updatePassword($id, Hash::make($password));
                if ($update) {
                    $request->session()->flash('success', self::PASSWORD_SUCCESS);
                    return redirect(route($this->path . '.view'));
                } else {
                    $request->session()->flash('danger', self::EXECUTION_ERROR);
                    return redirect(route($this->path . '.password', ['id' => $encrypted_id]))->withInput();
                }
            }
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
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
        $users = new User();
        $info = $users->getAdmin($id);

        if ($info) {
            $delete = $info->deleteAdmin($info);
            if ($delete) {

                return response()->json(['status' => 'success', 'message' => self::DELETE_SUCCESS]);
            } else {
                return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
    }

    // public function getGovernoratesAndStreetsByCity(Request $request) {

    //     $target = $request->input('target');
    //     if ($target == 1) {
    //         $cityId = $request->input('city_id');
    //         $lang = app()->getLocale();

    //         $governorates = Governorates::where('city_id', $cityId)
    //                 ->select('id', "name_{$lang} as name")
    //                 ->get();

    //         return response()->json([
    //                     'governorates' => $governorates,
    //         ]);
    //     } else {
    //         $cityId = $request->input('cityId');
    //         $gove_id = $request->input('gove_id');
    //         $lang = app()->getLocale();

    //         $streets = Street::where('city_id', $cityId)->where('gove_id', $gove_id)
    //                 ->select('id', "name_{$lang} as name")
    //                 ->get();

    //         return response()->json([
    //                     'streets' => $streets,
    //         ]);
    //     }
    // }
}
