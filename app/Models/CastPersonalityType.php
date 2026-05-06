<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CastPersonalityType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'sort_order'];
}
