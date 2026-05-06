<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CastBodyType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'sort_order'];
}
