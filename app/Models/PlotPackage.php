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

    /** Short plot size as buyers say it: "5 Marla", "10 Marla", "1 Kanal", "2 Kanal", "30 Marla". */
    public function getSizeLabelAttribute(): string
    {
        $marla = round($this->total_marla, 2);
        $kanal = $marla / 20;

        if ($marla >= 20 && floor($kanal) == $kanal) {
            return rtrim(rtrim(number_format($kanal, 2, '.', ''), '0'), '.').' Kanal';
        }

        return rtrim(rtrim(number_format($marla, 2, '.', ''), '0'), '.').' Marla';
    }

    public function getCashTotalAttribute(): float
    {
        return $this->total_marla * $this->price_cash_per_marla;
    }

    /** False for cash-only plans (no installment rate set). */
    public function getHasInstallmentAttribute(): bool
    {
        return $this->price_installment_per_marla !== null;
    }

    public function getInstallmentTotalAttribute(): ?float
    {
        return $this->has_installment ? $this->total_marla * $this->price_installment_per_marla : null;
    }

    public function getAdvanceAmountAttribute(): ?float
    {
        return $this->has_installment ? round($this->installment_total * $this->advance_percent / 100) : null;
    }

    public function getMonthlyInstallmentAttribute(): ?float
    {
        if (! $this->has_installment) {
            return null;
        }
        $months = max(1, $this->tenure_years * 12);

        return round(($this->installment_total - $this->advance_amount) / $months);
    }
}
