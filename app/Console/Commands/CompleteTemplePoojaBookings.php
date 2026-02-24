<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteTemplePoojaBookings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pooja:complete-bookings';

    /**
     * The console command description.
     */
    protected $description = 'Mark temple pooja bookings as complete every night at 11:30 PM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = DB::table('temple_pooja_bookings_tracking')
            ->where('booking_status', '=', 'pending')
            ->whereDate('pooja_date', '<=', Carbon::today())
            ->update([
                'booking_status' => 'completed',
                'updated_at' => Carbon::now(),
            ]);

        $this->info("Temple pooja bookings updated: {$updated}");
    }
}
