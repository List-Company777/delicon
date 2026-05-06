<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ShopType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
    public function shops(): HasMany { return $this->hasMany(Shop::class, 'shop_type_id'); }
}
