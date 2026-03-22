<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockPayment;
use App\Models\SaleInvoice;
use App\Models\PurchaseInvoice;
use App\Models\StockParty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockPaymentController extends Controller
{
    // ════════════════════════════════════════════════
    // INDEX — সব payment list
    // GET /api/stock/payments
    // ════════════════════════════════════════════════
    public function index(Request $request): JsonResponse
    {
        $query = StockPayment::forUser(Auth::id())->orderByDesc('date');

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [
                $request->date_from,
                $request->date_to,
            ]);
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

    // ════════════════════════════════════════════════
    // STORE — নতুন payment তৈরি
    // POST /api/stock/payments
    // ════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
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
            $payment = StockPayment::create(
                array_merge($validated, ['user_id' => Auth::id()])
            );

            if ($validated['payment_type'] === 'sale') {
                $invoice = SaleInvoice::find($validated['invoice_id']);
                if ($invoice) {
                    $invoice->increment('paid_amount', $validated['amount']);
                    $invoice->decrement('due_amount',  $validated['amount']);
                    $invoice->update([
                        'status' => $invoice->fresh()->due_amount <= 0
                            ? 'paid' : 'partial',
                    ]);
                    if ($invoice->customer_id) {
                        StockParty::where('id', $invoice->customer_id)
                            ->decrement('balance', $validated['amount']);
                    }
                }
            } else {
                $invoice = PurchaseInvoice::find($validated['invoice_id']);
                if ($invoice) {
                    $invoice->increment('paid_amount', $validated['amount']);
                    $invoice->decrement('due_amount',  $validated['amount']);
                    $invoice->update([
                        'status' => $invoice->fresh()->due_amount <= 0
                            ? 'paid' : 'partial',
                    ]);
                    if ($invoice->supplier_id) {
                        StockParty::where('id', $invoice->supplier_id)
                            ->increment('balance', $validated['amount']);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data'    => $payment,
                'message' => 'Payment recorded successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ════════════════════════════════════════════════
    // UPDATE — payment edit
    // PUT /api/stock/payments/{id}
    // ════════════════════════════════════════════════
    public function update(Request $request, $id): JsonResponse
    {
        $payment = StockPayment::forUser(Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'payment_method' => 'sometimes|string',
            'amount'         => 'sometimes|numeric|min:0.01',
            'date'           => 'sometimes|date',
            'notes'          => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // ── Amount পরিবর্তন হলে invoice + party balance আপডেট ──
            if (isset($validated['amount'])) {
                $oldAmount = (float) $payment->amount;
                $newAmount = (float) $validated['amount'];
                // float precision issue এড়াতে round ব্যবহার
                $diff      = round($newAmount - $oldAmount, 4);

                if (abs($diff) > 0.0001) {
                    if ($payment->payment_type === 'sale') {
                        $invoice = SaleInvoice::find($payment->invoice_id);
                        if ($invoice) {
                            $invoice->increment('paid_amount', $diff);
                            $invoice->decrement('due_amount',  $diff);
                            $fresh = $invoice->fresh();
                            $invoice->update([
                                'status' => $fresh->due_amount <= 0
                                    ? 'paid' : 'partial',
                            ]);
                            // ✅ StockParty balance update (sale = customer balance কমে)
                            if ($invoice->customer_id) {
                                if ($diff > 0) {
                                    StockParty::where('id', $invoice->customer_id)
                                        ->decrement('balance', $diff);
                                } else {
                                    StockParty::where('id', $invoice->customer_id)
                                        ->increment('balance', abs($diff));
                                }
                            }
                        }
                    } else {
                        $invoice = PurchaseInvoice::find($payment->invoice_id);
                        if ($invoice) {
                            $invoice->increment('paid_amount', $diff);
                            $invoice->decrement('due_amount',  $diff);
                            $fresh = $invoice->fresh();
                            $invoice->update([
                                'status' => $fresh->due_amount <= 0
                                    ? 'paid' : 'partial',
                            ]);
                            // ✅ StockParty balance update (purchase = supplier balance বাড়ে)
                            if ($invoice->supplier_id) {
                                if ($diff > 0) {
                                    StockParty::where('id', $invoice->supplier_id)
                                        ->increment('balance', $diff);
                                } else {
                                    StockParty::where('id', $invoice->supplier_id)
                                        ->decrement('balance', abs($diff));
                                }
                            }
                        }
                    }
                }
            }

            $payment->update($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $payment->fresh(),
                'message' => 'Payment updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ════════════════════════════════════════════════
    // DESTROY — payment delete + balance reverse
    // DELETE /api/stock/payments/{id}
    // ════════════════════════════════════════════════
    public function destroy($id): JsonResponse
    {
        $payment = StockPayment::forUser(Auth::id())->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($payment->payment_type === 'sale') {
                $invoice = SaleInvoice::find($payment->invoice_id);
                if ($invoice) {
                    $invoice->decrement('paid_amount', $payment->amount);
                    $invoice->increment('due_amount',  $payment->amount);
                    $fresh = $invoice->fresh();
                    $invoice->update([
                        'status' => $fresh->paid_amount <= 0
                            ? 'pending' : 'partial',
                    ]);
                    if ($invoice->customer_id) {
                        StockParty::where('id', $invoice->customer_id)
                            ->increment('balance', $payment->amount);
                    }
                }
            } else {
                $invoice = PurchaseInvoice::find($payment->invoice_id);
                if ($invoice) {
                    $invoice->decrement('paid_amount', $payment->amount);
                    $invoice->increment('due_amount',  $payment->amount);
                    $fresh = $invoice->fresh();
                    $invoice->update([
                        'status' => $fresh->paid_amount <= 0
                            ? 'pending' : 'partial',
                    ]);
                    if ($invoice->supplier_id) {
                        StockParty::where('id', $invoice->supplier_id)
                            ->decrement('balance', $payment->amount);
                    }
                }
            }

            $payment->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
