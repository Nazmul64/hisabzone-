<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailorCustomer;
use App\Models\TailorEmployee;
use App\Models\TailorInventory;
use App\Models\TailorOrder;
use App\Models\TailorPayment;
use Illuminate\Http\Request;

class TailorReportController extends Controller
{
      private function uid(Request $r): int { return $r->user()->id; }

    public function summary(Request $request)
    {
        $uid = $this->uid($request);
        $clothCounts = TailorOrder::where('user_id', $uid)
            ->selectRaw('cloth_type, SUM(quantity) as total')
            ->groupBy('cloth_type')->get()
            ->pluck('total', 'cloth_type');

        return response()->json(['success' => true, 'data' => [
            'total_orders'    => TailorOrder::where('user_id', $uid)->count(),
            'total_income'    => TailorPayment::where('user_id', $uid)->sum('amount'),
            'total_due'       => TailorOrder::where('user_id', $uid)->selectRaw('SUM(price-paid_amount)as d')->value('d'),
            'total_customers' => TailorCustomer::where('user_id', $uid)->count(),
            'total_employees' => TailorEmployee::where('user_id', $uid)->count(),
            'inventory_count' => TailorInventory::where('user_id', $uid)->count(),
            'cloth_counts'    => $clothCounts,
        ]]);
    }

    public function sales(Request $request)
    {
        $uid = $this->uid($request);
        $byMethod = TailorPayment::where('user_id', $uid)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')->get()
            ->pluck('total', 'method');

        return response()->json(['success' => true, 'data' => [
            'by_method' => $byMethod,
            'recent'    => TailorPayment::with('customer','order')->where('user_id', $uid)
                            ->orderByDesc('payment_date')->take(20)->get(),
        ]]);
    }

    public function orders(Request $request)
    {
        $uid = $this->uid($request);
        $byStatus = TailorOrder::where('user_id', $uid)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->get()
            ->pluck('count', 'status');

        return response()->json(['success' => true, 'data' => [
            'by_status' => $byStatus,
            'all'       => TailorOrder::with('customer')->where('user_id', $uid)
                            ->orderByDesc('created_at')->get(),
        ]]);
    }

    public function customers(Request $request)
    {
        $uid = $this->uid($request);
        $customers = TailorCustomer::where('user_id', $uid)->get()->map(function ($c) {
            return [
                ...$c->toArray(),
                'order_count' => $c->orders()->count(),
                'total_spent' => $c->orders()->sum('price'),
            ];
        })->sortByDesc('total_spent')->values();

        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function monthly(Request $request)
    {
        $uid   = $this->uid($request);
        $month = $request->input('month', now()->month);
        $year  = $request->input('year',  now()->year);

        return response()->json(['success' => true, 'data' => [
            'orders'  => TailorOrder::where('user_id', $uid)
                            ->whereYear('order_date', $year)->whereMonth('order_date', $month)
                            ->count(),
            'income'  => TailorPayment::where('user_id', $uid)
                            ->whereYear('payment_date', $year)->whereMonth('payment_date', $month)
                            ->sum('amount'),
        ]]);
    }
}
