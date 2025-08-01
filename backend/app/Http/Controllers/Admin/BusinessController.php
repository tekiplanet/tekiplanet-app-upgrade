<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use App\Notifications\CustomNotification;
use Illuminate\Support\HtmlString;
use App\Services\NotificationService;

class BusinessController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $businesses = BusinessProfile::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('business_name', 'like', "%{$search}%")
                      ->orWhere('business_email', 'like', "%{$search}%")
                      ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('admin.businesses.index', compact('businesses'));
    }

    public function show(BusinessProfile $business)
    {
        return view('admin.businesses.show', compact('business'));
    }

    public function update(Request $request, BusinessProfile $business)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email|unique:business_profiles,business_email,' . $business->id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'registration_number' => 'nullable|string|unique:business_profiles,registration_number,' . $business->id,
            'tax_number' => 'nullable|string|unique:business_profiles,tax_number,' . $business->id,
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string|in:Abia,Adamawa,Akwa Ibom,Anambra,Bauchi,Bayelsa,Benue,Borno,Cross River,Delta,Ebonyi,Edo,Ekiti,Enugu,FCT,Gombe,Imo,Jigawa,Kaduna,Kano,Katsina,Kebbi,Kogi,Kwara,Lagos,Nasarawa,Niger,Ogun,Ondo,Osun,Oyo,Plateau,Rivers,Sokoto,Taraba,Yobe,Zamfara',
        ]);

        $validated['country'] = 'Nigeria';

        $business->update($validated);

        return response()->json([
            'message' => 'Business updated successfully'
        ]);
    }

    public function toggleStatus(Request $request, BusinessProfile $business)
    {
        $newStatus = $business->status === 'active' ? 'inactive' : 'active';
        $business->status = $newStatus;
        $business->save();

        // Prepare notification content
        $title = 'Business Status Update';
        $message = $newStatus === 'active' 
            ? "Your business account has been activated successfully."
            : "Your business account has been deactivated. Reason: " . $request->input('reason');

        // Prepare email content with more details
        $emailContent = new HtmlString("
            <p>Dear {$business->user->name},</p>
            <p>{$message}</p>
            <p><strong>Business Details:</strong></p>
            <ul>
                <li>Business Name: {$business->business_name}</li>
                <li>Business Email: {$business->business_email}</li>
                <li>Current Status: " . ucfirst($newStatus) . "</li>
            </ul>
            " . ($newStatus === 'inactive' ? "
            <p><strong>Reason for Deactivation:</strong><br>
            {$request->input('reason')}</p>
            " : "") . "
            <p>If you have any questions, please contact our support team.</p>
        ");

        // Send in-app notification
        $this->notificationService->send([
            'type' => 'business',
            'title' => $title,
            'message' => $message,
            'icon' => 'business',
            'action_url' => '/business/profile'
        ], $business->user);

        // Send email notification
        $business->user->notify(new CustomNotification(
            $title,
            $message,
            'business-status',
            $emailContent
        ));

        return response()->json([
            'message' => 'Business status updated successfully',
            'status' => $business->status
        ]);
    }

    public function customers(Request $request, BusinessProfile $business)
    {
        $customers = $business->business_customers()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('admin.businesses.customers.index', compact('business', 'customers'));
    }

    public function showCustomer(BusinessProfile $business, $customerId)
    {
        $customer = $business->business_customers()->findOrFail($customerId);

        // Calculate total spent from invoice payments
        $totalSpent = $customer->business_invoices()
            ->join('business_invoice_payments', 'business_invoices.id', '=', 'business_invoice_payments.invoice_id')
            ->sum('business_invoice_payments.amount');

        // Get the customer's currency from their latest invoice payment
        $latestPayment = $customer->business_invoices()
            ->join('business_invoice_payments', 'business_invoices.id', '=', 'business_invoice_payments.invoice_id')
            ->select('business_invoice_payments.currency')
            ->latest('business_invoice_payments.created_at')
            ->first();

        $currency = $latestPayment ? $latestPayment->currency : 'NGN';

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => $customer->status,
            'address' => $customer->address,
            'notes' => $customer->notes,
            'created_at' => $customer->created_at,
            'updated_at' => $customer->updated_at,
            'total_orders' => $customer->business_invoices()->count(),
            'total_spent' => $totalSpent,
            'currency' => $currency
        ]);
    }

    public function invoices(Request $request, BusinessProfile $business)
    {
        $invoices = $business->business_invoices()
            ->with(['customer', 'payments'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(10);

        return view('admin.businesses.invoices.index', compact('business', 'invoices'));
    }

    public function showInvoice(BusinessProfile $business, $invoiceId)
    {
        $invoice = $business->business_invoices()
            ->with(['customer', 'payments', 'items'])
            ->findOrFail($invoiceId);

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'status' => $invoice->status,
            'due_date' => $invoice->due_date,
            'notes' => $invoice->notes,
            'customer' => [
                'name' => $invoice->customer->name,
                'email' => $invoice->customer->email,
            ],
            'items' => $invoice->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                ];
            }),
            'payments' => $invoice->payments->map(function ($payment) {
                return [
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'payment_date' => $payment->payment_date,
                    'notes' => $payment->notes,
                ];
            }),
        ]);
    }
} 