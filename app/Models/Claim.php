<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    protected $fillable = ['claim_date','store_id','pic_name','pic_phone','total_claimed','mandiri_account_number','account_holder_name','transaction_date','proof_image_path','transfer_status','transfer_date','notes'];
    protected function casts(): array { return ['claim_date'=>'date','transaction_date'=>'date','transfer_date'=>'date','total_claimed'=>'decimal:2']; }
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function vouchers(): HasMany { return $this->hasMany(ClaimVoucher::class); }
    public function getVoucherCountAttribute(): int { return $this->vouchers->count(); }
}
