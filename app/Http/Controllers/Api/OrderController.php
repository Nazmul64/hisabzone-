<?php
// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailorOrder;
use App\Models\TailorCustomer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function uid(Request $r): int { return $r->user()->id; }

    // GET /orders?status=&customer_id=
    public function index(Request $request)
    {
        $query = TailorOrder::with('customer')
            ->where('user_id', $this->uid($request));

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->date) {
            $query->whereDate('delivery_date', $request->date);
        }

        $orders = $query->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    // POST /orders
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'       => 'required|exists:tailor_customers,id',
            'cloth_type'        => 'required|string',
            'quantity'          => 'required|integer|min:1',
            'price'             => 'required|numeric|min:0',
            'paid_amount'       => 'nullable|numeric|min:0',
            'delivery_date'     => 'required|date',
            'assigned_employee' => 'nullable|string',
            'measurements'      => 'nullable|array',
            'notes'             => 'nullable|string',
        ]);

        // Verify customer belongs to user
        TailorCustomer::where('user_id', $this->uid($request))
                       ->findOrFail($data['customer_id']);

        $order = TailorOrder::create([
            ...$data,
            'user_id'      => $this->uid($request),
            'order_number' => TailorOrder::generateOrderNumber(),
            'order_date'   => today(),
            'paid_amount'  => $data['paid_amount'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'অর্ডার তৈরি হয়েছে',
            'data'    => $order->load('customer'),
        ], 201);
    }

    // GET /orders/{id}
    public function show(Request $request, $id)
    {
        $order = TailorOrder::with('customer', 'payments')
            ->where('user_id', $this->uid($request))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    // PUT /orders/{id}
    public function update(Request $request, $id)
    {
        $order = TailorOrder::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $data = $request->validate([
            'cloth_type'        => 'sometimes|string',
            'quantity'          => 'sometimes|integer|min:1',
            'price'             => 'sometimes|numeric|min:0',
            'paid_amount'       => 'sometimes|numeric|min:0',
            'delivery_date'     => 'sometimes|date',
            'status'            => 'sometimes|in:pending,cutting,sewing,ironing,ready,delivered',
            'assigned_employee' => 'nullable|string',
            'measurements'      => 'nullable|array',
            'notes'             => 'nullable|string',
        ]);

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'অর্ডার আপডেট হয়েছে',
            'data'    => $order->load('customer'),
        ]);
    }

    // DELETE /orders/{id}
    public function destroy(Request $request, $id)
    {
        $order = TailorOrder::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'অর্ডার মুছে ফেলা হয়েছে',
        ]);
    }

    // PATCH /orders/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,cutting,sewing,ironing,ready,delivered',
        ]);

        $order = TailorOrder::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $order->update(['status' => $data['status']]);

        return response()->json([
            'success' => true,
            'message' => 'স্ট্যাটাস আপডেট হয়েছে',
            'data'    => $order,
        ]);
    }

    // PATCH /orders/{id}/measurements
    public function updateMeasurements(Request $request, $id)
    {
        $data = $request->validate([
            'measurements' => 'required|array',
        ]);

        $order = TailorOrder::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $existing     = $order->measurements ?? [];
        $merged       = array_merge($existing, $data['measurements']);
        $order->update(['measurements' => $merged]);

        return response()->json([
            'success' => true,
            'message' => 'মাপ সেভ হয়েছে',
            'data'    => $order,
        ]);
    }
}
