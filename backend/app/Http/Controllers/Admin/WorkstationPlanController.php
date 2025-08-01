<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkstationPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkstationPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = WorkstationPlan::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->withCount('subscriptions')
            ->latest()
            ->paginate(10);

        return view('admin.workstation.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.workstation.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'print_pages_limit' => 'required|integer|min:0',
            'meeting_room_hours' => 'required|integer|min:-1',
            'has_locker' => 'required|boolean',
            'has_dedicated_support' => 'required|boolean',
            'allows_installments' => 'required|boolean',
            'installment_months' => 'required_if:allows_installments,true|nullable|integer|min:1',
            'installment_amount' => 'required_if:allows_installments,true|nullable|numeric|min:0',
            'features' => 'required|array',
            'features.*' => 'required|string',
            'is_active' => 'required|boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        WorkstationPlan::create($validated);

        return redirect()
            ->route('admin.workstation.plans.index')
            ->with('success', 'Plan created successfully');
    }

    public function show(WorkstationPlan $plan)
    {
        $plan->loadCount('subscriptions');
        return view('admin.workstation.plans.show', compact('plan'));
    }

    public function edit(WorkstationPlan $plan)
    {
        return view('admin.workstation.plans.edit', compact('plan'));
    }

    public function update(Request $request, WorkstationPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'print_pages_limit' => 'required|integer|min:0',
            'meeting_room_hours' => 'required|integer|min:-1',
            'has_locker' => 'required|boolean',
            'has_dedicated_support' => 'required|boolean',
            'allows_installments' => 'required|boolean',
            'installment_months' => 'required_if:allows_installments,true|nullable|integer|min:1',
            'installment_amount' => 'required_if:allows_installments,true|nullable|numeric|min:0',
            'features' => 'required|array',
            'features.*' => 'required|string',
            'is_active' => 'required|boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $plan->update($validated);

        return redirect()
            ->route('admin.workstation.plans.show', $plan)
            ->with('success', 'Plan updated successfully');
    }

    public function toggleStatus(WorkstationPlan $plan)
    {
        try {
            $plan->is_active = !$plan->is_active;
            $plan->save();

            return response()->json([
                'success' => true,
                'message' => $plan->is_active ? 'Plan activated successfully.' : 'Plan deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan status.'
            ], 500);
        }
    }
} 