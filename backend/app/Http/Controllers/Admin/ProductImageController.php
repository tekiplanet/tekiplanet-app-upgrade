<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'image_url' => 'required|url|max:2048',
            'is_primary' => 'boolean'
        ]);

        try {
            // If this is set as primary, unset other primary images
            if ($validated['is_primary']) {
                $product->images()->where('is_primary', true)->update(['is_primary' => false]);
            }
            // If this is the first image, make it primary regardless
            else if ($product->images()->count() === 0) {
                $validated['is_primary'] = true;
            }

            $image = $product->images()->create([
                'id' => Str::uuid(),
                'image_url' => $validated['image_url'],
                'is_primary' => $validated['is_primary'],
                'order' => $product->images()->count() + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image added successfully',
                'image' => $image
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setPrimary(ProductImage $image)
    {
        try {
            // Unset other primary images for this product
            $image->product->images()->where('is_primary', true)->update(['is_primary' => false]);
            
            // Set this image as primary
            $image->update(['is_primary' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Image set as primary successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set image as primary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ProductImage $image)
    {
        try {
            // If this was the primary image, make another image primary
            if ($image->is_primary) {
                $nextImage = $image->product->images()
                    ->where('id', '!=', $image->id)
                    ->first();
                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ProductImage $image)
    {
        $validated = $request->validate([
            'image_url' => 'required|url|max:2048',
            'is_primary' => 'boolean'
        ]);

        try {
            // If setting as primary, unset other primary images
            if ($validated['is_primary'] && !$image->is_primary) {
                $image->product->images()->where('is_primary', true)->update(['is_primary' => false]);
            }

            $image->update([
                'image_url' => $validated['image_url'],
                'is_primary' => $validated['is_primary']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'image' => $image
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update image: ' . $e->getMessage()
            ], 500);
        }
    }
} 