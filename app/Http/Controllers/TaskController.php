<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Log;
use App\Notifications\TaskCompletedNotification;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // get the specified user's tasks
        $query = Task::where('user_id', $request->user()->id)->with('category');

        // filter the tasks by category_id and is_completed if they are provided in the request
        if($request->has('category_id')){
            $query = $query->where('category_id', $request->input('category_id'));
        }

        if($request->has('is_completed')){
            $query = $query->where('is_completed', $request->input('is_completed'));
        }

        // search for tasks by title or description if a search term is provided in the request
        if($request->has('search')){
            $query = $query->where(function($q) use ($request){
                $q->where('title', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('description', 'like', '%' . $request->input('search') . '%')
                    ->orWhereHas('category', function($q) use ($request){
                        $q->where('name', 'like', '%' . $request->input('search') . '%');
                    });
            });
        };

        // Date range filter (created_at)
        if($request->has('from_date')){
            $query = $query->whereDate('created_at', '>=', $request->input('from_date'));
        };

        if($request->has('to_date')){
            $query = $query->whereDate('created_at', '<=', $request->input('to_date'));
        };

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');

        $allowedSortFields = ['title', 'created_at', 'updated_at', 'is_completed'];
        if(!in_array($sortField, $allowedSortFields)){
            $sortField = 'created_at';
        }

        // Ensure sort order is either 'asc' or 'desc'
        $query = $query->orderBy($sortField, $sortOrder == 'asc' ? 'asc' : 'desc');
        $tasks = $query->latest()->paginate($request->input('per_page', 5));

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        // create a new task for the authenticated user
        $task = Task::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description ?? null,
            'category_id' => $request->category_id ?? null,
            'is_completed' => $request->is_completed ?? false,
            'due_date' => $request->due_date ?? null,
        ]);

        return new TaskResource($task->load('category'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task)
    {
        // return the specified task if it belongs to the authenticated user
       $this->authorize('view', $task);

        // return the task as a JSON response
        return new TaskResource($task->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        // update the specified task if it belongs to the authenticated user
        $this->authorize('update', $task);

        $wasCompleted = $task->is_completed;

        // update the task with the validated data
        $task->update($request->validated());

        // if it was completed after the update, send a notification to the user
        if(!$wasCompleted && $task->is_completed){
            // send a notification to the user that the task is completed
            $task->user->notify(new TaskCompletedNotification($task));
        }
        return new TaskResource($task->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        // delete the specified task if it belongs to the authenticated user
        $this->authorize('delete', $task);

        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id){
        // restore the specified task if it belongs to the authenticated user
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('update', $task);

        // restore the task
        $task->restore();
        return response()->json(['message' => 'Task restored successfully', 'task' => new TaskResource($task->load('category'))]);
    }
}
