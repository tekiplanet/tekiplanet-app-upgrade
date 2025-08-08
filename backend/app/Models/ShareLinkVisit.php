<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareLinkVisit extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_product_share_id',
        'visitor_ip',
        'user_agent',
        'referrer',
        'visited_at'
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function userProductShare(): BelongsTo
    {
        return $this->belongsTo(UserProductShare::class);
    }

    /**
     * Get the conversion rate for this share link.
     */
    public function getConversionRate(): float
    {
        $share = $this->userProductShare;
        if ($share->click_count === 0) {
            return 0.0;
        }
        
        return round(($share->purchase_count / $share->click_count) * 100, 2);
    }
}
