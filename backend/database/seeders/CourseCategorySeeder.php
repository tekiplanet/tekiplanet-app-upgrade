<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Programming & Development',
                'children' => [
                    'Web Development',
                    'Mobile Development',
                    'Game Development',
                    'Database Design',
                    'Software Testing'
                ]
            ],
            [
                'name' => 'Business & Entrepreneurship',
                'children' => [
                    'Business Strategy',
                    'Startup',
                    'Business Analytics',
                    'Digital Marketing',
                    'Project Management'
                ]
            ],
            [
                'name' => 'Design & Creative',
                'children' => [
                    'Graphic Design',
                    'UI/UX Design',
                    '3D & Animation',
                    'Video Editing',
                    'Photography'
                ]
            ],
            [
                'name' => 'Personal Development',
                'children' => [
                    'Leadership',
                    'Communication',
                    'Time Management',
                    'Career Development',
                    'Personal Finance'
                ]
            ]
        ];

        foreach ($categories as $category) {
            $parent = CourseCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => 'Courses related to ' . strtolower($category['name']),
                'status' => true,
                'order' => 0
            ]);

            foreach ($category['children'] as $index => $child) {
                CourseCategory::create([
                    'name' => $child,
                    'slug' => Str::slug($child),
                    'description' => 'Courses related to ' . strtolower($child),
                    'parent_id' => $parent->id,
                    'status' => true,
                    'order' => $index
                ]);
            }
        }
    }
} 