<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiMeetingController extends Controller
{
    // ── GET /samiti/meetings ──────────────────────────────────────────────
    public function index()
    {
        $meetings = SamitiMeeting::where('user_id', Auth::id())
            ->latest('date')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'meeting_id' => $m->meeting_id,
                'title'      => $m->title,
                'date'       => $m->date?->format('Y-m-d'),
                'time'       => $m->time,
                'venue'      => $m->venue,
                'agenda'     => $m->agenda,
                'notes'      => $m->notes,
                'attendees'  => $m->attendees,
            ]);

        return response()->json(['success' => true, 'data' => $meetings]);
    }

    // ── POST /samiti/meetings ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'date'   => 'required|date',
            'time'   => 'nullable|string|max:20',
            'venue'  => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'notes'  => 'nullable|string',
        ]);

        $userId = Auth::id();
        $count  = SamitiMeeting::where('user_id', $userId)->count();

        $meeting = SamitiMeeting::create([
            'user_id'    => $userId,
            'meeting_id' => 'M' . str_pad($count + 1, 3, '0', STR_PAD_LEFT),
            'title'      => $request->title,
            'date'       => $request->date,
            'time'       => $request->time ?? '10:00 AM',
            'venue'      => $request->venue ?? 'সমিতি ঘর',
            'agenda'     => $request->agenda ?? '',
            'notes'      => $request->notes ?? '',
            'attendees'  => 0,
        ]);

        return response()->json(['success' => true, 'data' => $meeting], 201);
    }

    // ── PUT /samiti/meetings/{id} ──────────────────────────────────────────
    // ✅ নতুন: এডিট করলে এই method call হবে — নতুন record হবে না
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'date'   => 'required|date',
            'time'   => 'nullable|string|max:20',
            'venue'  => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'notes'  => 'nullable|string',
        ]);

        $meeting = SamitiMeeting::where('user_id', Auth::id())->findOrFail($id);

        $meeting->update([
            'title'  => $request->title,
            'date'   => $request->date,
            'time'   => $request->time ?? $meeting->time,
            'venue'  => $request->venue ?? $meeting->venue,
            'agenda' => $request->agenda ?? '',
            'notes'  => $request->notes ?? '',
        ]);

        return response()->json(['success' => true, 'data' => $meeting]);
    }

    // ── DELETE /samiti/meetings/{id} ───────────────────────────────────────
    public function destroy(string $id)
    {
        $meeting = SamitiMeeting::where('user_id', Auth::id())->findOrFail($id);
        $meeting->delete();

        return response()->json(['success' => true, 'message' => 'Meeting deleted']);
    }
}
