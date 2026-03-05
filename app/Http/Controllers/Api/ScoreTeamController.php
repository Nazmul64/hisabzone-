<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreTeam;
use App\Models\ScoreExtra;
use App\Models\ScoreOver;
use App\Models\ScoreBowler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreTeamController extends Controller
{
    // ══════════════════════════════════════════════════════
    //  PATCH /api/scorehub/teams/{id}/grid
    //  গ্রিড আপডেট — কাটা ঘরের list পাঠাও
    //  Flutter থেকে: Set<int> gridCutSet → List<int> পাঠাবে
    //  ✅ user_id verify করা হচ্ছে
    // ══════════════════════════════════════════════════════
    public function updateGrid(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'grid_cut_cells'   => 'required|array',
            'grid_cut_cells.*' => 'integer|min:1',
            'max_run'          => 'sometimes|integer|min:1|max:9999',
        ]);

        $userId = $request->user()->id;
        $team   = ScoreTeam::forUser($userId)->find($id);

        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        // ✅ duplicate সরিয়ে sorted list সেভ করো
        $cells = array_values(array_unique($request->grid_cut_cells));
        sort($cells);

        $team->update([
            'grid_cut_cells' => $cells,
            'max_run'        => $request->max_run ?? $team->max_run,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'grid_cut_cells' => $team->grid_cut_cells,
                'grid_score'     => $team->grid_score,
                'grand_total'    => $team->grand_total,
            ],
            'message' => 'গ্রিড সেভ হয়েছে',
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  POST /api/scorehub/teams/{id}/extras
    //  অতিরিক্ত রান যোগ করো
    // ══════════════════════════════════════════════════════
    public function addExtra(Request $request, int $id): JsonResponse
    {
        $request->validate(['val' => 'required|integer|min:1']);

        $userId = $request->user()->id;
        $team   = ScoreTeam::forUser($userId)->find($id);

        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $count = $team->extras()->count();
        $extra = ScoreExtra::create([
            'team_id'     => $team->id,
            'user_id'     => $userId,
            'val'         => $request->val,
            'is_cut'      => false,
            'order_index' => $count,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $extra->id,
                'val'         => $extra->val,
                'is_cut'      => $extra->is_cut,
                'order_index' => $extra->order_index,
            ],
            'message' => 'অতিরিক্ত রান যোগ হয়েছে',
        ], 201);
    }

    // ══════════════════════════════════════════════════════
    //  PATCH /api/scorehub/extras/{id}/toggle
    //  অতিরিক্ত রান কাটো / ফেরাও
    // ══════════════════════════════════════════════════════
    public function toggleExtra(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $extra  = ScoreExtra::forUser($userId)->find($id);

        if (! $extra) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $extra->update(['is_cut' => ! $extra->is_cut]);

        // নতুন grand_total পাঠাই
        $extra->team->refresh();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $extra->id,
                'is_cut'      => $extra->is_cut,
                'grand_total' => $extra->team->grand_total,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE /api/scorehub/extras/{id}
    //  অতিরিক্ত রান মুছো
    // ══════════════════════════════════════════════════════
    public function deleteExtra(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $extra  = ScoreExtra::forUser($userId)->find($id);

        if (! $extra) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $extra->delete();

        return response()->json(['success' => true, 'message' => 'মুছে গেছে']);
    }

    // ══════════════════════════════════════════════════════
    //  POST /api/scorehub/teams/{id}/overs
    //  নতুন ওভার যোগ করো
    // ══════════════════════════════════════════════════════
    public function addOver(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $team   = ScoreTeam::forUser($userId)->find($id);

        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $lastNum = $team->overs()->max('over_number') ?? 0;
        $over    = ScoreOver::create([
            'team_id'     => $team->id,
            'user_id'     => $userId,
            'over_number' => $lastNum + 1,
            'is_done'     => false,
        ]);

        $team->update(['overs_count' => $lastNum + 1]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $over->id,
                'over_number' => $over->over_number,
                'is_done'     => false,
            ],
        ], 201);
    }

    // ══════════════════════════════════════════════════════
    //  PATCH /api/scorehub/overs/{id}/toggle
    //  ওভার শেষ / আনশেষ টগল
    // ══════════════════════════════════════════════════════
    public function toggleOver(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $over   = ScoreOver::forUser($userId)->find($id);

        if (! $over) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $over->update(['is_done' => ! $over->is_done]);

        return response()->json([
            'success' => true,
            'data'    => ['id' => $over->id, 'is_done' => $over->is_done],
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  POST /api/scorehub/teams/{id}/bowlers
    //  নতুন বলার যোগ করো
    // ══════════════════════════════════════════════════════
    public function addBowler(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'overs' => 'nullable|string|max:20',
        ]);

        $userId = $request->user()->id;
        $team   = ScoreTeam::forUser($userId)->find($id);

        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $count  = $team->bowlers()->count();
        $bowler = ScoreBowler::create([
            'team_id'     => $team->id,
            'user_id'     => $userId,
            'name'        => $request->name,
            'overs'       => $request->overs,
            'order_index' => $count,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $bowler->id,
                'name'        => $bowler->name,
                'overs'       => $bowler->overs,
                'order_index' => $bowler->order_index,
            ],
        ], 201);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE /api/scorehub/bowlers/{id}
    // ══════════════════════════════════════════════════════
    public function deleteBowler(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $bowler = ScoreBowler::forUser($userId)->find($id);

        if (! $bowler) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $bowler->delete();

        return response()->json(['success' => true, 'message' => 'বলার মুছে গেছে']);
    }
}
