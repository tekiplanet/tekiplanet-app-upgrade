<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('position')->get();
        
        return view('admin.currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('admin.currencies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3',
            'symbol' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0.000001',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'decimal_places' => 'required|integer|min:0|max:6'
        ]);

        DB::transaction(function () use ($request) {
            // Check if currency with this code already exists (including soft deleted)
            $existingCurrency = Currency::withTrashed()->where('code', strtoupper($request->code))->first();

            if ($existingCurrency) {
                if ($existingCurrency->trashed()) {
                    // Restore the soft deleted currency and update it
                    $existingCurrency->restore();
                    $existingCurrency->update([
                        'name' => $request->name,
                        'symbol' => $request->symbol,
                        'rate' => $request->rate,
                        'is_active' => $request->boolean('is_active', true),
                        'decimal_places' => $request->decimal_places
                    ]);

                    // If this is being set as base currency, unset any existing base currency
                    if ($request->boolean('is_base')) {
                        Currency::where('is_base', true)
                            ->where('id', '!=', $existingCurrency->id)
                            ->update(['is_base' => false]);
                        $existingCurrency->update(['is_base' => true]);
                    }

                    $message = 'Currency restored and updated successfully.';
                } else {
                    // Currency already exists and is active
                    return redirect()->route('admin.currencies.index')
                        ->with('notification', [
                            'type' => 'error',
                            'message' => 'A currency with this code already exists.'
                        ]);
                }
            } else {
                // If this is being set as base currency, unset any existing base currency
                if ($request->boolean('is_base')) {
                    Currency::where('is_base', true)->update(['is_base' => false]);
                }

                Currency::create([
                    'name' => $request->name,
                    'code' => strtoupper($request->code),
                    'symbol' => $request->symbol,
                    'rate' => $request->rate,
                    'is_base' => $request->boolean('is_base'),
                    'is_active' => $request->boolean('is_active', true),
                    'decimal_places' => $request->decimal_places
                ]);

                $message = 'Currency created successfully.';
            }
        });

        return redirect()->route('admin.currencies.index')
            ->with('notification', [
                'type' => 'success',
                'message' => $message ?? 'Currency created successfully.'
            ]);
    }

    public function edit(Currency $currency)
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3',
            'symbol' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0.000001',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'decimal_places' => 'required|integer|min:0|max:6'
        ]);

        // Check if code is being changed and if it conflicts with another active currency
        if ($request->code !== $currency->code) {
            $existingCurrency = Currency::where('code', strtoupper($request->code))->first();
            if ($existingCurrency && $existingCurrency->id !== $currency->id) {
                return redirect()->route('admin.currencies.index')
                    ->with('notification', [
                        'type' => 'error',
                        'message' => 'A currency with this code already exists.'
                    ]);
            }
        }

        DB::transaction(function () use ($request, $currency) {
            // If this is being set as base currency, unset any existing base currency
            if ($request->boolean('is_base')) {
                Currency::where('is_base', true)
                    ->where('id', '!=', $currency->id)
                    ->update(['is_base' => false]);
            }

            $currency->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'symbol' => $request->symbol,
                'rate' => $request->rate,
                'is_base' => $request->boolean('is_base'),
                'is_active' => $request->boolean('is_active'),
                'decimal_places' => $request->decimal_places
            ]);
        });

        return redirect()->route('admin.currencies.index')
            ->with('notification', [
                'type' => 'success',
                'message' => 'Currency updated successfully.'
            ]);
    }

    public function destroy(Currency $currency)
    {
        // Prevent deletion of base currency
        if ($currency->is_base) {
            return redirect()->route('admin.currencies.index')
                ->with('notification', [
                    'type' => 'error',
                    'message' => 'Cannot delete the base currency.'
                ]);
        }

        $currency->delete(); // This will soft delete (set deleted_at)

        return redirect()->route('admin.currencies.index')
            ->with('notification', [
                'type' => 'success',
                'message' => 'Currency deactivated successfully. You can restore it by adding it again with the same code.'
            ]);
    }

    public function toggleStatus(Currency $currency)
    {
        // Prevent deactivating base currency
        if ($currency->is_base && !$currency->is_active) {
            return redirect()->route('admin.currencies.index')
                ->with('notification', [
                    'type' => 'error',
                    'message' => 'Cannot deactivate the base currency.'
                ]);
        }

        $currency->update(['is_active' => !$currency->is_active]);

        return redirect()->route('admin.currencies.index')
            ->with('notification', [
                'type' => 'success',
                'message' => 'Currency status updated successfully.'
            ]);
    }

    public function updatePositions(Request $request)
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'required|string|exists:currencies,id'
        ]);

        foreach ($request->positions as $position => $currencyId) {
            Currency::where('id', $currencyId)->update(['position' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }
} 