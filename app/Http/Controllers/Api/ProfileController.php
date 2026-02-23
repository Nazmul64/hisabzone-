<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল ৩: app/Http/Controllers/Api/ProfileController.php
// ════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\FinanceManage;
use App\Models\Saving;
use App\Models\Task;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * প্রোফাইল ডেটা + stats
     */
    public function show()
    {
        $profile = Profile::getSingleton();

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
                'id'                 => $profile->id,
                'name'               => $profile->name,
                'email'              => $profile->email ?? '',
                'phone'              => $profile->phone ?? '',
                'member_since'       => $profile->created_at?->format('d M Y') ?? '',
                'total_transactions' => $totalTransactions,
                'total_savings'      => $totalSavings,
                'total_tasks'        => $totalTasks,
            ],
        ]);
    }

    /**
     * POST /api/profile
     * প্রোফাইল আপডেট
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $profile = Profile::getSingleton();
        $profile->update($data);

        return response()->json([
            'success' => true,
            'message' => 'প্রোফাইল সংরক্ষিত হয়েছে',
            'data'    => [
                'id'           => $profile->id,
                'name'         => $profile->name,
                'email'        => $profile->email ?? '',
                'phone'        => $profile->phone ?? '',
                'member_since' => $profile->created_at?->format('d M Y') ?? '',
            ],
        ]);
    }
}



