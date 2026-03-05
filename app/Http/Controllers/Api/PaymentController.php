<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailorPayment;
use App\Models\TailorOrder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private function uid(Request $r): int
    {
        return $r->user()->id;
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/tailor/payments
    // ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $payments = TailorPayment::with(['order', 'customer'])
            ->where('user_id', $this->uid($request))
            ->orderByDesc('payment_date')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $payments,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /api/tailor/payments
    // ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            // order_id nullable — salary payment এ real order নেই
            'order_id'     => 'nullable|string',
            'customer_id'  => 'required|string|exists:tailor_customers,id',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:cash,bkash,nagad,bank,rocket',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $isSalary = isset($data['order_id']) &&
                    str_starts_with((string) $data['order_id'], 'SALARY-');

        $payment = TailorPayment::create([
            'user_id'      => $this->uid($request),
            'order_id'     => $isSalary ? null : ($data['order_id'] ?? null),
            'customer_id'  => $data['customer_id'],
            'amount'       => $data['amount'],
            'method'       => $data['method'],
            'payment_date' => $data['payment_date'],
            'notes'        => $data['notes'] ?? null,
            'type'         => $isSalary ? 'salary' : 'order',
        ]);

        // Regular order payment হলে paid_amount আপডেট করো
        if (!$isSalary && !empty($data['order_id'])) {
            TailorOrder::where('user_id', $this->uid($request))
                ->where('id', $data['order_id'])
                ->increment('paid_amount', $data['amount']);
        }

        return response()->json([
            'success' => true,
            'message' => $isSalary ? 'বেতন দেওয়া হয়েছে' : 'পেমেন্ট নেওয়া হয়েছে',
            'data'    => $payment->fresh(),
        ], 201);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/tailor/payments/{id}
    // ─────────────────────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $payment = TailorPayment::with(['order', 'customer'])
            ->where('user_id', $this->uid($request))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $payment,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // PUT /api/tailor/payments/{id}
    // ─────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $payment = TailorPayment::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $data = $request->validate([
            'amount'       => 'sometimes|numeric|min:0.01',
            'method'       => 'sometimes|in:cash,bkash,nagad,bank,rocket',
            'payment_date' => 'sometimes|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $payment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'আপডেট হয়েছে',
            'data'    => $payment->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // DELETE /api/tailor/payments/{id}
    // ─────────────────────────────────────────────────────────
    public function destroy(Request $request, $id)
    {
        $payment = TailorPayment::where('user_id', $this->uid($request))
            ->findOrFail($id);

        // Regular order payment হলে paid_amount কমাও
        if ($payment->order_id && $payment->type !== 'salary') {
            TailorOrder::where('user_id', $this->uid($request))
                ->where('id', $payment->order_id)
                ->decrement('paid_amount', $payment->amount);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'মুছে ফেলা হয়েছে',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/tailor/payments/due/orders
    // ─────────────────────────────────────────────────────────
    public function dueOrders(Request $request)
    {
        $orders = TailorOrder::with('customer')
            ->where('user_id', $this->uid($request))
            ->where('status', '!=', 'delivered')
            ->whereRaw('paid_amount < price')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }
}
