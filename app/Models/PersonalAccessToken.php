<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = ['id', 'name', 'tokenable', 'token', 'abilities'];
}
