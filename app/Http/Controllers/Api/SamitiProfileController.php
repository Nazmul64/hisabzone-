<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiProfileController extends Controller
{
    /**
     * সমিতির প্রোফাইল দেখাও
     * User table থেকে user info এবং SamitiProfile থেকে samiti info
     */
    public function show()
    {
        $user = Auth::user();

        $profile = SamitiProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name'                => 'আমাদের সমিতি',
                'reg_no'              => 'REG-' . date('Y') . '-001',
                'address'             => 'ঢাকা, বাংলাদেশ',
                'phone'               => $user->phone ?? '',
                'email'               => $user->email ?? '',
                'president'           => $user->name ?? '',
                'secretary'           => '',
                'treasurer'           => '',
                'weekly_rate'         => 500,
                'loan_rate'           => 10,
                'max_loan_multiplier' => 5,
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                // Samiti info
                'name'                => $profile->name,
                'reg_no'              => $profile->reg_no,
                'address'             => $profile->address,
                'phone'               => $profile->phone,
                'email'               => $profile->email,
                'president'           => $profile->president,
                'secretary'           => $profile->secretary,
                'treasurer'           => $profile->treasurer,
                'weekly_rate'         => $profile->weekly_rate,
                'loan_rate'           => $profile->loan_rate,
                'max_loan_multiplier' => $profile->max_loan_multiplier,
                // User info (User table থেকে)
                'user_name'           => $user->name,
                'user_email'          => $user->email,
                'user_phone'          => $user->phone ?? '',
            ],
        ]);
    }

    /**
     * সমিতির প্রোফাইল আপডেট করো
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'                => 'sometimes|string|max:255',
            'reg_no'              => 'sometimes|string|max:100',
            'address'             => 'sometimes|string|max:500',
            'phone'               => 'sometimes|string|max:20',
            'email'               => 'sometimes|email|max:255',
            'president'           => 'sometimes|string|max:255',
            'secretary'           => 'sometimes|string|max:255',
            'treasurer'           => 'sometimes|string|max:255',
            'weekly_rate'         => 'sometimes|numeric|min:0',
            'loan_rate'           => 'sometimes|numeric|min:0|max:100',
            'max_loan_multiplier' => 'sometimes|integer|min:1',
        ]);

        $profile = SamitiProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->only([
                'name', 'reg_no', 'address', 'phone', 'email',
                'president', 'secretary', 'treasurer',
                'weekly_rate', 'loan_rate', 'max_loan_multiplier',
            ])
        );

        return response()->json(['success' => true, 'data' => $profile]);
    }
}
