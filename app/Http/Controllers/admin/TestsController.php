<?php

namespace App\Http\Controllers\Admin;

use App\Models\Test;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\TestRequest;

class TestsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'tests';
        $this->path = 'tests';
    }

    public function getIndex()
    {
        parent::$data['users'] = User::all();

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $records = Test::get();

        return Datatables::of($records)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        parent::$data['users'] = User::all();

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(TestRequest $request)
    {
        $data = $request->validated();
        if(isset($data['status'])) {
            $data['status'] = $request->input('status') == '1' ? 1 : 0;
        }

        $record = Test::create($data);

        if ($record) {
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
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Test::findOrFail($decryptedId);
        parent::$data['info'] = $record;
        parent::$data['users'] = User::all();

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(TestRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Test::findOrFail($decryptedId);

        $validatedData = $request->validated();
        if(isset($validatedData['status'])) {
            $validatedData['status'] = $request->input('status') == '1' ? 1 : 0;
        }

        $update = $record->update($validatedData);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', __('app.update_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.edit', ['id' => $id]))->withInput();
        }
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $record = Test::findOrFail($decryptedId);

        $newStatus = $record->status == 1 ? 0 : 1;
        $update = $record->update(['status' => $newStatus]);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status' => 'success',
                'message' => $newStatus ? __('app.activation_success') : __('app.disable_success'),
                'type' => $newStatus ? 'yes' : 'no'
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
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
            $record = Test::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
        if ($record->delete()) {
            Cache::forget('spatie.permission.cache');
            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }
}