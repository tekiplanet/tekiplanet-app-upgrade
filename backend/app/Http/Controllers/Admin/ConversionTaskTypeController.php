<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConversionTaskType;

class ConversionTaskTypeController extends Controller
{
    public function index()
    {
        $types = ConversionTaskType::all();
        return view('admin.conversion-task-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.conversion-task-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:conversion_task_types,name',
            'description' => 'nullable|string',
        ]);
        ConversionTaskType::create($validated);
        return redirect()->route('admin.conversion-task-types.index')->with('success', 'Task type created successfully.');
    }

    public function edit(ConversionTaskType $conversionTaskType)
    {
        return view('admin.conversion-task-types.edit', compact('conversionTaskType'));
    }

    public function update(Request $request, ConversionTaskType $conversionTaskType)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:conversion_task_types,name,' . $conversionTaskType->id,
            'description' => 'nullable|string',
        ]);
        $conversionTaskType->update($validated);
        return redirect()->route('admin.conversion-task-types.index')->with('success', 'Task type updated successfully.');
    }

    public function destroy(ConversionTaskType $conversionTaskType)
    {
        $conversionTaskType->delete();
        return redirect()->route('admin.conversion-task-types.index')->with('success', 'Task type deleted successfully.');
    }
}
