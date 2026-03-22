<?php
// ════════════════════════════════════════════════════════════
//  app/Http/Controllers/Api/SamitiFineController.php
//  ✅ store() — fine_id পাঠানো হচ্ছে না, তাই 500 ছিল। ঠিক করা হয়েছে।
//  ✅ update() — PUT, নতুন record হবে না
//  ✅ try-catch — 500 হলে message দেখাবে
// ════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiFine;
use App\Models\SamitiMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SamitiFineController extends Controller
{
    // ══════════════════════════════════════════
    //  GET /api/samiti/fines
    // ══════════════════════════════════════════
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $fines = SamitiFine::where('user_id', $userId)
                ->with('member')
                ->latest()
                ->get()
                ->map(fn($f) => $this->formatFine($f));

            return response()->json([
                'success' => true,
                'data'    => [
                    'fines'         => $fines,
                    'total_fines'   => (float) SamitiFine::where('user_id', $userId)->sum('amount'),
                    'total_paid'    => (float) SamitiFine::where('user_id', $userId)->where('is_paid', true)->sum('amount'),
                    'total_pending' => (float) SamitiFine::where('user_id', $userId)->where('is_paid', false)->sum('amount'),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('SamitiFine index: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════
    //  POST /api/samiti/fines
    //  ✅ 500 fix: fine_id পাঠানো হচ্ছে না → সরানো হয়েছে
    // ══════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'samiti_member_id' => 'required|integer|min:1',
                'reason'           => 'required|string|max:255',
                'amount'           => 'required|numeric|min:0.01',
                'date'             => 'nullable|date',
            ]);

            $userId = Auth::id();

            // ✅ member এই user এর কিনা চেক
            $member = SamitiMember::where('id', $validated['samiti_member_id'])
                ->where('user_id', $userId)
                ->first();

            if (! $member) {
                return response()->json([
                    'success' => false,
                    'message' => 'সদস্য পাওয়া যায়নি',
                ], 404);
            }

            $fine = SamitiFine::create([
                'user_id'          => $userId,
                'samiti_member_id' => $validated['samiti_member_id'],
                'reason'           => $validated['reason'],
                'amount'           => $validated['amount'],
                'date'             => $validated['date'] ?? now()->toDateString(),
                'is_paid'          => false,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $this->formatFine($fine->load('member')),
                'message' => 'জরিমানা আরোপ হয়েছে ✅',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SamitiFine store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'সংরক্ষণ ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════
    //  PUT /api/samiti/fines/{id}
    //  ✅ Edit → নতুন record হবে না
    // ══════════════════════════════════════════
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'samiti_member_id' => 'required|integer|min:1',
                'reason'           => 'required|string|max:255',
                'amount'           => 'required|numeric|min:0.01',
                'date'             => 'nullable|date',
            ]);

            $fine = SamitiFine::where('user_id', Auth::id())->find($id);

            if (! $fine) {
                return response()->json([
                    'success' => false,
                    'message' => 'জরিমানা পাওয়া যায়নি',
                ], 404);
            }

            $fine->update([
                'samiti_member_id' => $validated['samiti_member_id'],
                'reason'           => $validated['reason'],
                'amount'           => $validated['amount'],
                'date'             => $validated['date'] ?? $fine->date,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $this->formatFine($fine->fresh()->load('member')),
                'message' => 'আপডেট হয়েছে ✅',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SamitiFine update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'আপডেট ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════
    //  PATCH /api/samiti/fines/{id}/toggle
    // ══════════════════════════════════════════
    public function toggle(string $id): JsonResponse
    {
        try {
            $fine = SamitiFine::where('user_id', Auth::id())->find($id);

            if (! $fine) {
                return response()->json([
                    'success' => false,
                    'message' => 'জরিমানা পাওয়া যায়নি',
                ], 404);
            }

            $fine->is_paid   = ! $fine->is_paid;
            $fine->paid_date = $fine->is_paid ? now()->toDateString() : null;
            $fine->save();

            return response()->json([
                'success' => true,
                'data'    => $this->formatFine($fine->load('member')),
                'message' => $fine->is_paid ? 'পরিশোধ সম্পন্ন ✅' : 'অপরিশোধ করা হয়েছে',
            ]);

        } catch (\Throwable $e) {
            Log::error('SamitiFine toggle: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'toggle ব্যর্থ'], 500);
        }
    }

    // ══════════════════════════════════════════
    //  DELETE /api/samiti/fines/{id}
    // ══════════════════════════════════════════
    public function destroy(string $id): JsonResponse
    {
        try {
            $fine = SamitiFine::where('user_id', Auth::id())->find($id);

            if (! $fine) {
                return response()->json([
                    'success' => false,
                    'message' => 'জরিমানা পাওয়া যায়নি',
                ], 404);
            }

            $fine->delete();

            return response()->json(['success' => true, 'message' => 'মুছে গেছে ✅']);

        } catch (\Throwable $e) {
            Log::error('SamitiFine destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'মুছতে ব্যর্থ'], 500);
        }
    }

    // ══════════════════════════════════════════
    //  Private: format — সব field Flutter এ যাবে
    // ══════════════════════════════════════════
    private function formatFine(SamitiFine $f): array
    {
        return [
            'id'               => $f->id,
            'samiti_member_id' => $f->samiti_member_id,
            'member_name'      => $f->member->name ?? '',
            'reason'           => $f->reason,
            'amount'           => (float) $f->amount,   // ✅ float — String bug নেই
            'date'             => $f->date?->format('Y-m-d'),
            'is_paid'          => (bool) $f->is_paid,
            'paid_date'        => $f->paid_date?->format('Y-m-d'),
        ];
    }
}
