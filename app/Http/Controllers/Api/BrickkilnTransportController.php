<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transport;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnTransportController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'status'  => 'nullable|in:pending,ongoing,delivered',
            'sale_id' => 'nullable|integer',
        ]);

        $q = Transport::query();

        if ($req->filled('status'))  $q->where('status',  $req->status);
        if ($req->filled('sale_id')) $q->where('sale_id', $req->sale_id);

        $transports = $q->orderBy('date', 'desc')->get();

        return $this->ok($transports);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'sale_id'        => 'nullable|integer',
            'date'           => 'required|date',
            'vehicle_no'     => 'required|string|max:50',
            'driver_name'    => 'nullable|string|max:255',
            'driver_mobile'  => 'nullable|string|max:20',
            'fare'           => 'nullable|numeric|min:0',
            'destination'    => 'nullable|string|max:255',
            'brick_quantity' => 'nullable|integer|min:0',
            'status'         => 'nullable|in:pending,ongoing,delivered',
            'note'           => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        $transport = Transport::create($validated);

        return $this->created($transport, 'পরিবহন এন্ট্রি সংরক্ষিত হয়েছে');
    }

    public function show(Transport $transport): JsonResponse
    {
        return $this->ok($transport->load('sale'));
    }

    public function update(Request $req, Transport $transport): JsonResponse
    {
        $validated = $req->validate([
            'sale_id'        => 'nullable|integer',
            'date'           => 'nullable|date',
            'vehicle_no'     => 'nullable|string|max:50',
            'driver_name'    => 'nullable|string|max:255',
            'driver_mobile'  => 'nullable|string|max:20',
            'fare'           => 'nullable|numeric|min:0',
            'destination'    => 'nullable|string|max:255',
            'brick_quantity' => 'nullable|integer|min:0',
            'status'         => 'nullable|in:pending,ongoing,delivered',
            'note'           => 'nullable|string',
        ]);

        $transport->update($validated);

        return $this->ok($transport->fresh(), 'আপডেট সফল হয়েছে');
    }

    public function destroy(Transport $transport): JsonResponse
    {
        $transport->delete();
        return $this->ok(null, 'পরিবহন মুছে ফেলা হয়েছে');
    }

    public function updateStatus(Request $req, Transport $transport): JsonResponse
    {
        $req->validate([
            'status' => 'required|in:pending,ongoing,delivered',
        ]);

        $transport->update(['status' => $req->status]);

        return $this->ok($transport->fresh(), 'স্ট্যাটাস আপডেট হয়েছে');
    }
}
