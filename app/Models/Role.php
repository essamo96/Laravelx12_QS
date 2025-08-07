<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'status',
        'is_user',
    ];

    /**
     * علاقة مع جدول الأدمن
     */
    // public function admins()
    // {
    //     return $this->hasMany(Admin::class);
    // }

    /**
     * علاقة مع جدول الصلاحيات
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /**
     * إضافة رتبة جديدة
     */
    public function addRole($name, $status, $is_user)
    {
        return self::create([
            'name'     => $name,
            'status'   => $status,
            'is_user'  => $is_user,
        ]);
    }

    /**
     * تحديث رتبة
     */
    public function updateRole($role, $name, $status, $is_user)
    {
        $role->update([
            'name'     => $name,
            'status'   => $status,
            'is_user'  => $is_user,
        ]);

        return $role;
    }

    /**
     * تحديث حالة رتبة
     */
    public function updateStatus($id, $status)
    {
        return self::where('id', $id)->update(['status' => $status]);
    }

    /**
     * حذف رتبة
     */
    public function deleteRole($role)
    {
        return $role->delete();
    }

    /**
     * جلب رتبة بواسطة ID
     */
    public function getRole($id)
    {
        return self::find($id);
    }

    /**
     * جلب جميع الرتب الفعالة
     */
    public function getAllRolesActive()
    {
        return self::where('status', 1)->whereNull('deleted_at')->get();
    }

    /**
     * جلب الرتب مع صلاحياتها - مع فلترة حسب الاسم
     */
    public function getRoles($name = null)
    {
        return self::with('permissions')->when($name, function ($query) use ($name) {
            $query->where('name', 'LIKE', '%' . $name . '%');
        })->get();
    }
}
