<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ShopPlanApplication;

class Partner extends Model
{
    protected $fillable = [
        'type', 'company_name', 'contact_name', 'email', 'tel',
        'referral_code', 'commission_rate', 'commission_rate_override', 'bank_info', 'invoice_number', 'invoy_client_code', 'status', 'notes',
    ];

    protected $casts = [
        'commission_rate'          => 'decimal:4',
        'commission_rate_override' => 'decimal:4',
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

    /** 掲載中（active）の管理店舗数 */
    public function activeManagedShopsCount(): int
    {
        return $this->shops()->where('status', 'active')->count();
    }

    /** 管理件数から算出したマージン率（ベース20%、100件ごとに+1%、上限30%） */
    public function calculatedManagementRate(): float
    {
        $increment = min(intdiv($this->activeManagedShopsCount(), 100), 10);
        return 0.20 + $increment * 0.01;
    }

    /** 実効マージン率（管理代行：オーバーライド優先、なければ自動計算。紹介：commission_rate） */
    public function effectiveCommissionRate(): float
    {
        if ($this->isManagement()) {
            return $this->commission_rate_override !== null
                ? (float) $this->commission_rate_override
                : $this->calculatedManagementRate();
        }
        return (float) $this->commission_rate;
    }

    /** 手数料率をパーセント表示（例：10.00） */
    public function commissionRatePercent(): string
    {
        return number_format($this->effectiveCommissionRate() * 100, 2);
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

        $discount = $this->effectiveCommissionRate();
        return (int) round($total * (1 - $discount) * 1.1);
    }
}
