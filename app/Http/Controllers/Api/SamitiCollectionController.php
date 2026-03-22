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
    // ─────────────────────────────────────────────
    // GET /api/samiti/collections
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $userId = Auth::id();
        $week   = (int) $request->get('week',  1);
        $month  = (int) $request->get('month', now()->month);
        $year   = (int) $request->get('year',  now()->year);

        // সমিতি প্রোফাইল থেকে সাপ্তাহিক পরিমাণ
        $profile      = SamitiProfile::where('user_id', $userId)->first();
        $weeklyAmount = (float) ($profile?->weekly_rate ?? 500); // ✅ float cast

        // Active সদস্যদের জন্য collection auto-create
        $members = SamitiMember::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

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

        // Collection load করো — map এ সব type ✅ cast
        $rows = SamitiCollection::where('user_id', $userId)
            ->where('week_number', $week)
            ->where('month', $month)
            ->where('year', $year)
            ->with('member')
            ->get();

        $items = $rows->map(fn($c) => [
            'id'             => $c->id,
            'member_name'    => $c->member->name ?? '',
            'weekly_amount'  => (float) $c->amount,        // ✅ float
            'is_collected'   => (bool)  $c->is_collected,  // ✅ bool
            'collected_date' => $c->collected_date?->format('Y-m-d'),
        ]);

        // ✅ DB query দিয়ে sum করো — map করা collection এ না
        $totalTarget = (float) $rows->sum('amount');
        $totalCollected = (float) $rows->where('is_collected', true)->sum('amount');
        $totalPending   = $totalTarget - $totalCollected;

        return response()->json([
            'success' => true,
            'data'    => [
                'items'           => $items->values(),
                'total_target'    => $totalTarget,    // ✅ always float
                'total_collected' => $totalCollected, // ✅ always float
                'total_pending'   => $totalPending,   // ✅ always float
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // PATCH /api/samiti/collections/{id}/toggle
    // ─────────────────────────────────────────────
    public function toggle(string $id)
    {
        $col = SamitiCollection::where('user_id', Auth::id())
            ->findOrFail($id);

        $col->is_collected   = ! $col->is_collected;
        $col->collected_date = $col->is_collected
            ? now()->toDateString()
            : null;
        $col->save();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $col->id,
                'is_collected'   => (bool)  $col->is_collected,
                'collected_date' => $col->collected_date,
                'weekly_amount'  => (float) $col->amount,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // POST /api/samiti/collections/collect-all
    // ─────────────────────────────────────────────
    public function collectAll(Request $request)
    {
        $userId = Auth::id();
        $week   = (int) $request->get('week',  1);
        $month  = (int) $request->get('month', now()->month);
        $year   = (int) $request->get('year',  now()->year);

        SamitiCollection::where('user_id', $userId)
            ->where('week_number', $week)
            ->where('month',       $month)
            ->where('year',        $year)
            ->where('is_collected', false)
            ->update([
                'is_collected'   => true,
                'collected_date' => now()->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'সকলের কিস্তি সংগ্রহ করা হয়েছে',
        ]);
    }
}
