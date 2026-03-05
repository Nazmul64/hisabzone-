<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreMatch;
use App\Models\ScoreTeam;
use App\Models\ScoreOver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreMatchController extends Controller
{
    // ══════════════════════════════════════════════════════
    //  GET /api/scorehub/matches
    //  এই লগিন user এর সব ম্যাচ তালিকা
    //  ✅ অন্য user এর ম্যাচ দেখা যাবে না
    // ══════════════════════════════════════════════════════
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $matches = ScoreMatch::forUser($userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'title'       => $m->title,
                'team_a_name' => $m->team_a_name,
                'team_b_name' => $m->team_b_name,
                'match_date'  => $m->match_date?->toDateString(),
                'status'      => $m->status,
                'created_at'  => $m->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => $matches,
            'message' => 'ম্যাচ তালিকা পাওয়া গেছে',
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  POST /api/scorehub/matches
    //  নতুন ম্যাচ তৈরি করো
    //  ✅ দুটো দল + ২০টা ওভার auto তৈরি হবে
    // ══════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'team_a_name' => 'required|string|max:100',
            'team_b_name' => 'required|string|max:100',
            'title'       => 'nullable|string|max:200',
            'match_date'  => 'nullable|date',
        ]);

        $userId = $request->user()->id;

        DB::beginTransaction();
        try {
            // ── ম্যাচ তৈরি ──
            $match = ScoreMatch::create([
                'user_id'     => $userId,
                'title'       => $request->title,
                'team_a_name' => $request->team_a_name,
                'team_b_name' => $request->team_b_name,
                'match_date'  => $request->match_date ?? now()->toDateString(),
                'status'      => 'ongoing',
            ]);

            // ── দল ক তৈরি ──
            $teamA = ScoreTeam::create([
                'match_id'       => $match->id,
                'user_id'        => $userId,
                'name'           => $request->team_a_name,
                'side'           => 'a',
                'max_run'        => 100,
                'grid_cut_cells' => [],
                'overs_count'    => 20,
            ]);

            // ── দল খ তৈরি ──
            $teamB = ScoreTeam::create([
                'match_id'       => $match->id,
                'user_id'        => $userId,
                'name'           => $request->team_b_name,
                'side'           => 'b',
                'max_run'        => 100,
                'grid_cut_cells' => [],
                'overs_count'    => 20,
            ]);

            // ── দুই দলের জন্য ২০টা করে ওভার তৈরি (bulk insert) ──
            $oversA = [];
            $oversB = [];
            $now    = now();
            for ($i = 1; $i <= 20; $i++) {
                $oversA[] = [
                    'team_id'     => $teamA->id,
                    'user_id'     => $userId,
                    'over_number' => $i,
                    'is_done'     => false,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
                $oversB[] = [
                    'team_id'     => $teamB->id,
                    'user_id'     => $userId,
                    'over_number' => $i,
                    'is_done'     => false,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            ScoreOver::insert($oversA);
            ScoreOver::insert($oversB);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => [
                    'match_id'  => $match->id,
                    'team_a_id' => $teamA->id,
                    'team_b_id' => $teamB->id,
                ],
                'message' => 'ম্যাচ তৈরি হয়েছে! ✅',
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ম্যাচ তৈরিতে সমস্যা: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  GET /api/scorehub/matches/{id}
    //  একটা ম্যাচের পুরো ডাটা লোড করো
    //  ✅ user_id check করা হচ্ছে — অন্যেরটা 404 দেবে
    // ══════════════════════════════════════════════════════
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $match = ScoreMatch::forUser($userId)
            ->with([
                'teams.players',
                'teams.extras',
                'teams.overs',
                'teams.bowlers',
            ])
            ->find($id);

        if (! $match) {
            return response()->json([
                'success' => false,
                'message' => 'ম্যাচ পাওয়া যায়নি',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatMatch($match),
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  PATCH /api/scorehub/matches/{id}
    //  ম্যাচ আপডেট (title / status)
    // ══════════════════════════════════════════════════════
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $match  = ScoreMatch::forUser($userId)->find($id);

        if (! $match) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $match->update($request->only(['title', 'status', 'match_date']));

        return response()->json(['success' => true, 'message' => 'আপডেট হয়েছে']);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE /api/scorehub/matches/{id}
    //  ম্যাচ মুছো (cascade: teams, players, extras সব মুছবে)
    // ══════════════════════════════════════════════════════
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $match  = ScoreMatch::forUser($userId)->find($id);

        if (! $match) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $match->delete();

        return response()->json(['success' => true, 'message' => 'ম্যাচ মুছে গেছে']);
    }

    // ── Helper: পুরো ম্যাচ ডাটা format করো ──
    private function formatMatch(ScoreMatch $m): array
    {
        return [
            'id'          => $m->id,
            'title'       => $m->title,
            'team_a_name' => $m->team_a_name,
            'team_b_name' => $m->team_b_name,
            'match_date'  => $m->match_date?->toDateString(),
            'status'      => $m->status,
            'teams'       => $m->teams->map(fn($t) => $this->formatTeam($t))->values(),
        ];
    }

    private function formatTeam(ScoreTeam $t): array
    {
        return [
            'id'             => $t->id,
            'name'           => $t->name,
            'side'           => $t->side,
            'max_run'        => $t->max_run,
            'grid_cut_cells' => $t->grid_cut_cells ?? [],
            'grid_score'     => $t->grid_score,
            'extras_total'   => $t->extras_total,
            'grand_total'    => $t->grand_total,
            'overs_count'    => $t->overs_count,
            'players' => $t->players->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'order_index' => $p->order_index,
                'is_out'      => $p->is_out,
                'run_entries' => $p->run_entries ?? [],
                'score'       => $p->score,
            ])->values(),
            'extras' => $t->extras->map(fn($e) => [
                'id'          => $e->id,
                'val'         => $e->val,
                'is_cut'      => $e->is_cut,
                'order_index' => $e->order_index,
            ])->values(),
            'overs' => $t->overs->map(fn($o) => [
                'id'          => $o->id,
                'over_number' => $o->over_number,
                'is_done'     => $o->is_done,
            ])->values(),
            'bowlers' => $t->bowlers->map(fn($b) => [
                'id'          => $b->id,
                'name'        => $b->name,
                'overs'       => $b->overs,
                'order_index' => $b->order_index,
            ])->values(),
        ];
    }
}
