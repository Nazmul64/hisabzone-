<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrickProduction;
use App\Models\Inventory;
use App\Models\Worker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickProductionController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date|after_or_equal:date_from',
            'brick_type' => 'nullable|in:standard,premium,export',
            'worker_id'  => 'nullable|integer',
        ]);

        $q = BrickProduction::with('worker');

        if ($req->filled('date_from'))  $q->whereDate('date', '>=', $req->date_from);
        if ($req->filled('date_to'))    $q->whereDate('date', '<=', $req->date_to);
        if ($req->filled('brick_type')) $q->where('brick_type', $req->brick_type);
        if ($req->filled('worker_id'))  $q->where('worker_id', $req->worker_id);

        $productions = $q->orderBy('date', 'desc')->get();

        $summary = [
            'total_raw'    => (int) $productions->sum('raw_bricks'),
            'total_burned' => (int) $productions->sum('burned_bricks'),
            'total_broken' => (int) $productions->sum('broken_bricks'),
            'entries'      => $productions->count(),
        ];

        return $this->ok(['productions' => $productions, 'summary' => $summary]);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'date'          => 'required|date',
            'worker_id'     => 'nullable|integer',
            'worker_name'   => 'nullable|string|max:255',
            'raw_bricks'    => 'required|integer|min:0',
            'burned_bricks' => 'required|integer|min:0',
            'broken_bricks' => 'nullable|integer|min:0',
            'brick_type'    => 'required|in:standard,premium,export',
            'note'          => 'nullable|string',
        ]);

        $validated['broken_bricks'] = $validated['broken_bricks'] ?? 0;

        if (!empty($validated['worker_id']) && empty($validated['worker_name'])) {
            $validated['worker_name'] = Worker::find($validated['worker_id'])?->name;
        }

        $production = BrickProduction::create($validated);

        $this->updateInventory(
            $validated['brick_type'],
            $validated['burned_bricks'],
            $validated['broken_bricks']
        );

        return $this->created(
            $production->load('worker'),
            'উৎপাদন এন্ট্রি সফলভাবে সংরক্ষিত হয়েছে'
        );
    }

    public function show(BrickProduction $production): JsonResponse
    {
        return $this->ok($production->load('worker'));
    }

    public function update(Request $req, BrickProduction $production): JsonResponse
    {
        $validated = $req->validate([
            'date'          => 'sometimes|required|date',
            'worker_id'     => 'nullable|integer',
            'worker_name'   => 'nullable|string|max:255',
            'raw_bricks'    => 'sometimes|required|integer|min:0',
            'burned_bricks' => 'sometimes|required|integer|min:0',
            'broken_bricks' => 'nullable|integer|min:0',
            'brick_type'    => 'sometimes|required|in:standard,premium,export',
            'note'          => 'nullable|string',
        ]);

        $production->update($validated);

        return $this->ok($production->load('worker'), 'আপডেট সফল হয়েছে');
    }

    public function destroy(BrickProduction $production): JsonResponse
    {
        $production->delete();
        return $this->ok(null, 'এন্ট্রি মুছে ফেলা হয়েছে');
    }

    private function updateInventory(string $brickType, int $burned, int $broken): void
    {
        $uid = auth()->id();

        if ($burned > 0) {
            Inventory::withoutGlobalScope('user')
                     ->where('user_id', $uid)
                     ->where('brick_type', $brickType)
                     ->increment('total', $burned);
            Inventory::withoutGlobalScope('user')
                     ->where('user_id', $uid)
                     ->where('brick_type', $brickType)
                     ->increment('available', $burned);
        }

        if ($broken > 0) {
            Inventory::withoutGlobalScope('user')
                     ->where('user_id', $uid)
                     ->where('brick_type', 'broken')
                     ->increment('total', $broken);
            Inventory::withoutGlobalScope('user')
                     ->where('user_id', $uid)
                     ->where('brick_type', 'broken')
                     ->increment('available', $broken);
        }
    }
}
