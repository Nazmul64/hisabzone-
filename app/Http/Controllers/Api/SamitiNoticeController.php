<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiNoticeController extends Controller
{
    public function index()
    {
        $userId  = Auth::id();
        $notices = SamitiNotice::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'title'    => $n->title,
                'body'     => $n->body,
                'category' => $n->category,
                'date'     => $n->date?->format('Y-m-d'),
                'is_read'  => $n->is_read,
            ]);

        $unreadCount = SamitiNotice::where('user_id', $userId)->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'notices'      => $notices,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'nullable|string',
            'category' => 'required|string|max:50',
            'date'     => 'nullable|date',
        ]);

        $userId = Auth::id();
        $count  = SamitiNotice::where('user_id', $userId)->count();

        $notice = SamitiNotice::create([
            'user_id'   => $userId,
            'notice_id' => 'N' . str_pad($count + 1, 3, '0', STR_PAD_LEFT),
            'title'     => $request->title,
            'body'      => $request->body ?? '',
            'category'  => $request->category,
            'date'      => $request->date ?? now()->toDateString(),
            'is_read'   => false,
        ]);

        return response()->json(['success' => true, 'data' => $notice], 201);
    }

    public function markRead(string $id)
    {
        $notice          = SamitiNotice::where('user_id', Auth::id())->findOrFail($id);
        $notice->is_read = true;
        $notice->save();

        return response()->json(['success' => true, 'data' => $notice]);
    }

    public function markAllRead()
    {
        SamitiNotice::where('user_id', Auth::id())->update(['is_read' => true]);

        return response()->json(['success' => true, 'message' => 'All notices marked as read']);
    }

    public function destroy(string $id)
    {
        $notice = SamitiNotice::where('user_id', Auth::id())->findOrFail($id);
        $notice->delete();

        return response()->json(['success' => true, 'message' => 'Notice deleted']);
    }
}
