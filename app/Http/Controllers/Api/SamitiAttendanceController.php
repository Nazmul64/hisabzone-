<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiAttendance;
use App\Models\SamitiMeeting;
use App\Models\SamitiMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $userId    = Auth::id();
        $meetingId = $request->get('meeting_id');

        // সব সভার তালিকা
        $meetings = SamitiMeeting::where('user_id', $userId)
            ->latest('date')
            ->get();

        if ($meetings->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => ['meetings' => [], 'records' => [], 'selected_meeting' => null],
            ]);
        }

        // নির্বাচিত সভা (অথবা সর্বশেষ সভা)
        $selectedMeeting = $meetingId
            ? SamitiMeeting::where('user_id', $userId)->find($meetingId)
            : $meetings->first();

        if (!$selectedMeeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found'], 404);
        }

        // সব সদস্যের জন্য attendance record auto-create
        $members = SamitiMember::where('user_id', $userId)->where('status', 'active')->get();
        foreach ($members as $member) {
            SamitiAttendance::firstOrCreate(
                [
                    'user_id'           => $userId,
                    'samiti_member_id'  => $member->id,
                    'samiti_meeting_id' => $selectedMeeting->id,
                ],
                ['is_present' => false]
            );
        }

        // প্রতি সদস্যের attendance rate হিসাব করো
        $records = SamitiMember::where('user_id', $userId)
            ->where('status', 'active')
            ->get()
            ->map(function ($m) use ($userId, $meetings, $selectedMeeting) {
                // সব সভায় উপস্থিতির map
                $attendanceMap = [];
                foreach ($meetings as $mtg) {
                    $att = SamitiAttendance::where('user_id', $userId)
                        ->where('samiti_member_id', $m->id)
                        ->where('samiti_meeting_id', $mtg->id)
                        ->first();
                    $attendanceMap[$mtg->date?->format('Y-m-d')] = $att?->is_present ?? false;
                }

                $totalMeetings = count($attendanceMap);
                $presentCount  = collect($attendanceMap)->filter()->count();

                return [
                    'id'               => $m->id,
                    'member_id'        => $m->member_id,
                    'member_name'      => $m->name,
                    'attendance'       => $attendanceMap,
                    'present_count'    => $presentCount,
                    'absent_count'     => $totalMeetings - $presentCount,
                    'rate'             => $totalMeetings > 0 ? round($presentCount / $totalMeetings, 4) : 0,
                    // নির্বাচিত সভায় উপস্থিত কিনা
                    'is_present_today' => $attendanceMap[$selectedMeeting->date?->format('Y-m-d')] ?? false,
                    'attendance_id'    => SamitiAttendance::where('user_id', $userId)
                        ->where('samiti_member_id', $m->id)
                        ->where('samiti_meeting_id', $selectedMeeting->id)
                        ->value('id'),
                ];
            });

        $presentCount = $records->where('is_present_today', true)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'meetings' => $meetings->map(fn($m) => [
                    'id'    => $m->id,
                    'date'  => $m->date?->format('Y-m-d'),
                    'title' => $m->title,
                ]),
                'selected_meeting' => [
                    'id'    => $selectedMeeting->id,
                    'date'  => $selectedMeeting->date?->format('Y-m-d'),
                    'title' => $selectedMeeting->title,
                ],
                'records'       => $records->values(),
                'present_count' => $presentCount,
                'absent_count'  => $records->count() - $presentCount,
                'total_count'   => $records->count(),
            ],
        ]);
    }

    public function toggle(string $id)
    {
        $att             = SamitiAttendance::where('user_id', Auth::id())->findOrFail($id);
        $att->is_present = !$att->is_present;
        $att->save();

        return response()->json(['success' => true, 'data' => $att]);
    }
}
