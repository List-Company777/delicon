<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ShopPricePlan;
use App\Models\ShopOtherCharge;
use App\Models\XmlFeed;
use App\Models\Cast;

class Shop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'kana', 'genre_id', 'prefecture_id', 'area_id', 'station_id',
        'postal_code', 'address_locality', 'address', 'nearest_line', 'nearest_station_name', 'nearest_station_walk',
        'tel', 'line_id',
        'logo_image', 'main_image', 'status', 'bid_price', 'budget_balance', 'partner_id',
        'xml_source', 'xml_id', 'xml_enabled',
        'xml_bid_price', 'xml_monthly_budget', 'xml_plan_activated_at', 'xml_disabled_at', 'xml_image_url',
        'line_notify_user_id',
        'permit_type', 'permit_document_path',
        'paid_since', 'plan1_since', 'plan2_since', 'plan3_since', 'plan4_since', 'plan_expires_on',
        'alive_check_token', 'alive_check_sent_at', 'alive_confirmed_at',
        'base', 'catche', 'sangyo_text1', 'sangyo_text2', 'sangyo_text3', 'system_text', 'coupon', 'open_time', 'close_time', 'all_time', 'rest_day',
        'price_60', 'price_90', 'price_120', 'price_high', 'eigyo_area', 'eigyo_space',
        'shop_type_id', 'shop_type_id2', 'tags', 'plan', 'is_banner_plan', 'banner_checked_at', 'area_name_ok', 'banner_ok', 'banner_checked_at',
    ];

    protected $casts = [
        'xml_enabled'           => 'boolean',
        'xml_plan_activated_at' => 'datetime',
        'xml_disabled_at'       => 'datetime',
        'alive_check_sent_at'   => 'datetime',
        'tags'                  => 'array',
        'alive_confirmed_at'    => 'datetime',
        'banner_checked_at'     => 'datetime',
        'is_banner_plan'        => 'boolean',
    ];

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(ShopDetail::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(PartnerCommission::class);
    }


    public function isPaid(): bool
    {
        return ($this->plan ?? 99) <= 4;
    }

    public function planApplications(): HasMany
    {
        return $this->hasMany(ShopPlanApplication::class);
    }

    public function setPrices(): HasMany
    {
        return $this->hasMany(ShopSetPrice::class)->orderBy('sort_order');
    }

    public function pricePlans(): HasMany
    {
        return $this->hasMany(ShopPricePlan::class)->orderBy('sort_order');
    }

    public function otherCharges(): HasMany
    {
        return $this->hasMany(ShopOtherCharge::class)->orderBy('sort_order');
    }

    public function externalUrls(): HasMany
    {
        return $this->hasMany(ShopExternalUrl::class)->orderBy('sort_order');
    }

    public function castJobLimit(): int
    {
        return $this->hasBudget() ? 3 : 1;
    }

    public function staffJobLimit(): int
    {
        return $this->hasBudget() ? 5 : 1;
    }

    /** 有料プランが有効（残高が bid_price 以上ある） */
    public function hasBudget(): bool
    {
        return $this->budget_balance >= $this->bid_price;
    }

    public function getFullAddressAttribute(): string
    {
        $addr = $this->address;
        if (!$addr) {
            return implode('', array_filter([
                $this->prefecture?->name,
                $this->address_locality ?: $this->area?->name,
            ]));
        }
        $pref = $this->prefecture?->name;
        if ($pref && !str_starts_with($addr, $pref)) {
            return $pref . $addr;
        }
        return $addr;
    }

    /** XML連携中（任意フィード）かどうか */
    public function isXmlActive(): bool
    {
        if (!$this->xml_enabled || !$this->xml_source) {
            return false;
        }
        return XmlFeed::where('slug', $this->xml_source)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * 1クリック分の予算を消費する。
     * XML連携中の店舗は surcharge（hotlink加算分）のみ消費し bid_price は消費しない。
     * 残高が次のクリック分を下回ったら bid_price を 30（最低単価）にリセットして通知を返す。
     * @return bool リセットが発生したかどうか
     */
    public function consumeBudget(int $surcharge = 0): bool
    {
        return DB::transaction(function () use ($surcharge) {
            $shop = static::where('id', $this->id)->lockForUpdate()->first();

            if (!$shop->hasBudget()) {
                return false;
            }

            // XML連携中: 通常クリックは無料、hotlink surchargeのみ消費
            if ($shop->isXmlActive()) {
                if ($surcharge <= 0) {
                    return false;
                }
                $shop->decrement('budget_balance', $surcharge);
                if (!$shop->fresh()->hasBudget()) {
                    $shop->update(['bid_price' => 30]);
                    return true;
                }
                return false;
            }

            $amount = $shop->bid_price + $surcharge;
            $shop->decrement('budget_balance', $amount);
            if (!$shop->fresh()->hasBudget()) {
                $shop->update(['bid_price' => 30]);
                return true;
            }

            return false;
        });
    }

    public function castMembers(): HasMany
    {
        return $this->hasMany(Cast::class)->where("status", "active")->orderBy("sort_order");
    }

    public function getShopBannerUrlAttribute(): ?string
    {
        if (!$this->shop_file_name) return null;
        $path = $this->shop_file_name;
        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= '.jpg';
        }
        return $path;
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if ($this->main_image) {
            return Storage::url($this->main_image);
        }
        return null;
    }

    /**
     * 店舗詳細ページ用バナー画像URL（5:2）
     * main_image（新規アップロード）→ shop_file_name（レガシー）の順で優先
     */
    public function getBannerUrlAttribute(): ?string
    {
        if ($this->main_image) {
            $bannerPath = str_replace('main.jpg', 'main_banner.jpg', $this->main_image);
            return Storage::url($bannerPath);
        }
        return $this->shop_file_name;
    }

    public function getBannerWebpUrlAttribute(): ?string
    {
        if ($this->main_image) {
            return null; // storage画像はWebP未生成
        }
        if ($this->shop_file_name) {
            $path = $this->shop_file_name;
            if (!pathinfo($path, PATHINFO_EXTENSION)) {
                $path .= '.jpg';
            }
            return $path . '.webp';
        }
        return null;
    }

    public function shopType(): BelongsTo
    {
        return $this->belongsTo(ShopType::class, "shop_type_id");
    }

    public function shopType2(): BelongsTo
    {
        return $this->belongsTo(ShopType::class, "shop_type_id2");
    }

    public function news(): HasMany
    {
        return $this->hasMany(ShopNews::class)->latest();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }


    protected static function booted(): void
    {
        static::deleting(function (Shop $shop) {
            // キャスト画像（削除前に取得）
            Cast::where('shop_id', $shop->id)->with('images')->get()->each(function (Cast $cast) {
                // プロフィール画像（新システムでアップロードされたもののみ）
                if ($cast->img_file_name
                    && !str_starts_with($cast->img_file_name, '/img/common/')
                    && !str_starts_with($cast->img_file_name, '/img/girl/00/')) {
                    @unlink(public_path($cast->img_file_name . 'big.jpg'));
                    @unlink(public_path($cast->img_file_name . 'big.jpg.webp'));
                }
                // 追加写真（cast_images）
                foreach ($cast->images as $img) {
                    Storage::disk('public')->delete([
                        $img->img_path,
                        str_replace('.jpg', '.webp', $img->img_path),
                    ]);
                }
                // 日記画像
                Storage::disk('public')->deleteDirectory("casts/{$cast->id}/diary");
            });

            // 店舗ディレクトリ（メイン画像・求人画像）
            Storage::disk('public')->deleteDirectory("company/{$shop->id}");
        });
    }

    public function reviews()
    {
        return $this->hasMany(ShopReview::class);
    }
}
