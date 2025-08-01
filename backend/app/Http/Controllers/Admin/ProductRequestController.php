<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Notifications\ProductRequestStatusUpdated;
use App\Notifications\ProductRequestNoteUpdated;

class ProductRequestController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $requests = ProductRequest::with('user')
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return view('admin.product-requests.index', compact('requests'));
    }

    public function show(ProductRequest $productRequest)
    {
        $productRequest->load('user');
        return view('admin.product-requests.show', compact('productRequest'));
    }

    public function updateStatus(Request $request, ProductRequest $productRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $oldStatus = $productRequest->status;
        $productRequest->update($validated);

        // Send notification
        $this->notificationService->send([
            'type' => 'product_request_status_updated',
            'title' => 'Product Request Status Updated',
            'message' => "Your product request for '{$productRequest->product_name}' has been updated to " . ucfirst($validated['status']),
            'icon' => 'clipboard-check',
            'action_url' => "/product-requests/{$productRequest->id}",
            'extra_data' => [
                'old_status' => $oldStatus,
                'new_status' => $validated['status']
            ]
        ], $productRequest->user);

        // Queue email notification
        $productRequest->user->notify(new ProductRequestStatusUpdated($productRequest, $oldStatus));

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    public function updateNote(Request $request, ProductRequest $productRequest)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string'
        ]);

        $productRequest->update($validated);

        // Send notification
        $this->notificationService->send([
            'type' => 'product_request_note_updated',
            'title' => 'Product Request Note Updated',
            'message' => "An admin has added a note to your product request for '{$productRequest->product_name}'",
            'icon' => 'note',
            'action_url' => "/product-requests/{$productRequest->id}"
        ], $productRequest->user);

        // Queue email notification
        $productRequest->user->notify(new ProductRequestNoteUpdated($productRequest));

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully'
        ]);
    }
} 