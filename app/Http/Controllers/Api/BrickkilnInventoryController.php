<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrickProduction;
use App\Models\Inventory;
use App\Models\Sale;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BrickkilnInventoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $inventory = Inventory::orderBy('brick_type')->get();

        $summary = [
            'total_available' => (int) $inventory->sum('available'),
            'total_sold'      => (int) $inventory->sum('sold'),
            'total_produced'  => (int) $inventory->sum('total'),
        ];

        return $this->ok($inventory);
    }

    public function update(\Illuminate\Http\Request $req, Inventory $inventory): JsonResponse
    {
        $validated = $req->validate([
            'total'     => 'nullable|integer|min:0',
            'sold'      => 'nullable|integer|min:0',
            'available' => 'nullable|integer|min:0',
            'label'     => 'nullable|string|max:100',
            'emoji'     => 'nullable|string|max:10',
        ]);

        $inventory->update($validated);

        return $this->ok($inventory, 'ইনভেন্টরি আপডেট হয়েছে');
    }

    public function sync(): JsonResponse
    {
        $uid = auth()->id();

        $typeConfig = [
            'standard' => ['label' => 'স্ট্যান্ডার্ড', 'emoji' => '🧱'],
            'premium'  => ['label' => 'প্রিমিয়াম',    'emoji' => '🏅'],
            'export'   => ['label' => 'এক্সপোর্ট',    'emoji' => '📦'],
        ];

        foreach ($typeConfig as $type => $meta) {
            $totalProduced = BrickProduction::withoutGlobalScope('user')
                                ->where('user_id', $uid)
                                ->where('brick_type', $type)
                                ->sum('burned_bricks');

            $totalSold = Sale::withoutGlobalScope('user')
                            ->where('user_id', $uid)
                            ->where('brick_type', $type)
                            ->sum('quantity');

            Inventory::withoutGlobalScope('user')
                     ->updateOrCreate(
                ['user_id' => $uid, 'brick_type' => $type],
                [
                    'label'     => $meta['label'],
                    'emoji'     => $meta['emoji'],
                    'total'     => $totalProduced,
                    'sold'      => $totalSold,
                    'available' => max(0, $totalProduced - $totalSold),
                ]
            );
        }

        $totalBroken = BrickProduction::withoutGlobalScope('user')
                          ->where('user_id', $uid)->sum('broken_bricks');
        $brokenSold  = Sale::withoutGlobalScope('user')
                          ->where('user_id', $uid)->where('brick_type', 'broken')->sum('quantity');

        Inventory::withoutGlobalScope('user')
                 ->updateOrCreate(
            ['user_id' => $uid, 'brick_type' => 'broken'],
            [
                'label'     => 'ভাঙা ইট',
                'emoji'     => '🪨',
                'total'     => $totalBroken,
                'sold'      => $brokenSold,
                'available' => max(0, $totalBroken - $brokenSold),
            ]
        );

        $inventory = Inventory::orderBy('brick_type')->get();

        return $this->ok($inventory, 'ইনভেন্টরি sync হয়েছে');
    }
    public function destroy(Inventory $inventory): JsonResponse
{
    $inventory->delete();
    return $this->ok(null, 'ইনভেন্টরি মুছে ফেলা হয়েছে');
}
}
