<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hustle;
use App\Models\ProfessionalCategory;
use App\Models\Professional;
use App\Services\NotificationService;
use App\Mail\HustleCreated;
use App\Mail\PaymentCreated;
use App\Mail\PaymentStatusUpdated;
use App\Models\HustlePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Transaction;
use App\Mail\NewHustleMessage;

class HustleController extends Controller
{
    public function index(Request $request)
    {
        $hustles = Hustle::with(['category', 'assignedProfessional', 'applications'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->latest()
            ->paginate(10);

        $categories = ProfessionalCategory::all();

        return view('admin.hustles.index', compact('hustles', 'categories'));
    }

    public function create()
    {
        $categories = ProfessionalCategory::all();
        return view('admin.hustles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:professional_categories,id',
                'budget' => 'required|numeric|min:0',
                'deadline' => 'required|date|after:today',
                'requirements' => 'nullable|string',
            ]);

            $hustle = Hustle::create($validated);

            // Get all professionals in this category
            $professionals = Professional::where('category_id', $hustle->category_id)
                ->with('user')
                ->get();

            // Prepare notification data
            $notificationData = [
                'type' => 'new_hustle',
                'title' => 'New Hustle Available',
                'message' => "A new hustle '{$hustle->title}' has been posted in your category.",
                'icon' => 'briefcase',
                'action_url' => '/dashboard/hustles/' . $hustle->id,
                'extra_data' => [
                    'hustle_id' => $hustle->id,
                    'category_id' => $hustle->category_id,
                ]
            ];

            // Get notification service
            $notificationService = app(NotificationService::class);

            foreach ($professionals as $professional) {
                // Send notification
                $notificationService->send($notificationData, $professional->user);

                // Send email
                Mail::to($professional->user->email)
                    ->queue(new HustleCreated($hustle, $professional));
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hustle created successfully',
                    'hustle' => $hustle,
                    'redirect' => route('admin.hustles.show', $hustle)
                ]);
            }

