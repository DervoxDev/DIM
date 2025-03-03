<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;

class TeamController extends Controller
{
    private function handleImageUpload($image, $oldImagePath = null)
    {
        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }
        
        $path = $image->store('teams', 'public');
        return $path;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Team::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $teams = $query->paginate(15);

        return response()->json([
            'teams' => $teams
        ]);
    }

    public function show(Request $request, $id)
    {
        $team = Team::with('subscription')->find($id);

        if (!$team) {
            return response()->json([
                'error' => true,
                'message' => 'Team not found'
            ], 404);
        }
        if ($team->image_path) {
            $team->image_path = $team->image_path ? Storage::url($team->image_path) : null;
        }
        return response()->json([
            'team' => $team
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $team = new Team($request->except('image'));
        
        if ($request->hasFile('image')) {
            $team->image_path = $this->handleImageUpload($request->file('image'));
        }

        $team->save();

        ActivityLog::create([
            'log_type' => 'Create',
            'model_type' => "Team",
            'model_id' => $team->id,
            'model_identifier' => $team->name,
            'user_identifier' => $request->user()?->name,
            'user_id' => $request->user()->id,
            'user_email' => $request->user()?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => "Team {$team->name} created",
            'new_values' => $team->toArray()
        ]);

        return response()->json([
            'message' => 'Team created successfully',
            'team' => $team
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'error' => true,
                'message' => 'Team not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $team->image_path = $this->handleImageUpload(
                $request->file('image'),
                $team->image_path
            );
        }

        $team->update($request->except('image'));

        ActivityLog::create([
            'log_type' => 'Update',
            'model_type' => "Team",
            'model_id' => $team->id,
            'model_identifier' => $team->name,
            'user_identifier' => $request->user()?->name,
            'user_id' => $request->user()->id,
            'user_email' => $request->user()?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => "Team {$team->name} updated",
            'new_values' => $team->toArray()
        ]);

        return response()->json([
            'message' => 'Team updated successfully',
            'team' => $team
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'error' => true,
                'message' => 'Team not found'
            ], 404);
        }

        // Delete team image if exists
        if ($team->image_path) {
            Storage::disk('public')->delete($team->image_path);
        }

        $team->delete();

        ActivityLog::create([
            'log_type' => 'Delete',
            'model_type' => "Team",
            'model_id' => $team->id,
            'model_identifier' => $team->name,
            'user_identifier' => $request->user()?->name,
            'user_id' => $request->user()->id,
            'user_email' => $request->user()?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => "Team {$team->name} deleted",
            'new_values' => $team->toArray()
        ]);

        return response()->json([
            'message' => 'Team deleted successfully'
        ]);
    }

    public function removeImage(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'error' => true,
                'message' => 'Team not found'
            ], 404);
        }

        if ($team->image_path) {
            Storage::disk('public')->delete($team->image_path);
            $team->image_path = null;
            $team->save();
        }

        return response()->json([
            'message' => 'Image removed successfully'
        ]);
    }

    public function uploadImage(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'error' => true,
                'message' => 'Team not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($team->image_path) {
                Storage::disk('public')->delete($team->image_path);
            }

            $path = $request->file('image')->store('teams', 'public');
            $team->image_path = $path;
            $team->save();

            return response()->json([
                'message' => 'Image uploaded successfully',
                'team' => $team
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to upload image',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
