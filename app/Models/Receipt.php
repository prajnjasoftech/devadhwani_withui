<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property string $receipt_number
 * @property int|null $devotee_id
 * @property int|null $member_id
 * @property \Carbon\Carbon $receipt_date
 * @property float $total_amount
 * @property float $discount
 * @property float $net_amount
 * @property float $amount_paid
 * @property float $balance_due
 * @property string $payment_status
 * @property string|null $remarks
 * @property-read Temple $temple
 * @property-read Devotee|null $devotee
 * @property-read Member|null $member
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ReceiptPayment> $payments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TemplePoojaBooking> $bookings
 */
class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'temple_id',
        'receipt_number',
        'devotee_id',
        'member_id',
        'receipt_date',
        'total_amount',
        'discount',
        'net_amount',
        'amount_paid',
        'balance_due',
        'payment_status',
        'remarks',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Temple, Receipt>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }

    /**
     * @return BelongsTo<Devotee, Receipt>
     */
    public function devotee(): BelongsTo
    {
        return $this->belongsTo(Devotee::class);
    }

    /**
     * @return BelongsTo<Member, Receipt>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return HasMany<ReceiptPayment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ReceiptPayment::class);
    }

    /**
     * @return HasMany<TemplePoojaBooking>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(TemplePoojaBooking::class);
    }

    /**
     * Recalculate payment totals from payments.
     */
    public function recalculatePayments(): void
    {
        $this->amount_paid = $this->payments()->sum('amount');
        $this->balance_due = $this->net_amount - $this->amount_paid;

        if ($this->balance_due <= 0) {
            $this->payment_status = 'paid';
            $this->balance_due = 0;
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }

        $this->save();
    }

    /**
     * Recalculate totals from bookings.
     */
    public function recalculateTotals(): void
    {
        $this->total_amount = $this->bookings()->sum(\DB::raw('pooja_amount + COALESCE(service_charge, 0)'));
        $this->net_amount = $this->total_amount - $this->discount;
        $this->balance_due = $this->net_amount - $this->amount_paid;

        if ($this->balance_due <= 0) {
            $this->payment_status = 'paid';
            $this->balance_due = 0;
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }

        $this->save();
    }

    /**
     * Generate next receipt number (globally unique).
     */
    public static function generateReceiptNumber(int $templeId): string
    {
        $year = date('Y');
        // Check globally since receipt_number is unique across all temples
        $lastReceipt = static::where('receipt_number', 'like', "R-{$year}-%")
            ->orderByRaw('CAST(SUBSTRING(receipt_number, -6) AS UNSIGNED) DESC')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->receipt_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'R-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
