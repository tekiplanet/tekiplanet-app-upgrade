<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileSystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminFileSystemSettingController extends Controller
{
    /**
     * Display the file system settings page
     */
    public function index()
    {
        return view('admin.file-management.partials.settings');
    }

    /**
     * Get all system settings (API)
     */
    public function list(): JsonResponse
    {
        $settings = FileSystemSetting::orderBy('setting_key')->get();
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get a specific setting
     */
    public function show(string $key): JsonResponse
    {
        $setting = FileSystemSetting::where('setting_key', $key)->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    /**
     * Update a system setting
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $setting = FileSystemSetting::where('setting_key', $key)->firstOrFail();

        if (!$setting->is_editable) {
            return response()->json([
                'success' => false,
                'message' => 'This setting is not editable'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'setting_value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate value based on setting type
        $validationError = $this->validateSettingValue($setting, $request->setting_value);
        if ($validationError) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid value for setting type',
                'error' => $validationError
            ], 422);
        }

        $setting->typed_value = $request->setting_value;
        $setting->save();

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting
        ]);
    }

    /**
     * Update multiple settings at once
     */
    public function updateMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:file_system_settings,setting_key',
            'settings.*.value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updated = [];
        $errors = [];

        foreach ($request->settings as $item) {
            $setting = FileSystemSetting::where('setting_key', $item['key'])->first();
            
            if (!$setting) {
                $errors[] = "Setting '{$item['key']}' not found";
                continue;
            }

            if (!$setting->is_editable) {
                $errors[] = "Setting '{$item['key']}' is not editable";
                continue;
            }

            // Validate value
            $validationError = $this->validateSettingValue($setting, $item['value']);
            if ($validationError) {
                $errors[] = "Setting '{$item['key']}': {$validationError}";
                continue;
            }

            $setting->typed_value = $item['value'];
            $setting->save();
            $updated[] = $setting;
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($errors) === 0 ? 'Settings updated successfully' : 'Some settings failed to update',
            'data' => $updated,
            'errors' => $errors
        ], count($errors) === 0 ? 200 : 422);
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(): JsonResponse
    {
        $defaults = [
            'max_total_storage_per_user' => 1073741824, // 1GB
            'file_expiration_days' => 30,
            'enable_virus_scanning' => true,
            'download_url_expiration_hours' => 1,
            'max_concurrent_uploads' => 5,
            'enable_file_compression' => true
        ];

        $updated = [];
        foreach ($defaults as $key => $value) {
            $setting = FileSystemSetting::where('setting_key', $key)->first();
            if ($setting && $setting->is_editable) {
                $setting->typed_value = $value;
                $setting->save();
                $updated[] = $setting;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings reset to defaults successfully',
            'data' => $updated
        ]);
    }

    /**
     * Get settings by category
     */
    public function getByCategory(): JsonResponse
    {
        $settings = FileSystemSetting::orderBy('setting_key')->get();
        
        $categorized = [
            'storage' => [],
            'security' => [],
            'cloudinary' => [],
            'performance' => [],
            'general' => []
        ];

        foreach ($settings as $setting) {
            if (str_contains($setting->setting_key, 'storage') || str_contains($setting->setting_key, 'size')) {
                $categorized['storage'][] = $setting;
            } elseif (str_contains($setting->setting_key, 'virus') || str_contains($setting->setting_key, 'security')) {
                $categorized['security'][] = $setting;
            } elseif (str_contains($setting->setting_key, 'cloudinary')) {
                $categorized['cloudinary'][] = $setting;
            } elseif (str_contains($setting->setting_key, 'compression') || str_contains($setting->setting_key, 'concurrent')) {
                $categorized['performance'][] = $setting;
            } else {
                $categorized['general'][] = $setting;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $categorized
        ]);
    }

    /**
     * Export settings
     */
    public function export(): JsonResponse
    {
        $settings = FileSystemSetting::all();
        $export = [];

        foreach ($settings as $setting) {
            $export[$setting->setting_key] = [
                'value' => $setting->typed_value,
                'type' => $setting->setting_type,
                'description' => $setting->description,
                'editable' => $setting->is_editable
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $export
        ]);
    }

    /**
     * Validate setting value based on type
     */
    private function validateSettingValue(FileSystemSetting $setting, $value): ?string
    {
        switch ($setting->setting_type) {
            case 'integer':
                if (!is_numeric($value) || (int)$value < 0) {
                    return 'Value must be a positive integer';
                }
                break;
                
            case 'boolean':
                if (!is_bool($value) && !in_array($value, ['true', 'false', '1', '0'], true)) {
                    return 'Value must be a boolean';
                }
                break;
                
            case 'json':
                if (!is_array($value) && !is_string($value)) {
                    return 'Value must be an array or JSON string';
                }
                if (is_string($value) && !json_decode($value)) {
                    return 'Value must be valid JSON';
                }
                break;
                
            case 'string':
                if (!is_string($value)) {
                    return 'Value must be a string';
                }
                break;
        }

        return null;
    }
}
