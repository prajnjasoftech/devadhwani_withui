<?php

namespace Tests\Unit;

use App\Models\Devotee;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\ReceiptPayment;
use App\Models\Temple;
use App\Models\TemplePoojaBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptModelTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected function setUp(): void
    {
        parent::setUp();
        $this->temple = Temple::factory()->create();
    }

    protected function uniqueReceiptNumber(): string
    {
        return 'T-' . substr(uniqid(), -8);
    }

    /** @test */
    public function it_can_create_a_receipt()
    {
        $receiptNumber = $this->uniqueReceiptNumber();
        $receipt = Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $receiptNumber,
            'receipt_date' => now(),
            'total_amount' => 1000,
            'net_amount' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('receipts', [
            'id' => $receipt->id,
            'receipt_number' => $receiptNumber,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_temple()
    {
        $receipt = Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $this->uniqueReceiptNumber(),
            'receipt_date' => now(),
            'total_amount' => 1000,
            'net_amount' => 1000,
        ]);

        $this->assertInstanceOf(Temple::class, $receipt->temple);
        $this->assertEquals($this->temple->id, $receipt->temple->id);
    }

    /** @test */
    public function it_can_have_payments()
    {
        $receipt = Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $this->uniqueReceiptNumber(),
            'receipt_date' => now(),
            'total_amount' => 1000,
            'net_amount' => 1000,
        ]);

        ReceiptPayment::create([
            'receipt_id' => $receipt->id,
            'amount' => 500,
            'payment_date' => now(),
            'payment_mode' => 'cash',
        ]);

        $this->assertCount(1, $receipt->payments);
        $this->assertEquals(500, $receipt->payments->first()->amount);
    }

    /** @test */
    public function it_recalculates_payment_totals()
    {
        $receipt = Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $this->uniqueReceiptNumber(),
            'receipt_date' => now(),
            'total_amount' => 1000,
            'net_amount' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'payment_status' => 'pending',
        ]);

        ReceiptPayment::create([
            'receipt_id' => $receipt->id,
            'amount' => 600,
            'payment_date' => now(),
            'payment_mode' => 'cash',
        ]);

        $receipt->refresh();

        $this->assertEquals(600, $receipt->amount_paid);
        $this->assertEquals(400, $receipt->balance_due);
        $this->assertEquals('partial', $receipt->payment_status);
    }

    /** @test */
    public function it_marks_as_paid_when_fully_paid()
    {
        $receipt = Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $this->uniqueReceiptNumber(),
            'receipt_date' => now(),
            'total_amount' => 1000,
            'net_amount' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'payment_status' => 'pending',
        ]);

        ReceiptPayment::create([
            'receipt_id' => $receipt->id,
            'amount' => 1000,
            'payment_date' => now(),
            'payment_mode' => 'cash',
        ]);

        $receipt->refresh();

        $this->assertEquals(1000, $receipt->amount_paid);
        $this->assertEquals(0, $receipt->balance_due);
        $this->assertEquals('paid', $receipt->payment_status);
    }

    /** @test */
    public function it_generates_unique_receipt_numbers()
    {
        $number1 = Receipt::generateReceiptNumber($this->temple->id);
        $this->assertStringStartsWith('R-' . date('Y') . '-', $number1);

        Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $number1,
            'receipt_date' => now(),
            'total_amount' => 100,
            'net_amount' => 100,
        ]);

        $number2 = Receipt::generateReceiptNumber($this->temple->id);
        $this->assertNotEquals($number1, $number2);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $receipt = Receipt::create([
            'temple_id' => $this->temple->id,
            'receipt_number' => $this->uniqueReceiptNumber(),
            'receipt_date' => now(),
            'total_amount' => 1000,
            'net_amount' => 1000,
        ]);

        $receipt->delete();

        $this->assertSoftDeleted('receipts', ['id' => $receipt->id]);
    }
}
