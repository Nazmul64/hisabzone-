<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Financemanage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinancemanageController extends Controller
{
    // ════════════════════════════════════════════════════════
    // GET /api/financemanages
    // Query params:
    //   ?date=2026-02-27
    //   ?month=2&year=2026
    //   ?year=2026
    //   (nothing) → সব
    // ════════════════════════════════════════════════════════
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $query = Financemanage::with('category')
                ->where('user_id', $userId);

            // ── Filter ───────────────────────────────────────
            if ($request->filled('date')) {
                $query->whereDate('date', $request->input('date'));

            } elseif ($request->filled('month') && $request->filled('year')) {
                $query->whereMonth('date', (int) $request->input('month'))
                      ->whereYear('date',  (int) $request->input('year'));

            } elseif ($request->filled('year')) {
                $query->whereYear('date', (int) $request->input('year'));
            }

            $finances = $query->orderBy('date', 'desc')
                              ->orderBy('id',   'desc')
                              ->get();

            // ── Empty message ─────────────────────────────────
            $message = null;
            if ($finances->isEmpty()) {
                $message = match(true) {
                    $request->filled('date')                                  => 'এই তারিখে কোনো লেনদেন নেই।',
                    $request->filled('month') && $request->filled('year')    => 'এই মাসে কোনো লেনদেন নেই।',
                    $request->filled('year')                                  => 'এই বছরে কোনো লেনদেন নেই।',
                    default                                                    => 'কোনো লেনদেন নেই।',
                };
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $finances->map(fn($f) => $this->fmt($f))->values(),
            ]);

        } catch (\Throwable $e) {
            Log::error('[Financemanage@index] ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    // POST /api/financemanages
    // Body: { amount, type, category_id?, date, time?, description? }
    // ════════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount'      => 'required|numeric|min:0',
                'type'        => 'required|in:income,expense',
                'category_id' => 'nullable|integer|exists:categories,id',
                'date'        => 'required|date_format:Y-m-d',
                'time'        => 'nullable|string|max:8',
                'description' => 'nullable|string|max:1000',
            ]);

            $validated['user_id'] = $request->user()->id;

            // ✅ time default
            if (empty($validated['time'])) {
                $validated['time'] = '00:00';
            }

            $finance = Financemanage::create($validated);
            $finance->load('category');

            return response()->json([
                'success' => true,
                'message' => 'লেনদেন সফলভাবে যোগ হয়েছে।',
                'data'    => $this->fmt($finance),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
                'data'    => null,
            ], 422);

        } catch (\Throwable $e) {
            Log::error('[Financemanage@store] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    // GET /api/financemanages/{id}
    // ════════════════════════════════════════════════════════
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $finance = Financemanage::with('category')
                ->where('id',      $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $finance) {
                return response()->json([
                    'success' => false,
                    'message' => 'রেকর্ড পাওয়া যায়নি।',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => null,
                'data'    => $this->fmt($finance),
            ]);

        } catch (\Throwable $e) {
            Log::error('[Financemanage@show] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    // PUT /api/financemanages/{id}
    // ════════════════════════════════════════════════════════
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $finance = Financemanage::where('id',      $id)
                                    ->where('user_id', $request->user()->id)
                                    ->first();

            if (! $finance) {
                return response()->json([
                    'success' => false,
                    'message' => 'রেকর্ড পাওয়া যায়নি।',
                    'data'    => null,
                ], 404);
            }

            $validated = $request->validate([
                'amount'      => 'sometimes|numeric|min:0',
                'type'        => 'sometimes|in:income,expense',
                'category_id' => 'nullable|integer|exists:categories,id',
                'date'        => 'sometimes|date_format:Y-m-d',
                'time'        => 'nullable|string|max:8',
                'description' => 'nullable|string|max:1000',
            ]);

            $finance->update($validated);
            $finance->load('category');

            return response()->json([
                'success' => true,
                'message' => 'লেনদেন সফলভাবে আপডেট হয়েছে।',
                'data'    => $this->fmt($finance),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
                'data'    => null,
            ], 422);

        } catch (\Throwable $e) {
            Log::error('[Financemanage@update] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    // DELETE /api/financemanages/{id}
    // ════════════════════════════════════════════════════════
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $finance = Financemanage::where('id',      $id)
                                    ->where('user_id', $request->user()->id)
                                    ->first();

            if (! $finance) {
                return response()->json([
                    'success' => false,
                    'message' => 'রেকর্ড পাওয়া যায়নি।',
                    'data'    => null,
                ], 404);
            }

            $finance->delete();

            return response()->json([
                'success' => true,
                'message' => 'লেনদেন সফলভাবে মুছে ফেলা হয়েছে।',
                'data'    => null,
            ]);

        } catch (\Throwable $e) {
            Log::error('[Financemanage@destroy] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    // fmt()
    // Flutter Transaction.fromApi() এর সাথে perfectly matched
    // ✅ date → Y-m-d string (cast ছাড়াই safe)
    // ✅ time → H:i string
    // ✅ category null-safe
    // ✅ amount always float
    // ✅ id always string
    // ════════════════════════════════════════════════════════
    private function fmt(Financemanage $f): array
    {
        // ── date → 'Y-m-d' ───────────────────────────────
        $dateStr = null;
        if ($f->date) {
            // date column যা-ই হোক — string বা Carbon
            $raw = $f->getRawOriginal('date') ?? $f->date;
            if (is_string($raw)) {
                $dateStr = substr($raw, 0, 10); // '2026-02-27'
            } else {
                try {
                    $dateStr = \Carbon\Carbon::parse($raw)->format('Y-m-d');
                } catch (\Throwable $_) {
                    $dateStr = null;
                }
            }
        }

        // ── time → 'H:i' ─────────────────────────────────
        $timeStr = '00:00';
        if ($f->time) {
            $raw = $f->getRawOriginal('time') ?? $f->time;
            // '14:30:00' → '14:30'
            $timeStr = is_string($raw)
                ? substr($raw, 0, 5)
                : '00:00';
        }

        // ── category null-safe ────────────────────────────
        $cat = null;
        if ($f->category) {
            $cat = [
                'id'   => (int)    $f->category->id,
                'name' => (string) $f->category->name,
                'slug' => (string) ($f->category->slug ?? $f->category->name),
                'icon' => (string) ($f->category->icon ?? 'category'),
            ];
        }

        return [
            'id'          => (string) $f->id,
            'amount'      => (float)  $f->amount,
            'type'        => (string) $f->type,            // 'income'|'expense'
            'category_id' => $f->category_id,              // int|null
            'category'    => $cat,                         // array|null
            'date'        => $dateStr,                     // 'Y-m-d'|null
            'time'        => $timeStr,                     // 'H:i'
            'description' => (string) ($f->description ?? ''),
            'created_at'  => $f->created_at?->toIso8601String(),
            'updated_at'  => $f->updated_at?->toIso8601String(),
        ];
    }
}
