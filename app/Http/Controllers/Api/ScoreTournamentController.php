<?php
// ════════════════════════════════════════════════════════════
//  app/Http/Controllers/Api/ScoreTournamentController.php
//  ✅ সব query scorehub_tournament_teams তে
//  ✅ tournament_id দিয়ে filter
//  ✅ ড্র সাপোর্ট (winner_id = 0)
//  ✅ পয়েন্ট আপডেট লজিক সহ
// ════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScoreTournament;
use App\Models\ScoreTournamentTeam;
use App\Models\ScoreTournamentFixture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreTournamentController extends Controller
{
    // ══════════════════════════════════════════
    //  GET /api/scorehub/tournaments
    // ══════════════════════════════════════════
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $tournaments = ScoreTournament::forUser($userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($t) => $this->formatTournamentList($t));

        return response()->json(['success' => true, 'data' => $tournaments]);
    }

    // ══════════════════════════════════════════
    //  POST /api/scorehub/tournaments
    // ══════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'           => 'required|string|max:200',
            'description'    => 'nullable|string',
            'entry_fee'      => 'nullable|numeric|min:0',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'venue'          => 'nullable|string|max:200',
            'status'         => 'nullable|in:upcoming,ongoing,completed',
            'overs_per_match'=> 'nullable|integer|min:1|max:100',
            'prize_details'  => 'nullable|string',
        ]);

        $userId = $request->user()->id;

        $tournament = ScoreTournament::create([
            'user_id'        => $userId,
            'name'           => $request->name,
            'description'    => $request->description,
            'entry_fee'      => $request->entry_fee ?? 0,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'venue'          => $request->venue,
            'status'         => $request->status ?? 'upcoming',
            'overs_per_match'=> $request->overs_per_match ?? 20,
            'prize_details'  => $request->prize_details,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTournamentDetail($tournament),
            'message' => 'টুর্নামেন্ট তৈরি হয়েছে ✅',
        ], 201);
    }

    // ══════════════════════════════════════════
    //  GET /api/scorehub/tournaments/{id}
    // ══════════════════════════════════════════
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatTournamentDetail($tournament),
        ]);
    }

    // ══════════════════════════════════════════
    //  PATCH /api/scorehub/tournaments/{id}
    // ══════════════════════════════════════════
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $tournament->update($request->only([
            'name', 'description', 'entry_fee',
            'start_date', 'end_date', 'venue',
            'status', 'overs_per_match', 'prize_details',
        ]));

        return response()->json([
            'success' => true,
            'data'    => $this->formatTournamentDetail($tournament->fresh()),
            'message' => 'আপডেট হয়েছে ✅',
        ]);
    }

    // ══════════════════════════════════════════
    //  DELETE /api/scorehub/tournaments/{id}
    // ══════════════════════════════════════════
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $tournament->delete();

        return response()->json(['success' => true, 'message' => 'মুছে গেছে ✅']);
    }

    // ══════════════════════════════════════════
    //  POST /api/scorehub/tournaments/{id}/teams
    //  নতুন দল যোগ
    // ══════════════════════════════════════════
    public function addTeam(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'captain'        => 'nullable|string|max:100',
            'contact'        => 'nullable|string|max:100',
            'entry_fee_paid' => 'nullable|numeric|min:0',
            'fee_paid'       => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;
        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        // ✅ tournament_id ব্যবহার করে সঠিক টেবিলে insert
        $orderIndex = ScoreTournamentTeam::where('tournament_id', $id)->count();

        $team = ScoreTournamentTeam::create([
            'tournament_id'   => $id,
            'name'            => $request->name,
            'captain'         => $request->captain,
            'contact'         => $request->contact,
            'entry_fee_paid'  => $request->entry_fee_paid ?? 0,
            'fee_paid'        => $request->fee_paid ?? false,
            'matches_played'  => 0,
            'matches_won'     => 0,
            'matches_lost'    => 0,
            'matches_draw'    => 0,
            'points'          => 0,
            'order_index'     => $orderIndex,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTeam($team),
            'message' => 'দল যোগ হয়েছে ✅',
        ], 201);
    }

    // ══════════════════════════════════════════
    //  PATCH /api/scorehub/tournament-teams/{id}
    // ══════════════════════════════════════════
    public function updateTeam(Request $request, int $id): JsonResponse
    {
        $team = ScoreTournamentTeam::find($id);
        if (! $team) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $team->update($request->only([
            'name', 'captain', 'contact', 'entry_fee_paid', 'fee_paid',
        ]));

        return response()->json([
            'success' => true,
            'data'    => $this->formatTeam($team->fresh()),
        ]);
    }

    // ══════════════════════════════════════════
    //  DELETE /api/scorehub/tournament-teams/{id}
    // ══════════════════════════════════════════
    public function removeTeam(Request $request, int $id): JsonResponse
    {
        $team = ScoreTournamentTeam::find($id);
        if (! $team) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $team->delete();

        return response()->json(['success' => true, 'message' => 'দল মুছে গেছে']);
    }

    // ══════════════════════════════════════════
    //  POST /api/scorehub/tournaments/{id}/fixtures
    //  নতুন ম্যাচ শিডিউল
    // ══════════════════════════════════════════
    public function addFixture(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'team_a_id'  => 'required|integer',
            'team_b_id'  => 'required|integer|different:team_a_id',
            'match_date' => 'nullable|date',
            'match_time' => 'nullable|string',
            'venue'      => 'nullable|string|max:200',
            'round'      => 'nullable|string|max:100',
        ]);

        $userId = $request->user()->id;
        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $fixture = ScoreTournamentFixture::create([
            'tournament_id' => $id,
            'team_a_id'     => $request->team_a_id,
            'team_b_id'     => $request->team_b_id,
            'match_date'    => $request->match_date,
            'match_time'    => $request->match_time,
            'venue'         => $request->venue,
            'round'         => $request->round,
            'status'        => 'scheduled',
        ]);

        $fixture->load(['teamA', 'teamB', 'winner']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatFixture($fixture),
            'message' => 'ম্যাচ শিডিউল হয়েছে ✅',
        ], 201);
    }

    // ══════════════════════════════════════════
    //  PATCH /api/scorehub/fixtures/{id}
    //  ✅ ড্র সাপোর্ট: winner_id = 0 হলে ড্র
    // ══════════════════════════════════════════
    public function updateFixture(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'team_a_score'    => 'nullable|integer|min:0',
            'team_b_score'    => 'nullable|integer|min:0',
            'team_a_wickets'  => 'nullable|integer|min:0|max:10',
            'team_b_wickets'  => 'nullable|integer|min:0|max:10',
            'team_a_overs'    => 'nullable|string|max:10',
            'team_b_overs'    => 'nullable|string|max:10',
            'winner_id'       => 'nullable|integer',  // 0 = ড্র
            'mom_player_name' => 'nullable|string|max:100',
            'mom_runs'        => 'nullable|integer|min:0',
            'mom_fours'       => 'nullable|integer|min:0',
            'mom_sixes'       => 'nullable|integer|min:0',
            'status'          => 'nullable|in:scheduled,live,completed',
            'result_summary'  => 'nullable|string',
        ]);

        $fixture = ScoreTournamentFixture::find($id);
        if (! $fixture) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $winnerId  = $request->winner_id;  // 0 = ড্র, null = অনির্ধারিত, >0 = বিজয়ী
        $isDraw    = $winnerId === 0;
        $hasWinner = $winnerId > 0;

        // ✅ পয়েন্ট আপডেট — আগে পুরোনো ফলাফল রিভার্স করো
        if ($fixture->status === 'completed') {
            $this->reversePoints($fixture);
        }

        $fixture->update([
            'team_a_score'       => $request->team_a_score,
            'team_b_score'       => $request->team_b_score,
            'team_a_wickets'     => $request->team_a_wickets,
            'team_b_wickets'     => $request->team_b_wickets,
            'team_a_overs'       => $request->team_a_overs,
            'team_b_overs'       => $request->team_b_overs,
            'winner_team_id'     => $hasWinner ? $winnerId : null,
            'man_of_match_name'  => $request->mom_player_name,
            'man_of_match_runs'  => $request->mom_runs,
            'man_of_match_fours' => $request->mom_fours,
            'man_of_match_sixes' => $request->mom_sixes,
            'result_summary'     => $request->result_summary,
            'status'             => $request->status ?? $fixture->status,
        ]);

        // ✅ নতুন পয়েন্ট যোগ করো
        if ($request->status === 'completed') {
            if ($isDraw) {
                $this->applyDraw($fixture);
            } elseif ($hasWinner) {
                $this->applyWin($fixture, $winnerId);
            }
        }

        $fixture->load(['teamA', 'teamB', 'winner']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatFixture($fixture),
            'message' => 'ফলাফল আপডেট হয়েছে ✅',
        ]);
    }

    // ══════════════════════════════════════════
    //  DELETE /api/scorehub/fixtures/{id}
    // ══════════════════════════════════════════
    public function destroyFixture(Request $request, int $id): JsonResponse
    {
        $fixture = ScoreTournamentFixture::find($id);
        if (! $fixture) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        // পয়েন্ট রিভার্স করো
        if ($fixture->status === 'completed') {
            $this->reversePoints($fixture);
        }

        $fixture->delete();

        return response()->json(['success' => true, 'message' => 'ম্যাচ মুছে গেছে']);
    }

    // ══════════════════════════════════════════
    //  GET /api/scorehub/tournaments/{id}/points-table
    // ══════════════════════════════════════════
    public function pointsTable(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $teams = ScoreTournamentTeam::where('tournament_id', $id)
            ->orderByDesc('points')
            ->orderByDesc('matches_won')
            ->get()
            ->map(fn($t) => $this->formatTeam($t));

        return response()->json(['success' => true, 'data' => $teams]);
    }

    // ══════════════════════════════════════════
    //  GET /api/scorehub/tournaments/{id}/analysis
    // ══════════════════════════════════════════
    public function analysis(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $tournament = ScoreTournament::forUser($userId)->find($id);
        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'পাওয়া যায়নি'], 404);
        }

        $fixtures = ScoreTournamentFixture::where('tournament_id', $id)
            ->where('status', 'completed')
            ->get();

        $totalMatches = $fixtures->count();
        $totalFours   = $fixtures->sum('man_of_match_fours');
        $totalSixes   = $fixtures->sum('man_of_match_sixes');

        // সর্বোচ্চ রান
        $topScorer = null;
        $momList   = [];
        foreach ($fixtures as $f) {
            if ($f->man_of_match_name) {
                $entry = [
                    'player_name' => $f->man_of_match_name,
                    'runs'        => $f->man_of_match_runs  ?? 0,
                    'fours'       => $f->man_of_match_fours ?? 0,
                    'sixes'       => $f->man_of_match_sixes ?? 0,
                    'match_date'  => $f->match_date?->format('Y-m-d'),
                ];
                $momList[] = $entry;

                if (! $topScorer || ($entry['runs'] > $topScorer['runs'])) {
                    $topScorer = [
                        'name'  => $f->man_of_match_name,
                        'runs'  => $f->man_of_match_runs  ?? 0,
                        'fours' => $f->man_of_match_fours ?? 0,
                        'sixes' => $f->man_of_match_sixes ?? 0,
                    ];
                }
            }
        }

        // দলের পারফরম্যান্স চার্ট
        $teams = ScoreTournamentTeam::where('tournament_id', $id)
            ->orderByDesc('points')
            ->get()
            ->map(fn($t) => [
                'name'    => $t->name,
                'points'  => $t->points,
                'wins'    => $t->matches_won,
                'played'  => $t->matches_played,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'total_matches' => $totalMatches,
                'total_fours'   => $totalFours,
                'total_sixes'   => $totalSixes,
                'top_scorer'    => $topScorer,
                'mom_list'      => $momList,
                'teams_chart'   => $teams,
            ],
        ]);
    }

    // ══════════════════════════════════════════
    //  Private: Points Logic
    // ══════════════════════════════════════════

    private function applyWin(ScoreTournamentFixture $f, int $winnerId): void
    {
        $loserId = ($winnerId === $f->team_a_id) ? $f->team_b_id : $f->team_a_id;

        ScoreTournamentTeam::where('id', $winnerId)->increment('matches_won');
        ScoreTournamentTeam::where('id', $winnerId)->increment('matches_played');
        ScoreTournamentTeam::where('id', $winnerId)->increment('points', 2);

        ScoreTournamentTeam::where('id', $loserId)->increment('matches_lost');
        ScoreTournamentTeam::where('id', $loserId)->increment('matches_played');
    }

    private function applyDraw(ScoreTournamentFixture $f): void
    {
        foreach ([$f->team_a_id, $f->team_b_id] as $teamId) {
            ScoreTournamentTeam::where('id', $teamId)->increment('matches_draw');
            ScoreTournamentTeam::where('id', $teamId)->increment('matches_played');
            ScoreTournamentTeam::where('id', $teamId)->increment('points', 1);
        }
    }

    private function reversePoints(ScoreTournamentFixture $f): void
    {
        $winnerId = $f->winner_team_id;
        $isDraw   = is_null($winnerId) && $f->status === 'completed';

        if ($winnerId) {
            $loserId = ($winnerId === $f->team_a_id) ? $f->team_b_id : $f->team_a_id;
            ScoreTournamentTeam::where('id', $winnerId)->decrement('matches_won');
            ScoreTournamentTeam::where('id', $winnerId)->decrement('matches_played');
            ScoreTournamentTeam::where('id', $winnerId)->decrement('points', 2);
            ScoreTournamentTeam::where('id', $loserId)->decrement('matches_lost');
            ScoreTournamentTeam::where('id', $loserId)->decrement('matches_played');
        } elseif ($isDraw) {
            foreach ([$f->team_a_id, $f->team_b_id] as $teamId) {
                ScoreTournamentTeam::where('id', $teamId)->decrement('matches_draw');
                ScoreTournamentTeam::where('id', $teamId)->decrement('matches_played');
                ScoreTournamentTeam::where('id', $teamId)->decrement('points', 1);
            }
        }
    }

    // ══════════════════════════════════════════
    //  Private: Formatters
    // ══════════════════════════════════════════

    private function formatTournamentList(ScoreTournament $t): array
    {
        return [
            'id'             => $t->id,
            'name'           => $t->name,
            'description'    => $t->description,
            'entry_fee'      => (float) $t->entry_fee,
            'start_date'     => $t->start_date?->format('Y-m-d'),
            'end_date'       => $t->end_date?->format('Y-m-d'),
            'venue'          => $t->venue,
            'status'         => $t->status,
            'overs_per_match'=> $t->overs_per_match,
            'prize_details'  => $t->prize_details,
            // ✅ subquery count — model method ব্যবহার না করে
            'teams_count'    => ScoreTournamentTeam::where('tournament_id', $t->id)->count(),
            'fixtures_count' => ScoreTournamentFixture::where('tournament_id', $t->id)->count(),
        ];
    }

    private function formatTournamentDetail(ScoreTournament $t): array
    {
        $teams    = ScoreTournamentTeam::where('tournament_id', $t->id)
                        ->orderBy('order_index')->get()
                        ->map(fn($tm) => $this->formatTeam($tm));

        $fixtures = ScoreTournamentFixture::where('tournament_id', $t->id)
                        ->with(['teamA', 'teamB', 'winner'])
                        ->orderBy('match_date')->orderBy('created_at')->get()
                        ->map(fn($f) => $this->formatFixture($f));

        return [
            'id'             => $t->id,
            'name'           => $t->name,
            'description'    => $t->description,
            'entry_fee'      => (float) $t->entry_fee,
            'start_date'     => $t->start_date?->format('Y-m-d'),
            'end_date'       => $t->end_date?->format('Y-m-d'),
            'venue'          => $t->venue,
            'status'         => $t->status,
            'overs_per_match'=> $t->overs_per_match,
            'prize_details'  => $t->prize_details,
            'teams_count'    => $teams->count(),
            'fixtures_count' => $fixtures->count(),
            'teams'          => $teams,
            'fixtures'       => $fixtures,
        ];
    }

    private function formatTeam(ScoreTournamentTeam $t): array
    {
        return [
            'id'             => $t->id,
            'tournament_id'  => $t->tournament_id,
            'name'           => $t->name,
            'captain'        => $t->captain,
            'contact'        => $t->contact,
            'entry_fee_paid' => (float) $t->entry_fee_paid,
            'fee_paid'       => (bool) $t->fee_paid,
            'matches_played' => $t->matches_played,
            'matches_won'    => $t->matches_won,
            'matches_lost'   => $t->matches_lost,
            'matches_draw'   => $t->matches_draw,
            'points'         => $t->points,
            'order_index'    => $t->order_index,
        ];
    }

    private function formatFixture(ScoreTournamentFixture $f): array
    {
        return [
            'id'                 => $f->id,
            'tournament_id'      => $f->tournament_id,
            'team_a_id'          => $f->team_a_id,
            'team_b_id'          => $f->team_b_id,
            'team_a_name'        => $f->teamA?->name ?? '',
            'team_b_name'        => $f->teamB?->name ?? '',
            'match_date'         => $f->match_date?->format('Y-m-d'),
            'match_time'         => $f->match_time,
            'venue'              => $f->venue,
            'round'              => $f->round,
            'team_a_score'       => $f->team_a_score,
            'team_a_wickets'     => $f->team_a_wickets,
            'team_a_overs'       => $f->team_a_overs,
            'team_b_score'       => $f->team_b_score,
            'team_b_wickets'     => $f->team_b_wickets,
            'team_b_overs'       => $f->team_b_overs,
            'winner_team_id'     => $f->winner_team_id,
            'winner_name'        => $f->winner?->name,
            'man_of_match_name'  => $f->man_of_match_name,
            'man_of_match_runs'  => $f->man_of_match_runs,
            'man_of_match_fours' => $f->man_of_match_fours,
            'man_of_match_sixes' => $f->man_of_match_sixes,
            'result_summary'     => $f->result_summary,
            'status'             => $f->status,
        ];
    }
}
