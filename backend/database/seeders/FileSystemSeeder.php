<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default file categories
        $categories = [
            [
                'id' => Str::uuid(),
                'name' => 'Images',
                'description' => 'Image files including photos, screenshots, and graphics',
                'allowed_extensions' => json_encode(['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff']),
                'max_file_size' => 10 * 1024 * 1024, // 10MB
                'resource_type' => 'image',
                'is_active' => true,
                'requires_optimization' => true,
                'cloudinary_options' => json_encode([
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                    'folder' => 'grit-files/images'
                ]),
                'sort_order' => 1
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Videos',
                'description' => 'Video files including recordings, presentations, and tutorials',
                'allowed_extensions' => json_encode(['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', '3gp', 'mkv']),
                'max_file_size' => 100 * 1024 * 1024, // 100MB
                'resource_type' => 'video',
                'is_active' => true,
                'requires_optimization' => true,
                'cloudinary_options' => json_encode([
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                    'folder' => 'grit-files/videos'
                ]),
                'sort_order' => 2
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Documents',
                'description' => 'Document files including PDFs, Word, Excel, and PowerPoint files',
                'allowed_extensions' => json_encode(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf']),
                'max_file_size' => 25 * 1024 * 1024, // 25MB
                'resource_type' => 'raw',
                'is_active' => true,
                'requires_optimization' => false,
                'cloudinary_options' => json_encode([
                    'folder' => 'grit-files/documents'
                ]),
                'sort_order' => 3
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Archives',
                'description' => 'Compressed archive files',
                'allowed_extensions' => json_encode(['zip', 'rar', '7z', 'tar', 'gz']),
                'max_file_size' => 50 * 1024 * 1024, // 50MB
                'resource_type' => 'raw',
                'is_active' => true,
                'requires_optimization' => false,
                'cloudinary_options' => json_encode([
                    'folder' => 'grit-files/archives'
                ]),
                'sort_order' => 4
            ]
        ];

        foreach ($categories as $category) {
            DB::table('file_categories')->insert($category);
        }

        // Seed default system settings
        $settings = [
            [
                'id' => Str::uuid(),
                'setting_key' => 'max_total_storage_per_user',
                'setting_value' => '1073741824', // 1GB in bytes
                'setting_type' => 'integer',
                'description' => 'Maximum total storage allowed per user in bytes',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'file_expiration_days',
                'setting_value' => '30',
                'setting_type' => 'integer',
                'description' => 'Default number of days before files expire',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'enable_virus_scanning',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Enable virus scanning for uploaded files',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'download_url_expiration_hours',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'Number of hours before download URLs expire',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'max_concurrent_uploads',
                'setting_value' => '5',
                'setting_type' => 'integer',
                'description' => 'Maximum number of concurrent uploads per user',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'enable_file_compression',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Enable automatic file compression for images and videos',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'cloudinary_cloud_name',
                'setting_value' => '',
                'setting_type' => 'string',
                'description' => 'Cloudinary cloud name',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'cloudinary_api_key',
                'setting_value' => '',
                'setting_type' => 'string',
                'description' => 'Cloudinary API key',
                'is_editable' => true
            ],
            [
                'id' => Str::uuid(),
                'setting_key' => 'cloudinary_api_secret',
                'setting_value' => '',
                'setting_type' => 'string',
                'description' => 'Cloudinary API secret',
                'is_editable' => true
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('file_system_settings')->insert($setting);
        }
    }
}
