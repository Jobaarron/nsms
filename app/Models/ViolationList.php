<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationList extends Model
{
    protected $fillable = [
        'title',
        'severity',
        'category',
    ];
}
