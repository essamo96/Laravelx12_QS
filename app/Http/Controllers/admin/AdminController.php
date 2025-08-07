<?php

namespace App\Http\Controllers\Admin;
use App\Models\Setting;
use App\Models\PermissionsGroup;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/* * **************************** */

class AdminController extends BaseController {

    public static $data = [];

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function __construct() {
        $permission_group = new PermissionsGroup();
        self::$data['sidebar'] = $permission_group->getAllParentPermissionGroup();
        // dd(self::$data['sidebar']);
        self::$data['settings'] = Setting::where('id', 1)->first();
        $route_name = Route::currentRouteName();
        // dd($route_name);
        $route_data = explode('.', $route_name);
        $current_route = $route_data[0];
        // dd($current_route);
        $init_obj = new \stdClass();
        $init_obj->name = '';
        $init_obj->parent_id = '';
        self::$data['current_route'] = $init_obj;
        foreach (self::$data['sidebar'] as $menu_item) {
            // dd($menu_item->name_ar);
            if ($current_route == $menu_item->name) {
                // self::$data['current_route'] = $menu_item;
            }
            foreach ($menu_item->mychild as $child_item) {
                if ($current_route == $child_item->name) {
                    self::$data['current_route'] = $child_item;
                    break;
                }
            }
        }
        // self::$data['parents'] = Parents::all();
        // self::$data['admins'] = Admin::all();
        // self::$data['deletedAdmins'] = Admin::onlyTrashed()->get();
        // self::$data['materials'] = Materials::all();
        // self::$data['storehouse'] = Storehouse::all();
        // // أولياء الأمور المضافين يوميًا
        // $dailyParents = Parents::whereDate('created_at', Carbon::today())->count();

        // // أولياء الأمور المضافين أسبوعيًا
        // $weeklyParents = Parents::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

        // // أولياء الأمور المضافين شهريًا
        // $monthlyParents = Parents::whereMonth('created_at', Carbon::now()->month)->count();

        // // أولياء الأمور المضافين سنويًا
        // $yearlyParents = Parents::whereYear('created_at', Carbon::now()->year)->count();

        // self::$data['dailyParents'] = $dailyParents;
        // self::$data['weeklyParents'] = $weeklyParents;
        // self::$data['monthlyParents'] = $monthlyParents;
        // self::$data['yearlyParents'] = $yearlyParents;
    }
}
