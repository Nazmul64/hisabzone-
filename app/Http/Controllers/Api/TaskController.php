<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $tasks = Task::latest()->get()->map(fn($t) => $this->format($t));
            return response()->json(['success' => true, 'data' => $tasks]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'      => 'required|string|max:255',
                'due_date'   => 'nullable|date',
                'priority'   => 'nullable|in:low,medium,high',
                'note'       => 'nullable|string',
                'is_done'    => 'nullable|boolean',
            ]);

            $task = Task::create([
                'title'    => $request->title,
                'due_date' => $request->due_date,
                'priority' => $request->input('priority', 'medium'),
                'note'     => $request->note,
                'is_done'  => $request->input('is_done', false),
            ]);

            return response()->json(['success' => true, 'data' => $this->format($task), 'message' => 'Task created'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        try {
            $task->update($request->only(['title', 'due_date', 'priority', 'note', 'is_done']));
            return response()->json(['success' => true, 'data' => $this->format($task->fresh())]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted']);
    }

    // Toggle done/undone
    public function toggleDone(string $id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        $task->update(['is_done' => !$task->is_done]);
        return response()->json(['success' => true, 'data' => $this->format($task->fresh())]);
    }

    private function format($t): array
    {
        return [
            'id'       => $t->id,
            'title'    => $t->title,
            'due_date' => $t->due_date,
            'priority' => $t->priority,
            'note'     => $t->note,
            'is_done'  => (bool) $t->is_done,
        ];
    }
}
