<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল: app/Http/Controllers/Api/ProfileController.php
// ════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceManage;
use App\Models\Saving;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * লগইন করা ইউজারের প্রোফাইল + stats
     */
    public function show()
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        // Stats — model না থাকলে 0 দেখাবে
        $totalTransactions = 0;
        $totalSavings      = 0;
        $totalTasks        = 0;

        if (class_exists(FinanceManage::class)) {
            $totalTransactions = FinanceManage::count();
        }
        if (class_exists(Saving::class)) {
            $totalSavings = Saving::count();
        }
        if (class_exists(Task::class)) {
            $totalTasks = Task::count();
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
     * লগইন করা ইউজারের প্রোফাইল আপডেট
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
    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
        ]);

        // বর্তমান পাসওয়ার্ড চেক
        if (! \Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'বর্তমান পাসওয়ার্ড সঠিক নয়',
            ], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'পাসওয়ার্ড পরিবর্তন হয়েছে',
        ]);
    }
}
