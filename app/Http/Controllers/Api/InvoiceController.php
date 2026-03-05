<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TailorOrder;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, $orderId)
    {
        $order = TailorOrder::with('customer', 'payments')
            ->where('user_id', $request->user()->id)
            ->findOrFail($orderId);

        return response()->json(['success' => true, 'data' => [
            'invoice_no'   => 'INV-' . $order->order_number,
            'order'        => $order,
            'customer'     => $order->customer,
            'payments'     => $order->payments,
            'generated_at' => now()->toDateTimeString(),
        ]]);
    }
}
