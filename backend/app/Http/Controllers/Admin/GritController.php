<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GritController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Grit::with(['category', 'user'])->latest();

            if ($request->has('status')) {
                $query->where('admin_approval_status', $request->status);
            }

            $grits = $query->paginate(15);

            return response()->json($grits);
        } catch (\Exception $e) {
            Log::error('Error fetching grits for admin:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch grits'], 500);
        }
    }

    public function updateApprovalStatus(Request $request, Grit $grit)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:approved,rejected',
            ]);

            $grit->admin_approval_status = $validated['status'];
            $grit->save();

            // TODO: Add notification to the business owner who created the Grit.

            return response()->json($grit);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating grit approval status:', ['grit_id' => $grit->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update grit status'], 500);
        }
    }
}
