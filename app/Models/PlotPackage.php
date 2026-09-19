<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotPackage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'size_kanal' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_slug', 'slug');
    }

    public function getTotalMarlaAttribute(): float
    {
        return $this->size_kanal * 20;
    }

    public function getCashTotalAttribute(): float
    {
        return $this->total_marla * $this->price_cash_per_marla;
    }

    public function getInstallmentTotalAttribute(): float
    {
        return $this->total_marla * $this->price_installment_per_marla;
    }

    public function getAdvanceAmountAttribute(): float
    {
        return round($this->installment_total * $this->advance_percent / 100);
    }

    public function getMonthlyInstallmentAttribute(): float
    {
        $months = max(1, $this->tenure_years * 12);

        return round(($this->installment_total - $this->advance_amount) / $months);
    }
}
