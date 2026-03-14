<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyPrescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyPrescriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $prescriptions = PharmacyPrescription::where('user_id', auth()->id())
            ->with('customer')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $prescriptions,
            'summary' => [
                'total'   => $prescriptions->count(),
                'pending' => $prescriptions->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'  => 'nullable|exists:pharmacy_customers,id',
            'patient_name' => 'required|string|max:255',
            'doctor_name'  => 'nullable|string|max:255',
            'date'         => 'required|date',
            'medicines'    => 'nullable|array',
            'medicines.*'  => 'string|max:255',
            'status'       => 'nullable|in:pending,completed',
            'note'         => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        // support both 'note' and 'notes' key
        if (empty($validated['note']) && !empty($validated['notes'])) {
            $validated['note'] = $validated['notes'];
        }
        unset($validated['notes']);

        $prescription = PharmacyPrescription::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'status'  => $validated['status'] ?? 'pending',
        ]));

        return response()->json([
            'success' => true,
            'data'    => $prescription->load('customer'),
            'message' => 'Prescription saved successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $prescription = PharmacyPrescription::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'patient_name' => 'sometimes|required|string|max:255',
            'doctor_name'  => 'nullable|string|max:255',
            'date'         => 'sometimes|required|date',
            'status'       => 'nullable|in:pending,completed',
            'note'         => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        if (empty($validated['note']) && !empty($validated['notes'])) {
            $validated['note'] = $validated['notes'];
        }
        unset($validated['notes']);

        $prescription->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $prescription->fresh()->load('customer'),
            'message' => 'Prescription updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $prescription = PharmacyPrescription::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prescription deleted successfully',
        ]);
    }
}
