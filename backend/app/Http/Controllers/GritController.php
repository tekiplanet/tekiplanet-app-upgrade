<?php

namespace App\Http\Controllers;

use App\Models\Grit;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GritController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Grit::with(['category', 'user'])
                ->withCount('applications')
                ->where('status', 'open')
                ->where('admin_approval_status', 'approved');

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $grits = $query->latest()->paginate(10);

            return response()->json($grits);

        } catch (\Exception $e) {
            Log::error('Error fetching grits:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch grits'], 500);
        }
    }

    public function myGrits(Request $request)
    {
        try {
            $grits = Grit::with(['category', 'user'])
                ->withCount('applications')
                ->where('created_by_user_id', Auth::id())
                ->latest()
                ->paginate(10);

            return response()->json(['grits' => $grits]);
        } catch (\Exception $e) {
            Log::error('Error fetching my grits:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch my grits'], 500);
        }
    }

        public function store(Request $request)
    {
        Log::info('Create Grit Request Received:', $request->all());

        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:professional_categories,id',
                'owner_budget' => 'required|numeric|min:0',
                'deadline' => 'required|date',
                'requirements' => 'nullable|string',
                'skills_required' => 'nullable|array',
                'skills_required.*' => 'string|max:255', // Each skill must be a string
            ]);

            // Infer currency from authenticated user's preference3
            $ownerCurrency = strtoupper(optional(Auth::user())->currency_code ?: 'NGN');

            // Normalize requirements: if skills_required provided, map to a comma-separated requirements string
            $requirements = $validatedData['requirements'] ?? null;
            $skills = $request->input('skills_required');
            if (!$requirements && $skills) {
                if (is_array($skills)) {
                    $requirements = implode(', ', array_filter($skills));
                } elseif (is_string($skills)) {
                    $requirements = $skills;
                }
            }

            $grit = Grit::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'category_id' => $validatedData['category_id'],
                // Populate legacy single-currency budget column to satisfy NOT NULL constraint
                'budget' => $validatedData['owner_budget'],
                'owner_budget' => $validatedData['owner_budget'],
                'owner_currency' => $ownerCurrency,
                // Initialize professional terms as empty values (filled during negotiation)
                'professional_budget' => 0,
                'professional_currency' => $ownerCurrency,
                'deadline' => $validatedData['deadline'],
                'requirements' => $requirements,
                'created_by_user_id' => Auth::id(),
                // Keep DB enum valid; use admin_approval_status for the approval workflow
                'status' => 'open',
                'admin_approval_status' => 'pending',
            ]);

            return response()->json($grit, 201);

                } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Grit Creation Validation Failed:', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating grit:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create grit'], 500);
        }
    }

    public function show($id)
    {
        try {
            $grit = Grit::with([
                'category',
                'user',
                'assignedProfessional.user',
                'applications.professional.user',
                'negotiations',
                'disputes',
                'escrowTransactions'
            ])
            ->withCount('applications')
            ->findOrFail($id);

            return response()->json(['grit' => $grit]);

        } catch (\Exception $e) {
            Log::error('Error fetching grit details:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch grit details'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $grit = Grit::findOrFail($id);

            // Check if user owns this GRIT
            if ($grit->created_by_user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if GRIT can be edited (no professional assigned and status is open)
            if ($grit->assigned_professional_id || $grit->status !== 'open') {
                return response()->json(['message' => 'This GRIT cannot be edited. It may have a professional assigned or be in progress.'], 400);
            }

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:professional_categories,id',
                'owner_budget' => 'required|numeric|min:0',
                'deadline' => 'required|date|after:today',
                'requirements' => 'nullable|string',
                'admin_approval_status' => 'required|in:pending,approved,rejected',
            ]);

            // Update the GRIT
            $grit->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'category_id' => $validatedData['category_id'],
                'budget' => $validatedData['owner_budget'], // Legacy field
                'owner_budget' => $validatedData['owner_budget'],
                'deadline' => $validatedData['deadline'],
                'requirements' => $validatedData['requirements'],
                'admin_approval_status' => $validatedData['admin_approval_status'], // Reset to pending
            ]);

            return response()->json(['message' => 'GRIT updated successfully', 'grit' => $grit]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Grit Update Validation Failed:', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating grit:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update grit'], 500);
        }
    }
}
