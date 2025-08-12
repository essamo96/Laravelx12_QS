<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionsGroup extends Model
{
    use SoftDeletes;

    protected $table = 'permissions_group';

    protected $fillable = [
        'name', 'name_ar', 'name_en', 'icon', 'sort','color', 'status', 'parent_id',
    ];

    protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // العلاقة مع الأبناء (مجموعات فرعية)
    public function mychild()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort', 'asc');
    }

    // نسخة ثانية بدون ترتيب (احتياطية)
    public function childrenRaw()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // العلاقة مع جدول الصلاحيات
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes / Actions
    |--------------------------------------------------------------------------
    */
    public function deletePermissionsGroup($obj)
    {
        return $obj->delete();
    }

    public function getPermissionsGroup($id)
    {
        return $this->find($id);
    }

    public function getAllPermissionGroup()
    {
        return $this->where('status', 1)->orderBy('sort', 'asc')->get();
    }

    public function getAllParentPermissionGroup()
    {
        return $this->where('parent_id', 0)->where('status', 1)->orderBy('sort', 'asc')->get();
    }

    public function getAllPermissionGroupSearch($name = null)
    {
        return $this->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', '%' . $name . '%');
            }
        })->where('parent_id', 0)->get();
    }

    public function getPermissionGroupSearch($name = null)
    {
        return $this->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', '%' . $name . '%');
            }
        })->orderBy('sort')->get();
    }

    public function updateStatus($id, $status)
    {
        return $this->where('id', $id)->update(['status' => $status]);
    }
}
