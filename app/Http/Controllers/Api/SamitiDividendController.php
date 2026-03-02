<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiDividend;
use App\Models\SamitiMember;
use App\Models\SamitiSaving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiDividendController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $year   = (int) $request->get('year', now()->year);

        $dividends = SamitiDividend::where('user_id', $userId)
            ->where('year', $year)
            ->with('member')
            ->get()
            ->map(fn($d) => [
                'id'               => $d->id,
                'member_name'      => $d->member->name ?? '',
                'total_savings'    => $d->total_savings,
                'dividend_percent' => $d->dividend_percent,
                'dividend_amount'  => $d->dividend_amount,
                'is_distributed'   => $d->is_distributed,
                'distributed_date' => $d->distributed_date?->format('Y-m-d'),
            ]);

        $totalDividend     = $dividends->sum('dividend_amount');
        $totalDistributed  = $dividends->where('is_distributed', true)->sum('dividend_amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'dividends'         => $dividends->values(),
                'total_dividend'    => $totalDividend,
                'total_distributed' => $totalDistributed,
                'pending_amount'    => $totalDividend - $totalDistributed,
            ],
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'year'        => 'required|integer',
            'profit_pool' => 'required|numeric|min:0.01',
        ]);

        $userId     = Auth::id();
        $year       = $request->year;
        $profitPool = $request->profit_pool;

        // সব active সদস্যের সঞ্চয় হিসাব করো
        $members = SamitiMember::where('user_id', $userId)->where('status', 'active')->get();

        $membersWithSavings = $members->map(function ($m) use ($userId, $year) {
            $deposit  = SamitiSaving::where('user_id', $userId)
                ->where('samiti_member_id', $m->id)
                ->where('is_deposit', true)
                ->whereYear('date', '<=', $year)
                ->sum('amount');
            $withdraw = SamitiSaving::where('user_id', $userId)
                ->where('samiti_member_id', $m->id)
                ->where('is_deposit', false)
                ->whereYear('date', '<=', $year)
                ->sum('amount');
            return ['member' => $m, 'savings' => max(0, $deposit - $withdraw)];
        })->filter(fn($m) => $m['savings'] > 0);

        $totalSavings = $membersWithSavings->sum('savings');

        if ($totalSavings == 0) {
            return response()->json(['success' => false, 'message' => 'No savings found'], 422);
        }

        // ওই বছরের আগের dividend মুছে দাও
        SamitiDividend::where('user_id', $userId)->where('year', $year)->delete();

        $count = 0;
        foreach ($membersWithSavings as $item) {
            $percent        = ($item['savings'] / $totalSavings) * 100;
            $dividendAmount = ($percent / 100) * $profitPool;
            $count++;

            SamitiDividend::create([
                'user_id'          => $userId,
                'samiti_member_id' => $item['member']->id,
                'dividend_id'      => 'DIV-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT),
                'year'             => $year,
                'total_savings'    => $item['savings'],
                'dividend_percent' => round($percent, 4),
                'dividend_amount'  => round($dividendAmount, 2),
                'profit_pool'      => $profitPool,
                'is_distributed'   => false,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Dividends calculated for ' . $count . ' members']);
    }

    public function distributeAll(Request $request)
    {
        $userId = Auth::id();
        $year   = (int) $request->get('year', now()->year);

        SamitiDividend::where('user_id', $userId)
            ->where('year', $year)
            ->where('is_distributed', false)
            ->update([
                'is_distributed'   => true,
                'distributed_date' => now()->toDateString(),
            ]);

        return response()->json(['success' => true, 'message' => 'All dividends distributed']);
    }

    public function toggle(string $id)
    {
        $dividend                   = SamitiDividend::where('user_id', Auth::id())->findOrFail($id);
        $dividend->is_distributed   = !$dividend->is_distributed;
        $dividend->distributed_date = $dividend->is_distributed ? now()->toDateString() : null;
        $dividend->save();

        return response()->json(['success' => true, 'data' => $dividend]);
    }
}
