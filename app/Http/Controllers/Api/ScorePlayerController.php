<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScorePlayer;
use App\Models\ScoreTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScorePlayerController extends Controller
{
    // ══════════════════════════════════════════════════════
    //  POST /api/scorehub/teams/{id}/players
    //  নতুন খেলোয়াড় যোগ করো
    // ══════════════════════════════════════════════════════
    public function store(Request $request, int $teamId): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100']);

        $userId = $request->user()->id;
        $team   = ScoreTeam::forUser($userId)->find($teamId);

        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $count  = $team->players()->count();
        $player = ScorePlayer::create([
            'team_id'     => $team->id,
            'user_id'     => $userId,
            'name'        => $request->name,
            'order_index' => $count,
            'is_out'      => false,
            'run_entries' => [],
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $player->id,
                'name'        => $player->name,
                'order_index' => $player->order_index,
                'is_out'      => false,
                'run_entries' => [],
                'score'       => 0,
            ],
            'message' => 'খেলোয়াড় যোগ হয়েছে',
        ], 201);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE /api/scorehub/players/{id}
    // ══════════════════════════════════════════════════════
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $player = ScorePlayer::forUser($userId)->find($id);

        if (! $player) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $player->delete();

        return response()->json(['success' => true, 'message' => 'খেলোয়াড় মুছে গেছে']);
    }

    // ══════════════════════════════════════════════════════
    //  PATCH /api/scorehub/players/{id}/toggle-out
    //  আউট টগল (is_out: true/false)
    // ══════════════════════════════════════════════════════
    public function toggleOut(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_out' => 'required|boolean']);

        $userId = $request->user()->id;
        $player = ScorePlayer::forUser($userId)->find($id);

        if (! $player) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $player->update(['is_out' => $request->is_out]);

        return response()->json([
            'success' => true,
            'data'    => ['id' => $player->id, 'is_out' => $player->is_out],
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  POST /api/scorehub/players/{id}/runs
    //  রান যোগ করো (০ও চলবে — duck out)
    // ══════════════════════════════════════════════════════
    public function addRun(Request $request, int $id): JsonResponse
    {
        $request->validate(['run' => 'required|integer|min:0']); // ✅ min:0 — ০ রানও চলবে

        $userId = $request->user()->id;
        $player = ScorePlayer::forUser($userId)->find($id);

        if (! $player) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $entries   = $player->run_entries ?? [];
        $entries[] = $request->run;
        $player->update(['run_entries' => $entries]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $player->id,
                'run_entries' => $player->run_entries,
                'score'       => $player->score,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE /api/scorehub/players/{id}/runs/{runIdx}
    //  রান মুছো (Flutter: long press)
    //  runIdx = array index (0-based)
    // ══════════════════════════════════════════════════════
    public function removeRun(Request $request, int $id, int $runIdx): JsonResponse
    {
        $userId = $request->user()->id;
        $player = ScorePlayer::forUser($userId)->find($id);

        if (! $player) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $entries = $player->run_entries ?? [];

        if (! isset($entries[$runIdx])) {
            return response()->json(['success' => false, 'message' => 'রান index পাওয়া যায়নি'], 404);
        }

        array_splice($entries, $runIdx, 1);
        $player->update(['run_entries' => array_values($entries)]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $player->id,
                'run_entries' => $player->run_entries,
                'score'       => $player->score,
            ],
        ]);
    }
}
