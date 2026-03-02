<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = SamitiMember::where('user_id', Auth::id());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('member_id', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%");
            });
        }

        $members = $query->latest()->get()->map(function ($m) {
            return [
                'id'            => $m->id,
                'member_id'     => $m->member_id,
                'name'          => $m->name,
                'phone'         => $m->phone,
                'address'       => $m->address,
                'nid'           => $m->nid,
                'join_date'     => $m->join_date?->format('Y-m-d'),
                'status'        => $m->status,
                'total_savings' => $m->total_savings,
                'total_loan'    => $m->total_loan,
                'total_due'     => $m->total_due,
            ];
        });

        return response()->json(['success' => true, 'data' => $members]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'nid'     => 'nullable|string|max:50',
        ]);

        $userId   = Auth::id();
        $count    = SamitiMember::where('user_id', $userId)->count();
        $memberId = 'SM' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $member = SamitiMember::create([
            'user_id'   => $userId,
            'member_id' => $memberId,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'address'   => $request->address,
            'nid'       => $request->nid,
            'join_date' => now()->toDateString(),
            'status'    => 'active',
        ]);

        return response()->json(['success' => true, 'data' => $member], 201);
    }

    public function update(Request $request, string $id)
    {
        $member = SamitiMember::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'nid'     => 'nullable|string|max:50',
            'status'  => 'sometimes|in:active,inactive',
        ]);

        $member->update($request->only(['name', 'phone', 'address', 'nid', 'status']));

        return response()->json(['success' => true, 'data' => $member]);
    }

    public function destroy(string $id)
    {
        $member = SamitiMember::where('user_id', Auth::id())->findOrFail($id);
        $member->delete();

        return response()->json(['success' => true, 'message' => 'Member deleted']);
    }
}
