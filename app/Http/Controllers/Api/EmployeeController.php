<?php
// ═══════════════════════════════════════════════════════════
//  EmployeeController
// ═══════════════════════════════════════════════════════════
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TailorEmployee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private function uid(Request $r): int { return $r->user()->id; }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => TailorEmployee::where('user_id', $this->uid($request))->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string',
            'phone'  => 'required|string',
            'salary' => 'nullable|numeric',
            'role'   => 'nullable|string',
        ]);

        $emp = TailorEmployee::create([...$data, 'user_id' => $this->uid($request)]);
        return response()->json(['success' => true, 'message' => 'কর্মচারী যোগ হয়েছে', 'data' => $emp], 201);
    }

    public function show(Request $request, $id)
    {
        $emp = TailorEmployee::where('user_id', $this->uid($request))->findOrFail($id);
        return response()->json(['success' => true, 'data' => $emp]);
    }

    public function update(Request $request, $id)
    {
        $emp = TailorEmployee::where('user_id', $this->uid($request))->findOrFail($id);
        $emp->update($request->validate([
            'name'            => 'sometimes|string',
            'phone'           => 'sometimes|string',
            'salary'          => 'nullable|numeric',
            'role'            => 'nullable|string',
            'assigned_orders' => 'nullable|array',
        ]));
        return response()->json(['success' => true, 'message' => 'আপডেট হয়েছে', 'data' => $emp]);
    }

    public function destroy(Request $request, $id)
    {
        TailorEmployee::where('user_id', $this->uid($request))->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'মুছে ফেলা হয়েছে']);
    }
}

