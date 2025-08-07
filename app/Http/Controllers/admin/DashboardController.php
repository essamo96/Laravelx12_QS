<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends AdminController {

    public function __construct() {
//        $info = Admin::findOrFail(8);
//        $info->assignRole('employee');
//        Cache::forget('spatie.permission.cache');
        parent::__construct();
        parent::$data['active_menu'] = 'dashboard';
    }

    ///////////////////////////////
    public function getIndex() {
        // dd(self::$data['current_route']);
        return view('admin.dashboard.view', parent::$data);
    }

    public function getLang($lang) {
        Cache::forget('lang_' . Auth::guard('admin')->user()->id);
        Cache::forever('lang_' . Auth::guard('admin')->user()->id, $lang);
        return redirect(url()->previous());
    }
}
