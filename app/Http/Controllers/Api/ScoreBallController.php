<?php
// ════════════════════════════════════════════════════════════
//  app/Http/Controllers/Api/ScoreBallController.php
//  ✅ বাই রান — অতিরিক্ত রানে count + টিক মার্ক (0,1,2,4,6)
//  ✅ বলারভিত্তিক প্রতিটা বল (dot, runs, wicket) — শুধু দেখার জন্য
//  ✅ বাকি সব আগের মতো
// ════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreBallEntry;
use App\Models\ScoreTournamentTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreBallController extends Controller
{
    // ══════════════════════════════════════════
    //  GET /api/scorehub/teams/{id}/balls
    // ══════════════════════════════════════════
    public function index(Request $request, int $teamId): JsonResponse
    {
        $team = ScoreTournamentTeam::find($teamId);
        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $balls = ScoreBallEntry::where('batting_team_id', $teamId)
            ->orderBy('over_number')
            ->orderBy('ball_number')
            ->get();

        $overs = [];
        foreach ($balls as $ball) {
            $over = $ball->over_number;
            if (! isset($overs[$over])) {
                $overs[$over] = ['over_number' => $over, 'balls' => [], 'over_runs' => 0];
            }
            $overs[$over]['balls'][]    = $this->formatBall($ball);
            $overs[$over]['over_runs'] += $ball->is_cut ? 0 : $ball->runs;
        }

        return response()->json([
            'success' => true,
            'data'    => ['overs' => array_values($overs)],
        ]);
    }

    // ══════════════════════════════════════════
    //  POST /api/scorehub/teams/{id}/balls
    //  ✅ bye_run_value: 0,1,2,4,6 — টিক মার্কের জন্য
    // ══════════════════════════════════════════
    public function store(Request $request, int $teamId): JsonResponse
    {
        $request->validate([
            'fixture_id'    => 'required|integer',
            'over_number'   => 'required|integer|min:1',
            'ball_number'   => 'required|integer|min:1',
            'ball_type'     => 'required|in:dot,one,two,three,four,six,no_ball,bye,wide,wicket',
            'bowler_name'   => 'nullable|string|max:100',
            'batsman_name'  => 'nullable|string|max:100',
            'note'          => 'nullable|string|max:300',
            // ✅ বাই রানের মান: 0,1,2,4,6 (টিক মার্ক)
            'bye_run_value' => 'nullable|integer|in:0,1,2,4,6',
        ]);

        $team = ScoreTournamentTeam::find($teamId);
        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $ballType = $request->ball_type;
        $isExtra  = in_array($ballType, ScoreBallEntry::$extraTypes);

        // ✅ বাই রানের টিক মার্ক — bye_run_value দিয়ে নির্দিষ্ট রান
        $runs = match($ballType) {
            'dot'     => 0,
            'one'     => 1,
            'two'     => 2,
            'three'   => 3,
            'four'    => 4,
            'six'     => 6,
            'no_ball' => 1,
            'bye'     => $request->bye_run_value ?? 1,  // ✅ টিক মার্ক value
            'wide'    => 1,
            'wicket'  => 0,
            default   => 0,
        };

        $ball = ScoreBallEntry::create([
            'fixture_id'      => $request->fixture_id,
            'batting_team_id' => $teamId,
            'bowler_name'     => $request->bowler_name,
            'batsman_name'    => $request->batsman_name,
            'over_number'     => $request->over_number,
            'ball_number'     => $request->ball_number,
            'ball_type'       => $ballType,
            'runs'            => $runs,
            'is_extra'        => $isExtra,
            'is_cut'          => false,
            'note'            => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->formatBall($ball),
            'message' => 'বল রেকর্ড হয়েছে ✅',
        ], 201);
    }

    // ══════════════════════════════════════════
    //  PATCH /api/scorehub/balls/{id}/toggle-cut
    // ══════════════════════════════════════════
    public function toggleCut(Request $request, int $id): JsonResponse
    {
        $ball = ScoreBallEntry::find($id);
        if (! $ball) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }
        if (! $ball->is_extra) {
            return response()->json(['success' => false, 'message' => 'শুধু NB/বাই/ওয়াইড কাটা যাবে'], 422);
        }

        $ball->update(['is_cut' => ! $ball->is_cut]);

        return response()->json([
            'success' => true,
            'data'    => $this->formatBall($ball),
            'message' => $ball->is_cut ? 'কাটা হয়েছে' : 'কাটা বাতিল',
        ]);
    }

    // ══════════════════════════════════════════
    //  DELETE /api/scorehub/balls/{id}
    // ══════════════════════════════════════════
    public function destroy(Request $request, int $id): JsonResponse
    {
        $ball = ScoreBallEntry::find($id);
        if (! $ball) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }
        $ball->delete();

        return response()->json(['success' => true, 'message' => 'মুছে গেছে']);
    }

    // ══════════════════════════════════════════
    //  GET /api/scorehub/teams/{id}/balls/summary
    //  ✅ বলারভিত্তিক প্রতিটা বলের বিস্তারিত যোগ
    // ══════════════════════════════════════════
    public function summary(Request $request, int $teamId): JsonResponse
    {
        $team = ScoreTournamentTeam::find($teamId);
        if (! $team) {
            return response()->json(['success' => false, 'message' => 'দল পাওয়া যায়নি'], 404);
        }

        $balls   = ScoreBallEntry::where('batting_team_id', $teamId)->get();
        $summary = $this->buildSummary($balls, $team);

        return response()->json(['success' => true, 'data' => $summary]);
    }

    // ══════════════════════════════════════════
    //  Private: format
    // ══════════════════════════════════════════
    private function formatBall(ScoreBallEntry $b): array
    {
        return [
            'id'              => $b->id,
            'fixture_id'      => $b->fixture_id,
            'batting_team_id' => $b->batting_team_id,
            'over_number'     => $b->over_number,
            'ball_number'     => $b->ball_number,
            'ball_type'       => $b->ball_type,
            'ball_type_bn'    => $b->ball_type_bn,
            'runs'            => $b->runs,
            'is_extra'        => $b->is_extra,
            'is_cut'          => $b->is_cut,
            'bowler_name'     => $b->bowler_name  ?? '',
            'batsman_name'    => $b->batsman_name ?? '',
            'note'            => $b->note         ?? '',
        ];
    }

    // ══════════════════════════════════════════
    //  Private: summary build
    //  ✅ বলারভিত্তিক প্রতিটা বল (dot/run/wicket) — শুধু দেখার জন্য
    //  ✅ বাই রান আলাদা হিসাব
    // ══════════════════════════════════════════
    private function buildSummary($balls, $team): array
    {
        $total   = $balls->count();
        $dots    = $balls->where('ball_type', 'dot')->count();
        $fours   = $balls->where('ball_type', 'four')->count();
        $sixes   = $balls->where('ball_type', 'six')->count();
        $noBalls = $balls->where('ball_type', 'no_ball')->count();
        $byes    = $balls->where('ball_type', 'bye')->count();
        $wides   = $balls->where('ball_type', 'wide')->count();
        $wickets = $balls->where('ball_type', 'wicket')->count();

        // অতিরিক্ত রান (কাটা বাদে)
        $extraRuns = $balls->filter(fn($b) =>
            in_array($b->ball_type, ScoreBallEntry::$extraTypes) && ! $b->is_cut
        )->sum('runs');

        // বাই রান (কাটা বাদে)
        $byeRuns = $balls->filter(fn($b) =>
            $b->ball_type === 'bye' && ! $b->is_cut
        )->sum('runs');

        $byesCut = $balls->filter(fn($b) =>
            $b->ball_type === 'bye' && $b->is_cut
        )->count();

        // নো-বল রান
        $noBallRuns = $balls->filter(fn($b) =>
            $b->ball_type === 'no_ball' && ! $b->is_cut
        )->sum('runs');

        // ওয়াইড রান
        $wideRuns = $balls->filter(fn($b) =>
            $b->ball_type === 'wide' && ! $b->is_cut
        )->sum('runs');

        // ✅ বাই রানের টিক মার্ক বিতরণ (0,1,2,4,6)
        $byeBreakdown = $balls->filter(fn($b) => $b->ball_type === 'bye')
            ->groupBy('runs')
            ->map(fn($g, $runVal) => [
                'run_value' => (int) $runVal,
                'count'     => $g->count(),
                'cut_count' => $g->where('is_cut', true)->count(),
                'label'     => $runVal == 0 ? '০ বাই' : ($runVal == 4 ? '৪ বাই' : ($runVal == 6 ? '৬ বাই' : "$runVal বাই")),
            ])
            ->sortKeys()
            ->values();

        // নো-বল বলারভিত্তিক
        $noBallByBowler = $balls->where('ball_type', 'no_ball')
            ->groupBy('bowler_name')
            ->map(fn($g, $name) => [
                'bowler' => $name ?: 'অজানা',
                'count'  => $g->count(),
                'runs'   => $g->sum('runs'),
            ])->values();

        // বলারভিত্তিক উইকেট + victims
        $wicketsByBowler = $balls->where('ball_type', 'wicket')
            ->groupBy('bowler_name')
            ->map(fn($g, $name) => [
                'bowler'   => $name ?: 'অজানা',
                'wickets'  => $g->count(),
                'no_balls' => $balls->where('ball_type', 'no_ball')->where('bowler_name', $name)->count(),
                'victims'  => $g->pluck('batsman_name')
                                ->filter(fn($n) => ! empty($n))
                                ->values()
                                ->toArray(),
            ])
            ->sortByDesc('wickets')
            ->values();

        $topBowler = $wicketsByBowler->isNotEmpty() ? $wicketsByBowler->first() : null;

        // ✅ বলারভিত্তিক প্রতিটা বলের বিস্তারিত — শুধু দেখার জন্য, count হয় না
        $bowlerBallDetail = $balls->filter(fn($b) => ! empty($b->bowler_name))
            ->groupBy('bowler_name')
            ->map(fn($g, $name) => [
                'bowler'       => $name,
                'total_runs'   => $g->sum('runs'),
                'total_balls'  => $g->count(),
                'dots'         => $g->where('ball_type', 'dot')->count(),
                'fours'        => $g->where('ball_type', 'four')->count(),
                'sixes'        => $g->where('ball_type', 'six')->count(),
                'no_balls'     => $g->where('ball_type', 'no_ball')->count(),
                'wickets'      => $g->where('ball_type', 'wicket')->count(),
                'byes'         => $g->where('ball_type', 'bye')->count(),
                'wides'        => $g->where('ball_type', 'wide')->count(),
                // ✅ প্রতিটা বলের সিকোয়েন্স — ওভার.বল নম্বর সহ
                'ball_sequence' => $g->sortBy('over_number')->sortBy('ball_number')
                    ->map(fn($b) => [
                        'over'      => $b->over_number,
                        'ball'      => $b->ball_number,
                        'type'      => $b->ball_type,
                        'type_bn'   => $b->ball_type_bn,
                        'runs'      => $b->runs,
                        'is_cut'    => $b->is_cut,
                        'is_dot'    => $b->ball_type === 'dot',
                        'is_wicket' => $b->ball_type === 'wicket',
                        'batsman'   => $b->batsman_name ?? '',
                    ])->values()->toArray(),
                // ওভারভিত্তিক রান (এই বলারের)
                'runs_per_over' => $g->groupBy('over_number')
                    ->map(fn($ov, $overNum) => [
                        'over'  => (int) $overNum,
                        'runs'  => $ov->sum('runs'),
                        'balls' => $ov->count(),
                    ])
                    ->sortKeys()
                    ->values()
                    ->toArray(),
            ])
            ->sortByDesc('total_runs')
            ->values();

        // সর্বোচ্চ রান (ব্যাটসম্যান)
        $batsmanRuns = $balls
            ->whereIn('ball_type', ['one', 'two', 'three', 'four', 'six'])
            ->filter(fn($b) => ! empty($b->batsman_name))
            ->groupBy('batsman_name')
            ->map(fn($g, $name) => [
                'name'  => $name,
                'runs'  => $g->sum('runs'),
                'balls' => $g->count(),
                'fours' => $g->where('ball_type', 'four')->count(),
                'sixes' => $g->where('ball_type', 'six')->count(),
            ])
            ->sortByDesc('runs');

        $highestScorer = $batsmanRuns->isNotEmpty() ? $batsmanRuns->first() : null;

        // ওভারভিত্তিক রান
        $runsByOver = $balls->groupBy('over_number')
            ->map(fn($g, $over) => [
                'over'        => $over,
                'runs'        => $g->filter(fn($b) => ! $b->is_cut)->sum('runs'),
                'balls_count' => $g->count(),
                'dots'        => $g->where('ball_type', 'dot')->count(),
                'fours'       => $g->where('ball_type', 'four')->count(),
                'sixes'       => $g->where('ball_type', 'six')->count(),
                'wickets'     => $g->where('ball_type', 'wicket')->count(),
                'no_balls'    => $g->where('ball_type', 'no_ball')->count(),
            ])
            ->sortKeys()
            ->values();

        // বলারভিত্তিক মোট রান (শুধু দেখার জন্য)
        $runsByBowler = $balls->filter(fn($b) => ! empty($b->bowler_name))
            ->groupBy('bowler_name')
            ->map(fn($g, $name) => [
                'bowler'   => $name,
                'runs'     => $g->sum('runs'),
                'no_balls' => $g->where('ball_type', 'no_ball')->count(),
                'wickets'  => $g->where('ball_type', 'wicket')->count(),
                'balls'    => $g->count(),
            ])
            ->sortByDesc('runs')
            ->values();

        return [
            'team_id'    => $team->id,
            'team_name'  => $team->name,
            'total_balls'=> $total,
            'dots'       => $dots,
            'fours'      => $fours,
            'sixes'      => $sixes,
            'no_balls'   => $noBalls,
            'byes'       => $byes,
            'wides'      => $wides,
            'wickets'    => $wickets,
            'extra_runs' => $extraRuns,

            // বাই রান
            'bye_runs'           => $byeRuns,
            'byes_cut'           => $byesCut,
            'bye_breakdown'      => $byeBreakdown,    // ✅ টিক মার্ক breakdown

            // নো-বল / ওয়াইড
            'no_ball_runs'       => $noBallRuns,
            'wide_runs'          => $wideRuns,

            // বলারভিত্তিক
            'runs_by_bowler'     => $runsByBowler,
            'bowler_ball_detail' => $bowlerBallDetail, // ✅ প্রতিটা বলের বিস্তারিত
            'highest_scorer'     => $highestScorer,
            'top_bowler'         => $topBowler,
            'wickets_by_bowler'  => $wicketsByBowler,
            'no_ball_by_bowler'  => $noBallByBowler,
            'runs_by_over'       => $runsByOver,
        ];
    }
}
