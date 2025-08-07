<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Hash;
use Crypt;
use Session;
use Validator;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Settings;

//////////////////////////////////

class SettingsController extends AdminController {

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
        parent::$data['active_menu'] = 'settings';
        $this->path = 'settings';
    }

    //////////////////////////////////////////
    public function getIndex(Request $request) {
        $settings = new Settings();
        $info = $settings->getSetting(1);
        if ($info) {
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.view', parent::$data);
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route('settings.view'));
        }
    }

    ////////////////////////////////////////////////
    public function postIndex(Request $request) {
        $settings = new Settings();
        $save_data = $request->all();
        $info = $settings->getSetting(1);
        $save_data['close_status'] = (int) $request->get('close_status') ?? 0;

        if ($info) {
            $validator = Validator::make($save_data, [
                        'title' => 'required',
                        'description' => 'required',
                        'tags' => 'required',
                         'currency' => 'required',
                        'mobile' => 'required',
                        'address' => 'required',
                        'market_situation' => 'required',
                        'contact_email' => 'nullable|email',
            ]);
            //////////////////////////////////////////////////////////
            if ($validator->fails()) {
                $request->session()->flash('danger', $validator->messages());
                return redirect(route('settings.view'))->withInput();
            } else {
                $update = $info->update($save_data);
                if ($update) {
                    Cache::forget('settings');
                    $info = $settings->getSetting(1);
                    ////////////////////////////////////////////
                    $request->session()->flash('success', self::UPDATE_SUCCESS);
                    return redirect(route('settings.view'));
                } else {
                    $request->session()->flash('danger', self::EXECUTION_ERROR);
                    return redirect(route('settings.view'));
                }
            }
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route('settings.view'));
        }
    }
    public function updateCloseStatus(Request $request)
    {
        $validatedData = $request->validate([
            'close_status' => 'boolean',
            'close_text' => 'required|string'
        ], [
            'close_status.boolean' => 'حقل الحالة يجب أن يكون صحيح أو خطأ.',
            'close_text.required' => 'حقل نص الإغلاق مطلوب.',
            'close_text.string' => 'حقل نص الإغلاق يجب أن يكون نصًا.'
        ]);
    
        try {
            $settings = Settings::first();
            $settings->close_status = $validatedData['close_status'];
            $settings->close_text = $validatedData['close_text'];
            $settings->save();
    
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحالة ونص الإغلاق بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث.'
            ], 500);
        }
    }
    
    
}
