<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockPayment;
use App\Models\SaleInvoice;
use App\Models\PurchaseInvoice;
use App\Models\StockParty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockPayment::forUser(Auth::id())->orderByDesc('date');

        if ($request->filled('payment_type')) $query->where('payment_type', $request->payment_type);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $payments = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $payments,
            'summary' => [
                'total_received' => $payments->where('payment_type', 'sale')->sum('amount'),
                'total_paid'     => $payments->where('payment_type', 'purchase')->sum('amount'),
            ],
        ]);
    }

    // payment_type: 'sale' or 'purchase'
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'     => 'required|string',
            'invoice_number' => 'required|string',
            'payment_type'   => 'required|in:sale,purchase',
            'payment_method' => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'date'           => 'required|date',
            'notes'          => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $payment = StockPayment::create(array_merge($validated, ['user_id' => Auth::id()]));

            if ($validated['payment_type'] === 'sale') {
                $invoice = SaleInvoice::find($validated['invoice_id']);
                if ($invoice) {
                    $invoice->increment('paid_amount', $validated['amount']);
                    $invoice->decrement('due_amount', $validated['amount']);
                    $invoice->update(['status' => $invoice->due_amount <= 0 ? 'paid' : 'partial']);
                    if ($invoice->customer_id) {
                        StockParty::where('id', $invoice->customer_id)->decrement('balance', $validated['amount']);
                    }
                }
            } else {
                $invoice = PurchaseInvoice::find($validated['invoice_id']);
                if ($invoice) {
                    $invoice->increment('paid_amount', $validated['amount']);
                    $invoice->decrement('due_amount', $validated['amount']);
                    $invoice->update(['status' => $invoice->due_amount <= 0 ? 'paid' : 'partial']);
                    if ($invoice->supplier_id) {
                        StockParty::where('id', $invoice->supplier_id)->increment('balance', $validated['amount']);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $payment, 'message' => 'Payment recorded successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $payment = StockPayment::forUser(Auth::id())->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($payment->payment_type === 'sale') {
                $invoice = SaleInvoice::find($payment->invoice_id);
                if ($invoice) {
                    $invoice->decrement('paid_amount', $payment->amount);
                    $invoice->increment('due_amount', $payment->amount);
                    $invoice->update(['status' => $invoice->paid_amount <= 0 ? 'pending' : 'partial']);
                    if ($invoice->customer_id) {
                        StockParty::where('id', $invoice->customer_id)->increment('balance', $payment->amount);
                    }
                }
            } else {
                $invoice = PurchaseInvoice::find($payment->invoice_id);
                if ($invoice) {
                    $invoice->decrement('paid_amount', $payment->amount);
                    $invoice->increment('due_amount', $payment->amount);
                    $invoice->update(['status' => $invoice->paid_amount <= 0 ? 'pending' : 'partial']);
                    if ($invoice->supplier_id) {
                        StockParty::where('id', $invoice->supplier_id)->decrement('balance', $payment->amount);
                    }
                }
            }

            $payment->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
