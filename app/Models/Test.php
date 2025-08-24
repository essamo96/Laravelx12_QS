<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = ['age',
'big_number',
'birth_date',
'description',
'last_login_at',
'name_ar',
'name_en',
'options',
'price',
'published_at',
'status',
'user_id',
'uuid',
'weight'];

    public function getSearch($name = null)
    {
        return $this->where(function ($query) use ($name) {
            if ($name != "") {
                $query->where('name', 'LIKE', '%' . $name . '%');
            }
        })->get();
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}