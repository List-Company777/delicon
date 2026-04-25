<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchKeyword extends Model
{
    protected $fillable = ['keyword', 'gender', 'search_count', 'normalization_status'];
}
