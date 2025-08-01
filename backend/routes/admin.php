<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\ProfessionalController;
use App\Http\Controllers\Admin\CourseExamController;
use App\Http\Controllers\Admin\CourseExamParticipantController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductFeatureController;
use App\Http\Controllers\Admin\ProductSpecificationController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ShippingAddressController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\HustleController;
use App\Http\Controllers\Admin\HustleApplicationController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\ConsultingBookingController;
use App\Http\Controllers\Admin\ConsultingTimeSlotController;
use App\Http\Controllers\Admin\ConsultingBookingReminderController;
use App\Http\Controllers\Admin\WorkstationPlanController;
use App\Http\Controllers\Admin\WorkstationSubscriptionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectStageController;
use App\Http\Controllers\Admin\ProjectTeamMemberController;
use App\Http\Controllers\Admin\ProjectFileController;
use App\Http\Controllers\Admin\ProjectInvoiceController;
use App\Http\Controllers\Admin\ProductRequestController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.post');
    });

    Route::post('logout', [LogoutController::class, 'logout'])
        ->name('admin.logout')
        ->middleware('auth:admin');

    // Protected routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        
        // Dashboard accessible by all admins
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User management routes - accessible by super admin and admin
        Route::middleware('admin.roles:super_admin,admin,finance')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('users/notify-bulk', [UserController::class, 'notifyBulk'])->name('users.notify-bulk');
            Route::post('users/{user}/transactions', [UserController::class, 'createTransaction'])
                ->name('users.transactions.store');

            // Business management routes
            Route::resource('business-profiles', BusinessController::class)->except(['create', 'store', 'destroy'])->names([
                'index' => 'businesses.index',
                'show' => 'businesses.show',
                'update' => 'businesses.update',
            ])->parameters([
                'business-profiles' => 'business'
            ]);
            Route::post('business-profiles/{business}/toggle-status', [BusinessController::class, 'toggleStatus'])
                ->name('businesses.toggle-status');

            // Business Customers routes
            Route::get('business-profiles/{business}/customers', [BusinessController::class, 'customers'])
                ->name('businesses.customers.index');
            Route::get('business-profiles/{business}/customers/{customer}', [BusinessController::class, 'showCustomer'])
                ->name('businesses.customers.show');

            // Business Invoices routes
            Route::get('business-profiles/{business}/invoices', [BusinessController::class, 'invoices'])
                ->name('businesses.invoices.index');
            Route::get('business-profiles/{business}/invoices/{invoice}', [BusinessController::class, 'showInvoice'])
                ->name('businesses.invoices.show');
        });

        // Course management routes - accessible by super admin, admin, and tutor
        Route::middleware('admin.roles:super_admin,admin,tutor')->group(function () {
            // Add course management routes here
        });

        // Financial routes - accessible by super admin, admin, and finance
        Route::middleware('admin.roles:super_admin,admin,finance')->group(function () {
            // Add financial routes here
        });

        // Sales routes - accessible by super admin, admin, and sales
        Route::middleware('admin.roles:super_admin,admin,sales')->group(function () {
            // Add sales routes here
        });

        // Management routes - accessible by super admin, admin, and management
        Route::middleware('admin.roles:super_admin,admin,management')->group(function () {
            // Add management routes here
        });

        // Professional routes
        Route::resource('professionals', ProfessionalController::class)->except(['create', 'store', 'destroy']);
        Route::post('professionals/{professional}/toggle-status', [ProfessionalController::class, 'toggleStatus'])
            ->name('professionals.toggle-status');

        // Course Exam routes
        Route::group(['prefix' => 'courses/{course}/exams', 'as' => 'courses.exams.'], function () {
            Route::get('/', [CourseExamController::class, 'index'])->name('index');
            Route::get('/create', [CourseExamController::class, 'create'])->name('create');
            Route::post('/', [CourseExamController::class, 'store'])->name('store');
            Route::get('/{exam}', [CourseExamController::class, 'show'])->name('show');
            Route::get('/{exam}/edit', [CourseExamController::class, 'edit'])->name('edit');
            Route::put('/{exam}', [CourseExamController::class, 'update'])->name('update');
            Route::delete('/{exam}', [CourseExamController::class, 'destroy'])->name('destroy');
            Route::post('/{exam}/status', [CourseExamController::class, 'updateStatus'])->name('update-status');

            // Add participants routes
            Route::group(['prefix' => '{exam}/participants', 'as' => 'participants.'], function () {
                Route::get('/', [CourseExamParticipantController::class, 'index'])->name('index');
                Route::post('/bulk-update', [CourseExamParticipantController::class, 'bulkUpdate'])->name('bulk-update');
                Route::post('/{participant}', [CourseExamParticipantController::class, 'update'])->name('update');
            });
        });

        // Product routes
        Route::resource('products', ProductController::class)->except(['destroy']);

        // Product Features routes
        Route::post('products/{product}/features', [ProductFeatureController::class, 'store'])
            ->name('products.features.store');
        Route::delete('products/features/{feature}', [ProductFeatureController::class, 'destroy'])
            ->name('products.features.destroy');

        // Product Specifications routes
        Route::post('products/{product}/specifications', [ProductSpecificationController::class, 'store'])
            ->name('products.specifications.store');
        Route::delete('products/specifications/{specification}', [ProductSpecificationController::class, 'destroy'])
            ->name('products.specifications.destroy');

        // Product Images routes
        Route::post('products/{product}/images', [ProductImageController::class, 'store'])
            ->name('products.images.store');
        Route::put('products/images/{image}', [ProductImageController::class, 'update'])
            ->name('products.images.update');
        Route::post('products/images/{image}/set-primary', [ProductImageController::class, 'setPrimary'])
            ->name('products.images.set-primary');
        Route::delete('products/images/{image}', [ProductImageController::class, 'destroy'])
            ->name('products.images.destroy');

        // Product Categories routes
        Route::resource('product-categories', ProductCategoryController::class)->except(['create', 'edit', 'destroy'])->parameters([
            'product-categories' => 'category'
        ]);

        // Brand routes
        Route::resource('brands', BrandController::class)->except(['create', 'edit', 'destroy']);

        // Shipping Management
        Route::prefix('shipping')->name('shipping.')->group(function () {
            // Shipping Zones
            Route::get('/zones', [ShippingZoneController::class, 'index'])->name('zones.index');
            Route::post('/zones', [ShippingZoneController::class, 'store'])->name('zones.store');
            Route::put('/zones/{zone}', [ShippingZoneController::class, 'update'])->name('zones.update');
            Route::delete('/zones/{zone}', [ShippingZoneController::class, 'destroy'])->name('zones.destroy');

            // Shipping Methods
            Route::get('/methods', [ShippingMethodController::class, 'index'])->name('methods.index');
            Route::post('/methods', [ShippingMethodController::class, 'store'])->name('methods.store');
            Route::put('/methods/{method}', [ShippingMethodController::class, 'update'])->name('methods.update');
            Route::delete('/methods/{method}', [ShippingMethodController::class, 'destroy'])->name('methods.destroy');

            // Shipping Addresses
            Route::get('/addresses', [ShippingAddressController::class, 'index'])->name('addresses.index');
            Route::post('/addresses', [ShippingAddressController::class, 'store'])->name('addresses.store');
            Route::put('/addresses/{address}', [ShippingAddressController::class, 'update'])->name('addresses.update');
            Route::delete('/addresses/{address}', [ShippingAddressController::class, 'destroy'])->name('addresses.destroy');
        });

        // Service Categories
        Route::resource('service-categories', ServiceCategoryController::class);
        Route::post('service-categories/{serviceCategory}/toggle-featured', [ServiceCategoryController::class, 'toggleFeatured'])
            ->name('service-categories.toggle-featured');

        // Services
        Route::resource('services', ServiceController::class);
        Route::post('services/{service}/toggle-featured', [ServiceController::class, 'toggleFeatured'])
            ->name('services.toggle-featured');



        // Add these inside the middleware('auth:admin') group
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
            Route::patch('/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('update-status');
            Route::get('/{transaction}/receipt/download', [TransactionController::class, 'downloadReceipt'])->name('download-receipt');
            Route::post('/{transaction}/receipt/send', [TransactionController::class, 'sendReceipt'])->name('send-receipt');
        });

        Route::prefix('bank-accounts')->name('bank-accounts.')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::get('/{bankAccount}', [BankAccountController::class, 'show'])->name('show');
            Route::patch('/{bankAccount}/verification', [BankAccountController::class, 'updateVerification'])->name('update-verification');
        });

        // Add these inside the middleware('auth:admin') group
        Route::prefix('hustles')->name('hustles.')->group(function () {
            Route::get('/', [HustleController::class, 'index'])->name('index');
            Route::get('/create', [HustleController::class, 'create'])->name('create');
            Route::post('/', [HustleController::class, 'store'])->name('store');
            Route::get('/{hustle}', [HustleController::class, 'show'])->name('show');
            Route::get('/{hustle}/edit', [HustleController::class, 'edit'])->name('edit');
            Route::put('/{hustle}', [HustleController::class, 'update'])->name('update');
            Route::delete('/{hustle}', [HustleController::class, 'destroy'])->name('destroy');
            
            // Applications routes
            Route::get('/{hustle}/applications', [HustleApplicationController::class, 'index'])->name('applications.index');
            Route::get('/{hustle}/applications/{application}', [HustleApplicationController::class, 'show'])->name('applications.show');
            Route::patch('/{hustle}/applications/{application}/status', [HustleApplicationController::class, 'updateStatus'])->name('applications.update-status');
        });

        Route::patch('hustles/{hustle}/status', [HustleController::class, 'updateStatus'])
            ->name('hustles.update-status');

        Route::patch('hustles/{hustle}/payments/{payment}/status', [HustleController::class, 'updatePaymentStatus'])
            ->name('hustles.payments.update-status');

        Route::get('hustles/{hustle}/messages', [HustleController::class, 'getMessages'])
            ->name('hustles.messages');
        Route::post('hustles/{hustle}/messages', [HustleController::class, 'sendMessage'])
            ->name('hustles.messages.send');

        // Inside the middleware('auth:admin') group
        Route::prefix('quotes')->name('quotes.')->group(function () {
            Route::get('/', [QuoteController::class, 'index'])->name('index');
            Route::get('/{quote}', [QuoteController::class, 'show'])->name('show');
            Route::patch('/{quote}/status', [QuoteController::class, 'updateStatus'])->name('update-status');
            Route::patch('/{quote}/assign', [QuoteController::class, 'assign'])->name('assign');
            Route::post('/{quote}/messages', [QuoteController::class, 'sendMessage'])->name('messages.send');
            Route::get('/{quote}/messages', [QuoteController::class, 'getMessages'])->name('messages');
        });

        // Add this inside the middleware('auth:admin') group
        Route::prefix('consulting')->name('consulting.')->group(function () {
            // Bookings
            Route::get('/bookings', [ConsultingBookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/{booking}', [ConsultingBookingController::class, 'show'])->name('bookings.show');
            Route::post('/bookings/{booking}/status', [ConsultingBookingController::class, 'updateStatus'])
                ->name('bookings.update-status');
            Route::post('/bookings/{booking}/assign-expert', [ConsultingBookingController::class, 'assignExpert'])
                ->name('bookings.assign-expert');

            // Time Slots
            Route::get('/timeslots', [ConsultingTimeSlotController::class, 'index'])->name('timeslots.index');
            Route::post('/timeslots', [ConsultingTimeSlotController::class, 'store'])->name('timeslots.store');
            Route::post('/timeslots/bulk-create', [ConsultingTimeSlotController::class, 'bulkCreate'])->name('timeslots.bulk-create');
            Route::post('/timeslots/bulk-delete', [ConsultingTimeSlotController::class, 'bulkDestroy'])->name('timeslots.bulk-destroy');
            Route::get('/timeslots/{timeSlot}/edit', [ConsultingTimeSlotController::class, 'edit'])->name('timeslots.edit');
            Route::put('/timeslots/{timeSlot}', [ConsultingTimeSlotController::class, 'update'])->name('timeslots.update');
            Route::delete('/timeslots/{timeSlot}', [ConsultingTimeSlotController::class, 'destroy'])->name('timeslots.destroy');

            // Reminders
            Route::post('/bookings/{booking}/send-reminder', [ConsultingBookingReminderController::class, 'sendReminder'])
                ->name('bookings.send-reminder');
        });

        // Workstation Plans
        Route::prefix('workstation')->name('workstation.')->group(function () {
            // Plans
            Route::get('/plans', [WorkstationPlanController::class, 'index'])->name('plans.index');
            Route::get('/plans/create', [WorkstationPlanController::class, 'create'])->name('plans.create');
            Route::post('/plans', [WorkstationPlanController::class, 'store'])->name('plans.store');
            Route::get('/plans/{plan}', [WorkstationPlanController::class, 'show'])->name('plans.show');
            Route::get('/plans/{plan}/edit', [WorkstationPlanController::class, 'edit'])->name('plans.edit');
            Route::put('/plans/{plan}', [WorkstationPlanController::class, 'update'])->name('plans.update');
            Route::post('/plans/{plan}/toggle-status', [WorkstationPlanController::class, 'toggleStatus'])
                ->name('plans.toggle-status');

            // Subscriptions
            Route::get('/subscriptions', [WorkstationSubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::get('/subscriptions/{subscription}', [WorkstationSubscriptionController::class, 'show'])
                ->name('subscriptions.show');
            Route::patch('/subscriptions/{subscription}/status', [WorkstationSubscriptionController::class, 'updateStatus'])
                ->name('subscriptions.update-status');
        });

        // Project routes
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('index');
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
            Route::patch('/{project}/status', [ProjectController::class, 'updateStatus'])->name('update-status');

            // Project Stages
            Route::prefix('{project}/stages')->name('stages.')->group(function () {
                Route::post('/', [ProjectStageController::class, 'store'])->name('store');
                Route::patch('/{stage}', [ProjectStageController::class, 'update'])->name('update');
                Route::delete('/{stage}', [ProjectStageController::class, 'destroy'])->name('destroy');
            });

            // Project Team Members
            Route::prefix('{project}/team-members')->name('team-members.')->group(function () {
                Route::post('/', [ProjectTeamMemberController::class, 'store'])->name('store');
                Route::patch('/{member}', [ProjectTeamMemberController::class, 'update'])->name('update');
                Route::delete('/{member}', [ProjectTeamMemberController::class, 'destroy'])->name('destroy');
            });

            // Project Files
            Route::prefix('{project}/files')->name('files.')->group(function () {
                Route::post('/', [ProjectFileController::class, 'store'])->name('store');
                Route::delete('/{file}', [ProjectFileController::class, 'destroy'])->name('destroy');
            });

            // Project Invoices
            Route::prefix('{project}/invoices')->name('invoices.')->group(function () {
                Route::post('/', [ProjectInvoiceController::class, 'store'])->name('store');
                Route::patch('/{invoice}', [ProjectInvoiceController::class, 'update'])->name('update');
                Route::delete('/{invoice}', [ProjectInvoiceController::class, 'destroy'])->name('destroy');
            });
        });

        // Product Request routes
        Route::middleware('admin.roles:super_admin,admin,sales')->group(function () {
            Route::prefix('product-requests')->name('product-requests.')->group(function () {
                Route::get('/', [ProductRequestController::class, 'index'])->name('index');
                Route::get('/{productRequest}', [ProductRequestController::class, 'show'])->name('show');
                Route::patch('/{productRequest}/status', [ProductRequestController::class, 'updateStatus'])->name('update-status');
                Route::patch('/{productRequest}/note', [ProductRequestController::class, 'updateNote'])->name('update-note');
            });
        });

        // Coupon Management
        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/', [CouponController::class, 'index'])->name('index');
            Route::post('/', [CouponController::class, 'store'])->name('store');
            Route::get('/{coupon}', [CouponController::class, 'show'])->name('show');
            Route::patch('/{coupon}', [CouponController::class, 'update'])->name('update');
            Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
            Route::patch('/{coupon}/toggle', [CouponController::class, 'toggle'])->name('toggle');
        });
    });
}); 