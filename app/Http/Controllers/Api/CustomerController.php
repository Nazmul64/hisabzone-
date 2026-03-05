<?php
// app/Http/Controllers/Api/CustomerController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailorCustomer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function uid(Request $r): int { return $r->user()->id; }

    // GET /customers
    public function index(Request $request)
    {
        $q = $request->query('search');
        $customers = TailorCustomer::where('user_id', $this->uid($request))
            ->when($q, fn($query) => $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%$q%")
                   ->orWhere('phone', 'like', "%$q%");
            }))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $customers,
        ]);
    }

    // POST /customers
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'address' => 'nullable|string',
            'email'   => 'nullable|email',
            'notes'   => 'nullable|string',
        ]);

        $customer = TailorCustomer::create([
            ...$data,
            'user_id' => $this->uid($request),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'কাস্টমার যোগ করা হয়েছে',
            'data'    => $customer,
        ], 201);
    }

    // GET /customers/{id}
    public function show(Request $request, $id)
    {
        $customer = TailorCustomer::where('user_id', $this->uid($request))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $customer->load('orders'),
        ]);
    }

    // PUT /customers/{id}
    public function update(Request $request, $id)
    {
        $customer = TailorCustomer::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'address' => 'nullable|string',
            'email'   => 'nullable|email',
            'notes'   => 'nullable|string',
        ]);

        $customer->update($data);

        return response()->json([
            'success' => true,
            'message' => 'কাস্টমার আপডেট হয়েছে',
            'data'    => $customer,
        ]);
    }

    // DELETE /customers/{id}
    public function destroy(Request $request, $id)
    {
        $customer = TailorCustomer::where('user_id', $this->uid($request))
            ->findOrFail($id);

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'কাস্টমার মুছে ফেলা হয়েছে',
        ]);
    }

    // GET /customers/{id}/orders
    public function orders(Request $request, $id)
    {
        $customer = TailorCustomer::where('user_id', $this->uid($request))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $customer->orders()->orderByDesc('created_at')->get(),
        ]);
    }
}