            return redirect()->route('admin.hustles.show', $hustle)
                ->with('success', 'Hustle created successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to create hustle: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create hustle: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => 'Failed to create hustle: ' . $e->getMessage()]);
        }
    }

    public function show(Hustle $hustle)
    {
        $hustle->load([
            'category',
            'assignedProfessional.user',
            'applications.professional.user',
            'applications.professional.category',
            'messages'
        ]);
        
        return view('admin.hustles.show', compact('hustle'));
    }

    public function edit(Hustle $hustle)
    {
        $categories = ProfessionalCategory::all();
        return view('admin.hustles.edit', compact('hustle', 'categories'));
    }

    public function update(Request $request, Hustle $hustle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:professional_categories,id',
            'budget' => 'required|numeric|min:0',
            'deadline' => 'required|date',
            'requirements' => 'nullable|string',
            'status' => 'required|in:open,approved,in_progress,completed,cancelled'
        ]);

        $hustle->update($validated);

        return redirect()->route('admin.hustles.show', $hustle)
            ->with('success', 'Hustle updated successfully.');
    }

    public function destroy(Hustle $hustle)
    {
        $hustle->delete();
        return redirect()->route('admin.hustles.index')
            ->with('success', 'Hustle deleted successfully.');
    }

    public function updateStatus(Request $request, Hustle $hustle)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:open,approved,in_progress,completed,cancelled'
            ]);

            if ($validated['status'] === 'in_progress') {
                // Create initial payment (40%)
                $initialPayment = HustlePayment::create([
                    'hustle_id' => $hustle->id,
                    'professional_id' => $hustle->assigned_professional_id,
                    'amount' => $hustle->budget * 0.4,
                    'payment_type' => 'initial',
                    'status' => 'pending'
                ]);

                // Create final payment (60%)
                $finalPayment = HustlePayment::create([
                    'hustle_id' => $hustle->id,
                    'professional_id' => $hustle->assigned_professional_id,
                    'amount' => $hustle->budget * 0.6,
                    'payment_type' => 'final',
                    'status' => 'pending'
                ]);

                // Send notifications for both payments
                $notificationService = app(NotificationService::class);
                $professional = $hustle->assignedProfessional;

                foreach ([$initialPayment, $finalPayment] as $payment) {
                    // Send notification
                    $notificationData = [
                        'type' => 'payment_created',
                        'title' => 'New Payment Created',
                        'message' => "A new {$payment->payment_type} payment has been created for '{$hustle->title}'.",
                        'icon' => 'cash',
                        'action_url' => '/dashboard/payments/' . $payment->id,
                        'extra_data' => [
                            'hustle_id' => $hustle->id,
                            'payment_id' => $payment->id,
                            'amount' => $payment->amount
                        ]
                    ];

                    $notificationService->send($notificationData, $professional->user);

                    // Send email
                    Mail::to($professional->user->email)
                        ->queue(new PaymentCreated($hustle, $professional, $payment));
                }
            }

            $hustle->update($validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Hustle status updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Failed to update hustle status.');
        }
    }

    public function updatePaymentStatus(Request $request, Hustle $hustle, HustlePayment $payment)
    {
        try {
            if ($payment->status === 'completed') {
                throw new \Exception('Cannot change status of completed payment.');
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,completed'
            ]);

            \DB::transaction(function () use ($payment, $validated, $hustle) {
                $payment->update($validated);

                if ($payment->status === 'completed') {
                    // Get the professional's user
                    $user = $payment->professional->user;

                    // Credit user's wallet
                    $user->increment('wallet_balance', $payment->amount);

                    // Create transaction record
                    Transaction::create([
                        'user_id' => $user->id,
                        'amount' => $payment->amount,
                        'type' => 'credit',
                        'description' => "Payment received for hustle: {$hustle->title}",
                        'category' => 'hustle_payment',
                        'status' => 'completed',
                        'payment_method' => 'wallet',
                        'reference_number' => 'HP-' . uniqid(),
                        'notes' => [
                            'hustle_id' => $hustle->id,
                            'payment_id' => $payment->id,
                            'payment_type' => $payment->payment_type
                        ]
                    ]);

                    // Update hustle payment flags
                    if ($payment->payment_type === 'initial') {
                        $hustle->update(['initial_payment_released' => true]);
                    } else {
                        $hustle->update(['final_payment_released' => true]);
                    }

                    // Send notification
                    $notificationService = app(NotificationService::class);
                    $professional = $hustle->assignedProfessional;

                    $notificationData = [
                        'type' => 'payment_received',
                        'title' => 'Payment Received',
                        'message' => "Your {$payment->payment_type} payment of ₦" . number_format($payment->amount, 2) . " for '{$hustle->title}' has been credited to your wallet.",
                        'icon' => 'cash',
                        'action_url' => '/dashboard/wallet',
                        'extra_data' => [
                            'hustle_id' => $hustle->id,
                            'payment_id' => $payment->id,
                            'amount' => $payment->amount,
                            'payment_type' => $payment->payment_type
                        ]
                    ];

                    $notificationService->send($notificationData, $professional->user);

                    // Send email
                    Mail::to($professional->user->email)
                        ->queue(new PaymentStatusUpdated($hustle, $professional, $payment));
                }
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Payment status updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function getMessages(Request $request, Hustle $hustle)
    {
        $messages = $hustle->messages()
            ->with('user')
            ->oldest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->user->full_name,
                    'sender_avatar' => $message->user->avatar,
                    'created_at' => $message->created_at->diffForHumans(),
                    'is_admin' => $message->sender_type === 'admin'
                ];
            });

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Hustle $hustle)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string'
            ]);

            // Get the professional's user record
            $professional = $hustle->assignedProfessional;

            $message = $hustle->messages()->create([
                'user_id' => $professional->user_id,
                'message' => $validated['message'],
                'sender_type' => 'admin',
                'is_read' => false
            ]);

            // Load the user relationship
            $message->load('user');

            // Send notification using NotificationService
            $notificationService = app(NotificationService::class);
            $notificationData = [
                'type' => 'new_message',
                'title' => 'New Message from Admin',
                'message' => "You have received a new message regarding '{$hustle->title}'",
                'icon' => 'chat',
                'action_url' => '/dashboard/hustles/' . $hustle->id . '/messages',
                'extra_data' => [
                    'hustle_id' => $hustle->id,
                    'message_id' => $message->id
                ]
            ];

            $notificationService->send($notificationData, $professional->user);

            // Send email
            Mail::to($professional->user->email)
                ->queue(new NewHustleMessage($hustle, $professional->user, $message));

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => 'Admin',
                    'sender_avatar' => null,
                    'created_at' => $message->created_at->diffForHumans(),
                    'is_admin' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 422);
        }
    }
} 