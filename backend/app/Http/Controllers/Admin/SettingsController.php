<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::first() ?? new Setting();
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'default_currency' => 'required|string|size:3',
            'currency_symbol' => 'required|string|max:10',
            'multi_currency_enabled' => 'boolean',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'default_theme' => 'required|in:light,dark,system',
            'registration_enabled' => 'boolean',
            'course_purchase_enabled' => 'boolean',
            'affiliate_program_enabled' => 'boolean',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'enrollment_fee' => 'required|numeric|min:0',
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string'
        ]);

        $settings = Setting::first() ?? new Setting();
        $settings->fill($request->all());
        $settings->save();

        return redirect()->route('admin.settings.index')
            ->with('notification', [
                'type' => 'success',
                'message' => 'Settings updated successfully.'
            ]);
    }
} 