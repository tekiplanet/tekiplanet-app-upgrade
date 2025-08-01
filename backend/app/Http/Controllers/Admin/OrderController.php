<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Notifications\OrderStatusUpdated;
use App\Notifications\OrderTrackingUpdated;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'shippingAddress', 'shippingMethod']);

        // Search by order ID or user info
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                   ->orWhere('status', 'like', '%' . $request->search . '%')
                   ->orWhere('payment_status', 'like', '%' . $request->search . '%')
                   ->orWhere('payment_method', 'like', '%' . $request->search . '%')
                   ->orWhereHas('user', function($query) use ($request) {
                       $query->where('first_name', 'like', '%' . $request->search . '%')
                           ->orWhere('last_name', 'like', '%' . $request->search . '%')
                           ->orWhere('email', 'like', '%' . $request->search . '%');
                   })
                   ->orWhereHas('shippingAddress', function($q) use ($request) {
                       $q->where('first_name', 'like', '%' . $request->search . '%')
                         ->orWhere('phone', 'like', '%' . $request->search . '%')
                         ->orWhere('last_name', 'like', '%' . $request->search . '%')
                         ->orWhere('address', 'like', '%' . $request->search . '%')
                         ->orWhere('city', 'like', '%' . $request->search . '%');
                   });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'shippingAddress', 'shippingMethod', 'statusHistory', 'tracking']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            // Create new status history record
            $order->statusHistory()->create([
                'id' => Str::uuid(),
                'status' => $validated['status'],
                'notes' => $validated['notes']
            ]);

            // Update order status
            $order->update(['status' => $validated['status']]);

            // Send email notification
            $order->user->notify(new OrderStatusUpdated(
                $order,
                $validated['status'],
                $validated['notes']
            ));

            // Send in-app notification
            $this->notificationService->send([
                'type' => 'order_status_updated',
                'title' => 'Order Status Updated',
                'message' => "Your order #{$order->id} status has been updated to " . ucfirst($validated['status']),
                'icon' => 'package',
                'action_url' => "/orders/{$order->id}/tracking",
                'extra_data' => [
                    'order_id' => $order->id,
                    'status' => $validated['status'],
                    'notes' => $validated['notes']
                ]
            ], $order->user);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTracking(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string'
        ]);

        try {
            $order->tracking()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'status' => $validated['status'],
                    'description' => $validated['description'],
                    'location' => $validated['location']
                ]
            );

            // Send email notification
            $order->user->notify(new OrderTrackingUpdated(
                $order,
                $validated['status'],
                $validated['location'],
                $validated['description']
            ));

            // Send in-app notification
            $this->notificationService->send([
                'type' => 'order_tracking_updated',
                'title' => 'Order Tracking Updated',
                'message' => "New tracking update for order #{$order->id}: " . ucfirst($validated['status']),
                'icon' => 'truck',
                'action_url' => "/orders/{$order->id}/tracking",
                'extra_data' => [
                    'order_id' => $order->id,
                    'status' => $validated['status'],
                    'location' => $validated['location'],
                    'description' => $validated['description']
                ]
            ], $order->user);

            return response()->json([
                'success' => true,
                'message' => 'Tracking information updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tracking information: ' . $e->getMessage()
            ], 500);
        }
    }
} 