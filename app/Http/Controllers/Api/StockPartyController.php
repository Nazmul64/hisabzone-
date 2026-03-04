<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockParty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockPartyController extends Controller
{
    // GET /stock/parties
    public function index(Request $request)
    {
        $query = StockParty::forUser(Auth::id());

        if ($request->type === 'supplier') {
            $query->suppliers();
        } elseif ($request->type === 'customer') {
            $query->customers();
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        $parties = $query->orderBy('name')->get();

        $summary = [
            'total_customers'  => StockParty::forUser(Auth::id())->customers()->count(),
            'total_suppliers'  => StockParty::forUser(Auth::id())->suppliers()->count(),
            'total_receivable' => StockParty::forUser(Auth::id())->customers()->where('balance', '>', 0)->sum('balance'),
            'total_payable'    => abs(StockParty::forUser(Auth::id())->suppliers()->where('balance', '<', 0)->sum('balance')),
        ];

        return response()->json([
            'data'    => $parties,
            'summary' => $summary,
        ]);
    }

    // POST /stock/parties
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'address'     => 'nullable|string|max:500',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_supplier' => 'required|boolean',
            'notes'       => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/stock'), $imageName);
            $validated['image_url'] = 'uploads/stock/' . $imageName;
        }

        $party = StockParty::create([
            'user_id'     => Auth::id(),
            'name'        => $validated['name'],
            'phone'       => $validated['phone'] ?? null,
            'email'       => $validated['email'] ?? null,
            'address'     => $validated['address'] ?? null,
            'image_url'   => $validated['image_url'] ?? null,
            'is_supplier' => $validated['is_supplier'],
            'notes'       => $validated['notes'] ?? null,
            'balance'     => 0,
        ]);

        return response()->json([
            'data'    => $party,
            'message' => 'Party created successfully',
        ], 201);
    }

    // GET /stock/parties/{id}
    public function show($id)
    {
        $party = StockParty::forUser(Auth::id())->findOrFail($id);
        $party->load(['saleInvoices', 'purchaseInvoices']);

        $summary = [
            'total_sale'     => $party->saleInvoices->sum('grand_total'),
            'total_purchase' => $party->purchaseInvoices->sum('grand_total'),
            'total_due'      => $party->is_supplier
                                    ? $party->purchaseInvoices->sum('due_amount')
                                    : $party->saleInvoices->sum('due_amount'),
        ];

        return response()->json([
            'data'    => $party,
            'summary' => $summary,
        ]);
    }

    // PUT /stock/parties/{id}
    public function update(Request $request, $id)
    {
        $party = StockParty::forUser(Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'notes'   => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image')) {
            if ($party->image_url && file_exists(public_path($party->image_url))) {
                unlink(public_path($party->image_url));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/stock'), $imageName);
            $validated['image_url'] = 'uploads/stock/' . $imageName;
        }

        $party->update($validated);

        return response()->json([
            'data'    => $party->fresh(),
            'message' => 'Party updated successfully',
        ]);
    }

    // DELETE /stock/parties/{id}
    public function destroy($id)
    {
        $party = StockParty::forUser(Auth::id())->findOrFail($id);

        if ($party->image_url && file_exists(public_path($party->image_url))) {
            unlink(public_path($party->image_url));
        }

        $party->delete();

        return response()->json(['message' => 'Party deleted successfully']);
    }

    // GET /stock/parties/{id}/ledger
    public function ledger($id)
    {
        $party = StockParty::forUser(Auth::id())->findOrFail($id);
        $party->load(['saleInvoices.items', 'purchaseInvoices.items']);

        $transactions = collect();

        if (!$party->is_supplier) {
            foreach ($party->saleInvoices as $inv) {
                $transactions->push([
                    'date'        => $inv->date,
                    'type'        => 'sale',
                    'invoice'     => $inv->invoice_number,
                    'debit'       => $inv->grand_total,
                    'credit'      => $inv->paid_amount,
                    'balance'     => $inv->due_amount,
                    'status'      => $inv->status,
                ]);
            }
        } else {
            foreach ($party->purchaseInvoices as $inv) {
                $transactions->push([
                    'date'        => $inv->date,
                    'type'        => 'purchase',
                    'invoice'     => $inv->invoice_number,
                    'debit'       => $inv->paid_amount,
                    'credit'      => $inv->grand_total,
                    'balance'     => $inv->due_amount,
                    'status'      => $inv->status,
                ]);
            }
        }

        return response()->json([
            'data'         => $party,
            'transactions' => $transactions->sortBy('date')->values(),
            'summary'      => [
                'total_debit'  => $transactions->sum('debit'),
                'total_credit' => $transactions->sum('credit'),
                'balance'      => $party->balance,
            ],
        ]);
    }
}
