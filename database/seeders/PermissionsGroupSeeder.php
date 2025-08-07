<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionsGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions_group')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $groups = [
            [
                'name' => 'main_dashboard',
                'name_ar' => 'الرئيسيه',
                'name_en' => 'Dashboard',
                'icon' => 'bi-house-door',
                'sort' => 1,
                'status' => 1,
                'parent_id' => 0,
                'created_at' => '2023-07-07 08:00:21',
                'updated_at' => '2023-07-07 08:00:21',
            ],
            [
                'name' => 'dashboard',
                'name_ar' => 'الرئيسية',
                'name_en' => 'Dashboard',
                'icon' => null,
                'sort' => 1,
                'status' => 1,
                'parent_id' => 1,
                'created_at' => '2023-04-11 22:14:40',
                'updated_at' => '2023-07-07 09:50:17',
            ],
            [
                'name' => 'users_menu',
                'name_ar' => 'ادارة  تسجيل الدخول',
                'name_en' => 'Login Management',
                'icon' => 'bi-lock-fill',
                'sort' => 7,
                'status' => 1,
                'parent_id' => 0,
                'created_at' => '2023-04-12 09:49:36',
                'updated_at' => '2023-09-24 21:01:01',
            ],
            [
                'name' => 'constants',
                'name_ar' => 'قائمة ادارة الثوابت',
                'name_en' => 'Constants Management List',
                'icon' => 'bi-pin-angle-fill',
                'sort' => 4,
                'status' => 1,
                'parent_id' => 0,
                'created_at' => '2023-04-24 18:06:27',
                'updated_at' => '2023-09-24 20:45:44',
            ],
            [
                'name' => 'roles',
                'name_ar' => 'ادارة الوظائف',
                'name_en' => 'Job management',
                'icon' => null,
                'sort' => 2,
                'status' => 1,
                'parent_id' => 3,
                'created_at' => '2017-10-04 17:21:10',
                'updated_at' => '2024-08-25 15:33:47',
            ],
            [
                'name' => 'settings',
                'name_ar' => 'الاعدادات',
                'name_en' => 'Settings',
                'icon' => 'bi bi-gear',
                'sort' => 5,
                'status' => 1,
                'parent_id' => 54,
                'created_at' => '2023-04-09 17:09:13',
                'updated_at' => '2023-04-17 12:51:48',
            ],
            [
                'name' => 'permissions_group',
                'name_ar' => 'القائمة الجانبية',
                'name_en' => 'Side Menu',
                'icon' => null,
                'sort' => 4,
                'status' => 1,
                'parent_id' => 3,
                'created_at' => '2017-10-04 17:20:44',
                'updated_at' => '2023-09-24 20:42:40',
            ],
            [
                'name' => 'permissions',
                'name_ar' => 'صلاحية القائمة الجانبية',
                'name_en' => 'Permissions',
                'icon' => null,
                'sort' => 6,
                'status' => 1,
                'parent_id' => 3,
                'created_at' => '2023-04-11 17:06:37',
                'updated_at' => '2023-04-12 10:35:58',
            ],


            [
                'name' => 'pages',
                'name_ar' => 'الصفحات الثابته',
                'name_en' => 'Pages',
                'icon' => null,
                'sort' => 5,
                'status' => 1,
                'parent_id' => 54,
                'created_at' => '2023-04-16 10:46:11',
                'updated_at' => '2023-04-17 12:50:49',
            ],
            [
                'name' => 'system_settings',
                'name_ar' => 'اعدادات النظام',
                'name_en' => 'System settings',
                'icon' => 'bi-gear',
                'sort' => 5,
                'status' => 1,
                'parent_id' => 0,
                'created_at' => '2023-04-17 12:46:53',
                'updated_at' => '2023-04-17 12:53:15',
            ],

            [
                'name' => 'notifications',
                'name_ar' => 'الاشعارات',
                'name_en' => 'Notifications',
                'icon' => 'bi-bell-fill',
                'sort' => 1,
                'status' => 1,
                'parent_id' => 0,
                'created_at' => '2023-06-12 20:13:54',
                'updated_at' => '2024-08-25 16:10:07',
            ],

            [
                'name' => 'static_system',
                'name_ar' => 'ثوابت النظام',
                'name_en' => 'Static Mangement',
                'icon' => 'bi-gear',
                'sort' => 20,
                'status' => 1,
                'parent_id' => 0,
                'created_at' => '2024-08-08 18:05:02',
                'updated_at' => '2024-08-08 18:05:02',
            ],
            // [
            //     'name' => 'group',
            //     'name_ar' => 'ادارة المجموعات',
            //     'name_en' => 'Group management',
            //     'icon' => 'bi bi-diagram-3-fill',
            //     'sort' => 5,
            //     'status' => 1,
            //     'parent_id' => 0,
            //     'created_at' => '2024-08-20 12:06:38',
            //     'updated_at' => '2024-08-20 12:15:04',
            // ],
        ];

        foreach ($groups as $index => $group) {
            DB::table('permissions_group')->insert(array_merge($group, ['id' => $index + 1]));
        }
    }
}
