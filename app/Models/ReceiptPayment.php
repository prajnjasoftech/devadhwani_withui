<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $receipt_id
 * @property float $amount
 * @property \Carbon\Carbon $payment_date
 * @property string $payment_mode
 * @property string|null $transaction_id
 * @property int|null $member_id
 * @property string|null $remarks
 * @property-read Receipt $receipt
 * @property-read Member|null $member
 */
class ReceiptPayment extends Model
{
    protected $fillable = [
        'receipt_id',
        'amount',
        'payment_date',
        'payment_mode',
        'transaction_id',
        'member_id',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Receipt, ReceiptPayment>
     */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    /**
     * @return BelongsTo<Member, ReceiptPayment>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Boot method to update receipt totals on payment changes.
     */
    protected static function booted()
    {
        static::saved(function (ReceiptPayment $payment) {
            $payment->receipt->recalculatePayments();
        });

        static::deleted(function (ReceiptPayment $payment) {
            $payment->receipt->recalculatePayments();
        });
    }
}
