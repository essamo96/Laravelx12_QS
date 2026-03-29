<?php

return [

    'guard' => env('PERMISSION_GUARD', 'admin'),

    'permission_prefix' => 'admin',

    'actions' => [
        'view',
        'add',
        'edit',
        'delete',
        'update',
        'status',
    ],

    'default_group_parent_id' => (int) env('PERMISSIONS_GROUP_PARENT_ID', 3),

    'super_admin_role' => env('SUPER_ADMIN_ROLE', 'super-admin'),

    'admin_view_paths' => [
        resource_path('views/admin'),
    ],

    'skip_view_directories' => [
        'layout',
        'layouts',
        'parts',
        'components',
        'auth',
        'errors',
        'login',
    ],
];
