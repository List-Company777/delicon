<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ShopPlanApplication;

class Partner extends Model
{
    protected $fillable = [
        'type', 'company_name', 'contact_name', 'email', 'tel',
        'referral_code', 'commission_rate', 'bank_info', 'invoice_number', 'status', 'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:4',
    ];

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(PartnerCommission::class);
    }

    public function planApplications(): HasMany
    {
        return $this->hasMany(ShopPlanApplication::class);
    }

    public function isManagement(): bool
    {
        return $this->type === 'management';
    }

    public function isReferral(): bool
    {
        return $this->type === 'referral';
    }

    /** 手数料率をパーセント表示（例：10.00） */
    public function commissionRatePercent(): string
    {
        return number_format((float) $this->commission_rate * 100, 2);
    }

    /** 累計未払い手数料（紹介代理店用） */
    public function pendingAmount(): int
    {
        return (int) $this->commissions()->where('status', 'pending')->sum('commission_amount');
    }

    /**
     * 管理代行代理店：指定月の請求額（税込）
     * 申請金額 × (1 - discount_rate) × 1.1
     */
    public function billingAmountForMonth(int $year, int $month): int
    {
        $total = (int) $this->planApplications()
            ->where('status', 'approved')
            ->whereYear('approved_at', $year)
            ->whereMonth('approved_at', $month)
            ->sum('amount');

        $discount = (float) $this->commission_rate; // 管理代行では割引率として使用
        return (int) round($total * (1 - $discount) * 1.1);
    }
}
