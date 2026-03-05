<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreHistoryController extends Controller
{
    // ──────────────────────────────────────────────────────────
    //  GET /api/scorehub/history
    //  ✅ FIX: grand_total is a computed property on the Model,
    //         NOT a real DB column — তাই select() করা যাবে না,
    //         extras relation load করতে হবে যাতে grand_total কাজ করে
    // ──────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $matches = ScoreMatch::forUser($userId)
            ->with(['teams.extras'])   // ✅ extras দরকার grand_total compute করতে
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'title'       => $m->title ?? '',
                'team_a_name' => $m->team_a_name,
                'team_b_name' => $m->team_b_name,
                'match_date'  => $m->match_date?->toDateString() ?? '',
                'status'      => $m->status,
                'created_at'  => $m->created_at->toDateTimeString(),
                'teams'       => $m->teams->map(fn($t) => [
                    'id'          => $t->id,
                    'side'        => $t->side,
                    'grand_total' => (int) ($t->grand_total ?? 0),
                ])->values(),
            ]);

        return response()->json(['success' => true, 'data' => $matches]);
    }

    // ──────────────────────────────────────────────────────────
    //  GET /api/scorehub/history/{id}
    // ──────────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $match = ScoreMatch::forUser($userId)
            ->with(['teams.players', 'teams.extras', 'teams.overs', 'teams.bowlers'])
            ->find($id);

        if (! $match) {
            return response()->json(['success' => false, 'message' => 'ম্যাচ পাওয়া যায়নি'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->formatDetail($match)]);
    }

    // ──────────────────────────────────────────────────────────
    //  DELETE /api/scorehub/history/{id}
    // ──────────────────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $match  = ScoreMatch::forUser($userId)->find($id);

        if (! $match) {
            return response()->json(['success' => false, 'message' => 'ম্যাচ পাওয়া যায়নি'], 404);
        }

        $match->delete();

        return response()->json(['success' => true, 'message' => 'ম্যাচ মুছে গেছে']);
    }

    private function formatDetail(ScoreMatch $m): array
    {
        return [
            'id'          => $m->id,
            'title'       => $m->title ?? '',
            'team_a_name' => $m->team_a_name,
            'team_b_name' => $m->team_b_name,
            'match_date'  => $m->match_date?->toDateString() ?? '',
            'status'      => $m->status,
            'created_at'  => $m->created_at->toDateTimeString(),
            'teams'       => $m->teams->map(fn($t) => [
                'id'             => $t->id,
                'side'           => $t->side,
                'name'           => $t->name ?? '',
                'max_run'        => (int) ($t->max_run ?? 100),
                'grid_cut_cells' => $t->grid_cut_cells ?? [],
                'grand_total'    => (int) ($t->grand_total ?? 0),
                'players'  => $t->players->map(fn($p) => [
                    'id'          => $p->id,
                    'name'        => $p->name ?? '',
                    'is_out'      => (bool) $p->is_out,
                    'run_entries' => $p->run_entries ?? [],
                ])->values(),
                'extras'   => $t->extras->map(fn($e) => [
                    'id'     => $e->id,
                    'val'    => (int) $e->val,
                    'is_cut' => (bool) $e->is_cut,
                ])->values(),
                'overs'    => $t->overs->map(fn($o) => [
                    'id'          => $o->id,
                    'over_number' => (int) $o->over_number,
                    'is_done'     => (bool) $o->is_done,
                ])->values(),
                'bowlers'  => $t->bowlers->map(fn($b) => [
                    'id'    => $b->id,
                    'name'  => $b->name ?? '',
                    'overs' => $b->overs ?? '',
                ])->values(),
            ])->values(),
        ];
    }
}
