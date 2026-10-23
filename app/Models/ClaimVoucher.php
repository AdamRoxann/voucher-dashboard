<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimVoucher extends Model
{
    protected $fillable = ['claim_id','voucher_type','voucher_code'];
    public function claim(): BelongsTo { return $this->belongsTo(Claim::class); }
}
