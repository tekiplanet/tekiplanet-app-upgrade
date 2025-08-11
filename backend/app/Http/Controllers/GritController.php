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
            $query = Grit::with(['category', 'applications', 'user'])
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

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:professional_categories,id',
                'owner_budget' => 'required|numeric|min:0',
                'deadline' => 'required|date',
                'requirements' => 'nullable|string',
                'skills_required' => 'nullable', // can be array or comma-separated string from frontend
            ]);

            // Infer currency from authenticated user's preference
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
                'owner_budget' => $validatedData['owner_budget'],
                'owner_currency' => $ownerCurrency,
                'deadline' => $validatedData['deadline'],
                'requirements' => $requirements,
                'created_by_user_id' => Auth::id(),
                'status' => 'pending_admin_approval',
                'admin_approval_status' => 'pending',
            ]);

            return response()->json($grit, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
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
            ])->findOrFail($id);

            return response()->json(['grit' => $grit]);

        } catch (\Exception $e) {
            Log::error('Error fetching grit details:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch grit details'], 500);
        }
    }
}
