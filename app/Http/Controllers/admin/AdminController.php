<?php

namespace App\Http\Controllers\Admin;
use App\Models\Setting;
use App\Models\PermissionsGroup;
use Illuminate\Support\Collection;
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
    $sidebar = $permission_group->getAllParentPermissionGroup();
    $sidebar = $this->mergeConfigMenuIntoSidebar($sidebar);
    self::$data['sidebar'] = $this->filterSidebarByRegisteredRoutes($sidebar);
    // self::$data['settings'] = Setting::where('id', 1)->first();

    // الحصول على اسم الراوت الحالي
    $route_name = Route::currentRouteName();
    $route_data = explode('.', $route_name);
    $current_route = $route_data[0] ?? '';

    // قيمة افتراضية في حال عدم وجود تطابق
    $init_obj = new \stdClass();
    $init_obj->name = '';
    $init_obj->name_ar = '';
    $init_obj->name_en = '';
    $init_obj->parent_id = '';
    self::$data['current_route'] = $init_obj;

    // البحث عن تطابق في القائمة الجانبية (الأبناء أو الأبوين)
    foreach (self::$data['sidebar'] ?? [] as $menu_item) {
        // تحقق من الأب نفسه
        if ($current_route === $menu_item->name) {
            self::$data['current_route'] = $menu_item;
            break;  // وجدنا التطابق نوقف البحث
        }

        // تحقق من الأبناء إن وجدوا (mychild قد تكون مصفوفة أو empty)
        if (!empty($menu_item->mychild)) {
            foreach ($menu_item->mychild as $child_item) {
                if ($current_route === $child_item->name) {
                    self::$data['current_route'] = $child_item;
                    break 2; // خروج من الحلقات كلها
                }
            }
        }
    }
}

    /**
     * دمج عناصر من config/admin_menu.php مع القائمة القادمة من permissions_group (بدون تكرار الاسم).
     */
    protected function mergeConfigMenuIntoSidebar(Collection $sidebar): Collection
    {
        $existing = $sidebar->pluck('name')->filter()->all();

        foreach (config('admin_menu', []) as $key => $entry) {
            $name = is_array($entry) ? ($entry['name'] ?? $key) : $key;
            if (in_array($name, $existing, true)) {
                continue;
            }

            $sidebar->push((object) [
                'id' => null,
                'name' => $name,
                'name_ar' => $entry['name_ar'] ?? $name,
                'name_en' => $entry['name_en'] ?? $name,
                'icon' => $entry['icon'] ?? 'bi-grid',
                'color' => $entry['color'] ?? 'primary',
                'sort' => (int) ($entry['sort'] ?? 500),
                'status' => 1,
                'parent_id' => 0,
                'mychild' => collect(),
            ]);
            $existing[] = $name;
        }

        return $sidebar->unique(fn ($item) => (string) ($item->name ?? ''))
            ->sortBy(fn ($item) => (int) ($item->sort ?? 0))
            ->values();
    }

    /**
     * إخفاء عناصر القائمة التي لا يوجد لها مسار {name}.view مسجّل (مثلاً بعد حذف وحدة tests).
     * الصلاحية وحدها لا تكفي — يجب أن يكون المسار موجوداً حتى لا يحدث RouteNotFoundException في القائمة.
     */
    protected function filterSidebarByRegisteredRoutes(Collection $sidebar): Collection
    {
        return $sidebar->map(function ($item) {
            if (! empty($item->mychild) && count($item->mychild) > 0) {
                $filtered = $item->mychild->filter(function ($child) {
                    $cn = $child->name ?? '';

                    return $cn !== '' && Route::has($cn.'.view');
                })->values();

                if ($filtered->isEmpty()) {
                    return null;
                }
                $item->mychild = $filtered;

                return $item;
            }

            $name = $item->name ?? '';
            if ($name === '' || ! Route::has($name.'.view')) {
                return null;
            }

            return $item;
        })->filter()->values();
    }

}
