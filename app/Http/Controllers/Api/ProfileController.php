<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল: app/Http/Controllers/Api/ProfileController.php
// ✅ show, update, changePassword — সব কাজ করবে
// ════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     */
    public function show()
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        $totalTransactions = 0;
        $totalSavings      = 0;
        $totalTasks        = 0;

        if (class_exists(\App\Models\FinanceManage::class)) {
            $totalTransactions = \App\Models\FinanceManage::count();
        }
        if (class_exists(\App\Models\Saving::class)) {
            $totalSavings = \App\Models\Saving::count();
        }
        if (class_exists(\App\Models\Task::class)) {
            $totalTasks = \App\Models\Task::count();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email ?? '',
                'phone'              => $user->phone ?? '',
                'member_since'       => $user->created_at?->format('d M Y') ?? '',
                'total_transactions' => $totalTransactions,
                'total_savings'      => $totalSavings,
                'total_tasks'        => $totalTasks,
            ],
        ]);
    }

    /**
     * POST /api/profile
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'প্রোফাইল সংরক্ষিত হয়েছে',
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email ?? '',
                'phone'        => $user->phone ?? '',
                'member_since' => $user->created_at?->format('d M Y') ?? '',
            ],
        ]);
    }

    /**
     * POST /api/profile/change-password
     * ✅ current_password + new_password + new_password_confirmation
     */
    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        // বর্তমান পাসওয়ার্ড চেক
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'বর্তমান পাসওয়ার্ড সঠিক নয়',
                'errors'  => [
                    'current_password' => ['বর্তমান পাসওয়ার্ড সঠিক নয়'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'পাসওয়ার্ড পরিবর্তন হয়েছে ✅',
        ]);
    }
}
