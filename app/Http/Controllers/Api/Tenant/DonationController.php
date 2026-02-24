<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Donation as MainDonation;
use App\Models\Tenant\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function index(Request $request)
    {

        // If id is passed, filter by temple_id
        if ($request->has('temple_id')) {
            // DB::reconnect('mysql');
            return MainDonation::where('temple_id', $request->temple_id)->get();

            // return  "inside = ".$request->temple_id;
        }
        // return "outside = ".$request->temple_id;

        // Otherwise, return all donations
        return Donation::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'donor_name' => 'required|string',
            'phone' => 'nullable',
            'amount' => 'required|numeric',
            'mode' => 'nullable|string',
            'donation_date' => 'required|date',
        ]);
        $donation = Donation::create($data);

        return response()->json(['message' => 'Donation added', 'donation' => $donation]);
    }
}
