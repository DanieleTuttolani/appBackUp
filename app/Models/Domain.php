<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'domain_name',
        'database_name',
        'ip',
        'password',
        'backup_rate_time'
    ];
}
