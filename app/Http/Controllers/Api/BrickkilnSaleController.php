<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Sale;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnSaleController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'status'      => 'nullable|in:paid,due,partial',
            'brick_type'  => 'nullable|in:standard,premium,export',
            'customer_id' => 'nullable|integer',
        ]);

        $q = Sale::with('customer');

        if ($req->filled('date_from'))   $q->whereDate('date', '>=', $req->date_from);
        if ($req->filled('date_to'))     $q->whereDate('date', '<=', $req->date_to);
        if ($req->filled('status'))      $q->where('status', $req->status);
        if ($req->filled('brick_type'))  $q->where('brick_type', $req->brick_type);
        if ($req->filled('customer_id')) $q->where('customer_id', $req->customer_id);

        $sales = $q->orderBy('date', 'desc')->get();

        $summary = [
            'total_sales' => (float) $sales->sum('total'),
            'total_paid'  => (float) $sales->sum('paid_amount'),
            'total_due'   => (float) $sales->sum('due_amount'),
            'count'       => $sales->count(),
        ];

        return $this->ok($sales);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'date'               => 'required|date',
            'customer_id'        => 'nullable|integer',
            'customer_name'      => 'nullable|string|max:255',
            'quantity'           => 'required|integer|min:1',
            'price_per_thousand' => 'required|numeric|min:0',
            'paid_amount'        => 'nullable|numeric|min:0',
            'brick_type'         => 'required|in:standard,premium,export',
            'note'               => 'nullable|string',
            'status'             => 'nullable|in:paid,due,partial',
            'total'              => 'nullable|numeric|min:0',
        ]);

        $validated['total']       = ($validated['quantity'] / 1000) * $validated['price_per_thousand'];
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['invoice_no']  = 'INV-' . strtoupper(uniqid());

        if (!empty($validated['customer_id']) && empty($validated['customer_name'])) {
            $validated['customer_name'] = Customer::find($validated['customer_id'])?->name;
        }

        $sale = Sale::create($validated);

        // Inventory update
        $uid = auth()->id();
        Inventory::withoutGlobalScope('user')
                 ->where('user_id', $uid)
                 ->where('brick_type', $validated['brick_type'])
                 ->increment('sold', $validated['quantity']);
        Inventory::withoutGlobalScope('user')
                 ->where('user_id', $uid)
                 ->where('brick_type', $validated['brick_type'])
                 ->decrement('available', $validated['quantity']);

        // Customer due update
        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
            $customer?->increment('total_amount', $validated['total']);
            if ($sale->due_amount > 0) {
                $customer?->increment('due', $sale->due_amount);
            }
        }

        return $this->created($sale->load('customer'), 'বিক্রয় এন্ট্রি সফলভাবে সংরক্ষিত হয়েছে');
    }

    public function show(Sale $sale): JsonResponse
    {
        return $this->ok($sale->load(['customer', 'transport']));
    }

    public function update(Request $req, Sale $sale): JsonResponse
    {
        $validated = $req->validate([
            'date'               => 'nullable|date',
            'customer_name'      => 'nullable|string|max:255',
            'quantity'           => 'nullable|integer|min:1',
            'price_per_thousand' => 'nullable|numeric|min:0',
            'paid_amount'        => 'nullable|numeric|min:0',
            'brick_type'         => 'nullable|in:standard,premium,export',
            'status'             => 'nullable|in:paid,due,partial',
            'note'               => 'nullable|string',
            'total'              => 'nullable|numeric|min:0',
        ]);

        // Recalculate total if quantity or price changed
        if (isset($validated['quantity']) || isset($validated['price_per_thousand'])) {
            $qty   = $validated['quantity']           ?? $sale->quantity;
            $price = $validated['price_per_thousand'] ?? $sale->price_per_thousand;
            $validated['total'] = ($qty / 1000) * $price;
        }

        $sale->update($validated);

        return $this->ok($sale->fresh(), 'আপডেট সফল হয়েছে');
    }

    public function destroy(Sale $sale): JsonResponse
    {
        $sale->delete();
        return $this->ok(null, 'বিক্রয় এন্ট্রি মুছে ফেলা হয়েছে');
    }

    public function pay(Request $req, Sale $sale): JsonResponse
    {
        $req->validate(['amount' => 'required|numeric|min:0.01']);

        $amount = min($req->amount, $sale->due_amount);

        $sale->increment('paid_amount', $amount);
        $sale->decrement('due_amount',  $amount);

        $fresh = $sale->fresh();
        $fresh->update(
            $fresh->due_amount <= 0
                ? ['status' => 'paid', 'due_amount' => 0]
                : ['status' => 'partial']
        );

        if ($sale->customer_id) {
            Customer::withoutGlobalScope('user')
                    ->find($sale->customer_id)
                    ?->decrement('due', $amount);
        }

        return $this->ok($fresh, 'পেমেন্ট গ্রহণ করা হয়েছে');
    }
}
