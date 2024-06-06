<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'roles';

    protected $fillable = ['name', 'created_at'];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_role', 'user_id', 'role_id');
    }
}
