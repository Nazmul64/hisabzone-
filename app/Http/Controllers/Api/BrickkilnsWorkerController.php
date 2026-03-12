<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnsWorkerController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'search'    => 'nullable|string|max:100',
            'status'    => 'nullable|in:active,inactive',
            'work_type' => 'nullable|string|max:100',
        ]);

        $q = Worker::query();

        if ($req->filled('search')) {
            $term = $req->search;
            $q->where(function ($sub) use ($term) {
                $sub->where('name',   'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%");
            });
        }

        if ($req->filled('status'))    $q->where('status',    $req->status);
        if ($req->filled('work_type')) $q->where('work_type', $req->work_type);

        $workers = $q->orderBy('name')->get();

        $summary = [
            'total'            => $workers->count(),
            'active'           => $workers->where('status', 'active')->count(),
            'inactive'         => $workers->where('status', 'inactive')->count(),
            'total_daily_wage' => (float) $workers->where('status', 'active')->sum('daily_wage'),
        ];

        return $this->ok(['workers' => $workers, 'summary' => $summary]);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'name'       => 'required|string|max:255',
            'mobile'     => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'work_type'  => 'nullable|string|max:100',
            'daily_wage' => 'nullable|numeric|min:0',
            'join_date'  => 'nullable|date',
            'status'     => 'nullable|in:active,inactive',
            'nid'        => 'nullable|string|max:50',
            'note'       => 'nullable|string',
        ]);

        $worker = Worker::create($validated);

        return $this->created($worker, 'শ্রমিক সফলভাবে যোগ করা হয়েছে');
    }

    public function show(Worker $worker): JsonResponse
    {
        $worker->load('salaries');
        return $this->ok($worker);
    }

    public function update(Request $req, Worker $worker): JsonResponse
    {
        $validated = $req->validate([
            'name'       => 'sometimes|required|string|max:255',
            'mobile'     => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'work_type'  => 'nullable|string|max:100',
            'daily_wage' => 'nullable|numeric|min:0',
            'join_date'  => 'nullable|date',
            'status'     => 'nullable|in:active,inactive',
            'nid'        => 'nullable|string|max:50',
            'note'       => 'nullable|string',
        ]);

        $worker->update($validated);

        return $this->ok($worker, 'আপডেট সফল হয়েছে');
    }

    public function destroy(Worker $worker): JsonResponse
    {
        $worker->delete();
        return $this->ok(null, 'শ্রমিক মুছে ফেলা হয়েছে');
    }
}
