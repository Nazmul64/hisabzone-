<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiCollection;
use App\Models\SamitiMember;
use App\Models\SamitiProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiCollectionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $week   = (int) $request->get('week', 1);
        $month  = (int) $request->get('month', now()->month);
        $year   = (int) $request->get('year', now()->year);

        // সমিতির প্রোফাইল থেকে সাপ্তাহিক পরিমাণ নাও
        $profile      = SamitiProfile::where('user_id', $userId)->first();
        $weeklyAmount = $profile?->weekly_rate ?? 500;

        // সব active সদস্যের জন্য collection record auto-create
        $members = SamitiMember::where('user_id', $userId)->where('status', 'active')->get();
        foreach ($members as $member) {
            SamitiCollection::firstOrCreate(
                [
                    'user_id'          => $userId,
                    'samiti_member_id' => $member->id,
                    'week_number'      => $week,
                    'month'            => $month,
                    'year'             => $year,
                ],
                [
                    'amount'       => $weeklyAmount,
                    'is_collected' => false,
                ]
            );
        }

        $items = SamitiCollection::where('user_id', $userId)
            ->where('week_number', $week)
            ->where('month', $month)
            ->where('year', $year)
            ->with('member')
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'member_name'    => $c->member->name ?? '',
                'weekly_amount'  => $c->amount,
                'is_collected'   => $c->is_collected,
                'collected_date' => $c->collected_date?->format('Y-m-d'),
            ]);

        $totalTarget    = $items->sum('weekly_amount');
        $totalCollected = $items->where('is_collected', true)->sum('weekly_amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'items'           => $items->values(),
                'total_target'    => $totalTarget,
                'total_collected' => $totalCollected,
                'total_pending'   => $totalTarget - $totalCollected,
            ],
        ]);
    }

    public function toggle(string $id)
    {
        $col               = SamitiCollection::where('user_id', Auth::id())->findOrFail($id);
        $col->is_collected = !$col->is_collected;
        $col->collected_date = $col->is_collected ? now()->toDateString() : null;
        $col->save();

        return response()->json(['success' => true, 'data' => $col]);
    }

    public function collectAll(Request $request)
    {
        $userId = Auth::id();
        $week   = (int) $request->get('week', 1);
        $month  = (int) $request->get('month', now()->month);
        $year   = (int) $request->get('year', now()->year);

        SamitiCollection::where('user_id', $userId)
            ->where('week_number', $week)
            ->where('month', $month)
            ->where('year', $year)
            ->where('is_collected', false)
            ->update([
                'is_collected'   => true,
                'collected_date' => now()->toDateString(),
            ]);

        return response()->json(['success' => true, 'message' => 'All collected']);
    }
}
