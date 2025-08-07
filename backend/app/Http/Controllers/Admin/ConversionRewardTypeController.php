<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConversionRewardType;

class ConversionRewardTypeController extends Controller
{
    public function index()
    {
        $types = ConversionRewardType::all();
        return view('admin.conversion-reward-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.conversion-reward-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:conversion_reward_types,name',
            'description' => 'nullable|string',
        ]);
        ConversionRewardType::create($validated);
        return redirect()->route('admin.conversion-reward-types.index')->with('success', 'Reward type created successfully.');
    }

    public function edit(ConversionRewardType $conversionRewardType)
    {
        return view('admin.conversion-reward-types.edit', compact('conversionRewardType'));
    }

    public function update(Request $request, ConversionRewardType $conversionRewardType)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:conversion_reward_types,name,' . $conversionRewardType->id,
            'description' => 'nullable|string',
        ]);
        $conversionRewardType->update($validated);
        return redirect()->route('admin.conversion-reward-types.index')->with('success', 'Reward type updated successfully.');
    }

    public function destroy(ConversionRewardType $conversionRewardType)
    {
        $conversionRewardType->delete();
        return redirect()->route('admin.conversion-reward-types.index')->with('success', 'Reward type deleted successfully.');
    }
}
