<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConversionTask;
use App\Models\ConversionTaskType;
use App\Models\ConversionRewardType;
use App\Models\ConversionTaskReward;

class ConversionTaskController extends Controller
{
    public function index()
    {
        $tasks = ConversionTask::with(['type', 'rewardType', 'rewards'])->get();
        $taskTypes = ConversionTaskType::all();
        $rewardTypes = ConversionRewardType::all();
        $products = \App\Models\Product::all(['id', 'name']);
        $coupons = \App\Models\Coupon::all(['id', 'code']);
        $courses = \App\Models\Course::all(['id', 'title']);
        return view('admin.conversion-tasks.index', compact('tasks', 'taskTypes', 'rewardTypes', 'products', 'coupons', 'courses'));
    }

    public function create()
    {
        $taskTypes = ConversionTaskType::all();
        $rewardTypes = ConversionRewardType::all();
        $products = \App\Models\Product::all(['id', 'name']);
        $coupons = \App\Models\Coupon::all(['id', 'code']);
        $courses = \App\Models\Course::all(['id', 'title']);
        return view('admin.conversion-tasks.create', compact('taskTypes', 'rewardTypes', 'products', 'coupons', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type_id' => 'required|exists:conversion_task_types,id',
            'min_points' => 'required|integer|min:0',
            'max_points' => 'required|integer|min:0|gte:min_points',
            'reward_type_id' => 'required|exists:conversion_reward_types,id',
            'referral_target' => 'nullable|integer|min:1',
            // Reward-specific fields
            'product_id' => 'nullable|exists:products,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'course_id' => 'nullable|exists:courses,id',
            'cash_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'service_name' => 'nullable|string|max:255',
        ]);
        
        $task = ConversionTask::create($validated);
        return redirect()->route('admin.conversion-tasks.index')->with('success', 'Task created successfully.');
    }

    public function edit(ConversionTask $conversionTask)
    {
        $taskTypes = ConversionTaskType::all();
        $rewardTypes = ConversionRewardType::all();
        $products = \App\Models\Product::all(['id', 'name']);
        $coupons = \App\Models\Coupon::all(['id', 'code']);
        $courses = \App\Models\Course::all(['id', 'title']);
        $conversionTask->load('rewards');
        return view('admin.conversion-tasks.edit', compact('conversionTask', 'taskTypes', 'rewardTypes', 'products', 'coupons', 'courses'));
    }

    public function update(Request $request, ConversionTask $conversionTask)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type_id' => 'required|exists:conversion_task_types,id',
            'min_points' => 'required|integer|min:0',
            'max_points' => 'required|integer|min:0|gte:min_points',
            'reward_type_id' => 'required|exists:conversion_reward_types,id',
            'referral_target' => 'nullable|integer|min:1',
            // Reward-specific fields
            'product_id' => 'nullable|exists:products,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'course_id' => 'nullable|exists:courses,id',
            'cash_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'service_name' => 'nullable|string|max:255',
        ]);
        
        $conversionTask->update($validated);
        return redirect()->route('admin.conversion-tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(ConversionTask $conversionTask)
    {
        $conversionTask->delete();
        return redirect()->route('admin.conversion-tasks.index')->with('success', 'Task deleted successfully.');
    }
}
