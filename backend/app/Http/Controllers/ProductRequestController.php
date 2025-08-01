<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewProductRequestNotification;

class ProductRequestController extends Controller
{
    public function index()
    {
        $requests = ProductRequest::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price',
            'deadline' => 'required|date|after:today',
            'quantity_needed' => 'required|integer|min:1',
            'additional_details' => 'nullable|string'
        ]);

        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'status' => 'pending',
            'min_price' => $validated['min_price'],
            'max_price' => $validated['max_price'],
            'deadline' => $validated['deadline'],
            'product_name' => $validated['product_name'],
            'description' => $validated['description'],
            'quantity_needed' => $validated['quantity_needed'],
            'additional_details' => $validated['additional_details'] ?? null,
        ]);

        // Notify admins
        NewProductRequestNotification::notifyAdmins($productRequest);

        return response()->json([
            'message' => 'Product request submitted successfully',
            'request' => $productRequest
        ], 201);
    }

    public function show(ProductRequest $productRequest)
    {
        if ($productRequest->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'request' => $productRequest
        ]);
    }
} 