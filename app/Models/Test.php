<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description',
        'age',
        'big_number',
        'price',
        'weight',
        'status',
        'birth_date',
        'published_at',
        'last_login_at',
        'uuid',
        'options',
        'user_id',
        'image',
        'tags',
    ];

    public array $searchable = [
        'name_ar',
        'name_en',
        'description',
        'uuid',
        'options',
        'image',
        'tags',
    ];

    public array $datatableColumns = [
        'name_ar',
        'age',
        'price',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

