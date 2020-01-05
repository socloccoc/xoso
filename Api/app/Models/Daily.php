<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daily extends Model
{
    protected $guarded = [];
    protected $table = 'dailies';
    public $timestamps = false;
}
