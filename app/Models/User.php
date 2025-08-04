<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use  HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role_id',
        'created_by',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    // علاقة بالمستخدم الذي أنشأ الحساب
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // فلترة المستخدمين النشطين فقط
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    ////////////////////////////////
    // إضافة مستخدم جديد
    public function addUser($username, $name, $email, $role, $created_by, $password, $status)
    {
        $this->username = $username;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->created_by = $created_by;
        $this->password = bcrypt($password); // تشفير كلمة المرور
        $this->status = $status;

        $this->save();
        return $this;
    }

    ////////////////////////////////
    // تعديل بيانات مستخدم
    public function updateUser($obj, $username, $name, $email, $role, $status)
    {
        $obj->username = $username;
        $obj->name = $name;
        $obj->email = $email;
        $obj->role = $role;
        $obj->status = $status;

        $obj->save();
        return $obj;
    }

    ////////////////////////////////
    // تحديث كلمة المرور
    public function updatePassword($id, $password)
    {
        return $this->where('id', $id)
            ->update(['password' => bcrypt($password)]);
    }

    ////////////////////////////////
    // تحديث الحالة (نشط / غير نشط)
    public function updateStatus($id, $status)
    {
        return $this->where('id', $id)
            ->update(['status' => $status]);
    }

    ////////////////////////////////
    // حذف مستخدم (Soft Delete)
    public function deleteUser($obj)
    {
        return $obj->delete();
    }

    ////////////////////////////////
    // جلب مستخدم حسب الـ ID
    public function getUser($id)
    {
        return $this->find($id);
    }

    ////////////////////////////////
    // جلب كل المستخدمين الغير محذوفين
    public function getUsers()
    {
        return $this->whereNull('deleted_at')->get();
    }
}
