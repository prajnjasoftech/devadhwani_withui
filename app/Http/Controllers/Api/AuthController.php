<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpLog;
use App\Models\Role;
use App\Models\Temple;
use Illuminate\Http\Request;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    // Step 1: Send OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        // Use test OTP when Twilio is disabled
        $otp = config('services.twilio.enabled') ? rand(100000, 999999) : env('TEST_OTP', '123456');

        OtpLog::create([
            'phone' => $request->phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        if (config('services.twilio.enabled')) {
            try {
                $client = new Client(
                    config('services.twilio.sid'),
                    config('services.twilio.auth_token')
                );

                $message = $client->messages->create(
                    'whatsapp:'.$request->phone,
                    [
                        'from' => config('services.twilio.whatsapp_from'),
                        'contentSid' => 'HXb5b62575e6e4ff6129ad7c8efe1f983e',
                        'contentVariables' => json_encode([
                            '1' => (string) $otp,
                            '2' => '-',
                        ]),
                    ]
                );

                logger()->info('WhatsApp sent', ['sid' => $message->sid]);
            } catch (TwilioException $e) {
                logger()->error('Twilio Error', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);
            }
        }

        $response = ['message' => 'OTP sent successfully'];

        // Include test OTP in response when Twilio is disabled (for testing)
        if (! config('services.twilio.enabled')) {
            $response['test_otp'] = $otp;
        }

        return response()->json($response);
    }

    // Step 1: verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required',
            'temple_name' => 'required|string',
            'temple_address' => 'required|string',
            'temple_logo' => 'nullable|file|mimes:jpg,png,jpeg',
        ]);

        // Find OTP
        $otpLog = OtpLog::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (! $otpLog) {
            return response()->json(['status' => false, 'error' => 'Invalid or expired OTP'], 401);
        }

        $otpLog->update(['is_verified' => true]);

        // Ensure this phone exists in either temples or members, but not both
        $templeExists = Temple::where('phone', $request->phone)->exists();
        $memberExists = Member::where('phone', $request->phone)->exists();

        if ($templeExists || $memberExists) {
            return response()->json([
                'error' => 'Conflict: Phone number exists for both Temple and Member.',
            ], 409);
        }
        // Upload logo if present
        $logoPath = $request->hasFile('temple_logo')
            ? $request->file('temple_logo')->store('temple_logos', 'public')
            : null;

        // Create or update temple
        $temple = Temple::updateOrCreate(
            ['phone' => $request->phone],
            [
                'temple_name' => $request->temple_name,
                'temple_address' => $request->temple_address,
                'temple_logo' => $logoPath,
            ]
        );
        /*   if (! $temple->database_name) {
            $dbName = 'temple_' . $temple->id;
            $temple->update(['database_name' => $dbName]);
            \DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            TenantHelper::setConnection($dbName);
            \Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);
        }*/

        // Generate Sanctum Token
        $token = $temple->createToken('temple_token')->plainTextToken;

        return response()->json([
            'message' => 'Temple registered successfully',
            'temple' => $temple,
            'token' => $token,
            // Default if no role found
            'role' => [
                'User' => '4',
                'Role' => '4',
                'Pooja' => '4',
                'Settings' => '4',
                'Events' => '4',
                'Donations' => '4',
                'Members' => '4',
            ],
        ]);
    }

    // Step 2: Verify OTP and login
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required',
        ]);

        // Find OTP
        $otpLog = OtpLog::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (! $otpLog) {
            return response()->json(['status' => false, 'error' => 'Invalid or expired OTP'], 401);
        }

        $otpLog->update(['is_verified' => true]);

        // Check temple record
        $temple = Temple::where('phone', $request->phone)->first();

        if ($temple) {
            // return response()->json(['error' => 'Temple not registered'], 404);

            // Issue Sanctum token
            $token = $temple->createToken('temple_token')->plainTextToken;

            // Default if no role found
            $roleData = [
                'User' => '4',
                'Role' => '4',
                'Pooja' => '4',
                'Settings' => '4',
                'Events' => '4',
                'Donations' => '4',
                'Members' => '4',
            ];

            return response()->json([
                'message' => 'Login successful',
                'temple' => $temple,
                'token' => $token,
                'role' => $roleData,
            ]);
        } else {

            // 3️⃣ If not a temple, check in members table (main DB)
            $member = \App\Models\Member::where('phone', $request->phone)->first();

            if ($member) {
                $token = $member->createToken('member_token')->plainTextToken;
                // 4️⃣ Fetch role for the member’s temple
                $role = Role::where('temple_id', $member->temple_id)->where('id', $member->role_id)->first();

                // Default if no role found
                $roleData = $role ? $role->role : [
                    'User' => '0',
                    'Role' => '0',
                    'Pooja' => '0',
                    'Settings' => '0',
                    'Events' => '0',
                    'Donations' => '0',
                    'Members' => '0',
                ];

                return response()->json([
                    'message' => 'Member login successful',
                    'login_as' => 'member',
                    'member' => $member,
                    'token' => $token,
                    'role' => $roleData,
                ]);
            }
        }

        // 4️⃣ Neither found
        return response()->json(['error' => 'No account found for this number'], 404);
    }

    // Logout
    public function logout(Request $request)
    {
        // $request->user()->currentAccessToken()->delete();
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
