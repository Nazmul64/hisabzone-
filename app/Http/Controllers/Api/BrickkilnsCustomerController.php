<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnsCustomerController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'search'  => 'nullable|string|max:100',
            'has_due' => 'nullable|in:true,false',
        ]);

        $q = Customer::withCount('sales');

        if ($req->filled('search')) {
            $term = $req->search;
            $q->where(function ($sub) use ($term) {
                $sub->where('name',   'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%");
            });
        }

        if ($req->has_due === 'true') {
            $q->where('due', '>', 0);
        }

        $customers = $q->orderBy('name')->get();

        $summary = [
            'total'     => $customers->count(),
            'with_due'  => $customers->where('due', '>', 0)->count(),
            'total_due' => (float) $customers->sum('due'),
        ];

        return $this->ok($customers);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'note'    => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return $this->created($customer, 'গ্রাহক যোগ করা হয়েছে');
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['sales' => fn ($q) => $q->latest()->limit(10)]);
        return $this->ok($customer);
    }

    public function update(Request $req, Customer $customer): JsonResponse
    {
        $validated = $req->validate([
            'name'    => 'sometimes|required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'note'    => 'nullable|string',
        ]);

        $customer->update($validated);

        return $this->ok($customer, 'আপডেট সফল হয়েছে');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return $this->ok(null, 'গ্রাহক মুছে ফেলা হয়েছে');
    }

    public function collectDue(Request $req, Customer $customer): JsonResponse
    {
        $req->validate(['amount' => 'required|numeric|min:0.01']);

        if ($customer->due <= 0) {
            return $this->fail('এই গ্রাহকের কোনো বকেয়া নেই');
        }

        $amount = min($req->amount, $customer->due);
        $customer->decrement('due', $amount);

        return $this->ok($customer->fresh(), '৳' . number_format($amount, 2) . ' আদায় হয়েছে');
    }
}
