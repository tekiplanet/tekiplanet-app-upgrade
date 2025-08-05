<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\ProfessionalController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseModuleController;
use App\Http\Controllers\Admin\CourseLessonController;
use App\Http\Controllers\Admin\CourseTopicController;
use App\Http\Controllers\Admin\CourseScheduleController;
use App\Http\Controllers\Admin\CourseExamController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\WorkstationPlanController;
use App\Http\Controllers\Admin\WorkstationSubscriptionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\QuizController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Include admin routes
require __DIR__.'/admin.php';

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    // Existing routes...
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('admin.users.status');
    Route::post('/users/{user}/notify', [UserController::class, 'sendNotification'])->name('admin.users.notify');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::resource('businesses', BusinessController::class)->except(['create', 'store', 'destroy']);
    Route::post('businesses/{business}/toggle-status', [BusinessController::class, 'toggleStatus'])
        ->name('businesses.toggle-status');
    Route::resource('professionals', ProfessionalController::class)
        ->except(['create', 'store', 'destroy'])
        ->names([
            'index' => 'admin.professionals.index',
            'show' => 'admin.professionals.show',
            'edit' => 'admin.professionals.edit',
            'update' => 'admin.professionals.update',
        ]);
    Route::post('professionals/{professional}/toggle-status', [ProfessionalController::class, 'toggleStatus'])
        ->name('admin.professionals.toggle-status');
    Route::resource('courses', CourseController::class)
        ->names([
            'index' => 'admin.courses.index',
            'show' => 'admin.courses.show',
            'edit' => 'admin.courses.edit',
            'update' => 'admin.courses.update',
            'create' => 'admin.courses.create',
            'store' => 'admin.courses.store',
            'destroy' => 'admin.courses.destroy',
        ]);
    // Course Modules Routes
    Route::prefix('courses/{course}')->name('admin.courses.')->group(function () {
        Route::resource('modules', CourseModuleController::class)->except(['index', 'create', 'show']);
        
        // Lesson Routes - Flattened structure
        Route::post('modules/{module}/lessons', [CourseLessonController::class, 'store'])
            ->name('modules.lessons.store')
            ->where('course', '[0-9a-f-]+')
            ->where('module', '[0-9a-f-]+');

        Route::get('lessons/{lesson}/edit', [CourseLessonController::class, 'edit'])
            ->name('lessons.edit')
            ->where('course', '[0-9a-f-]+')
            ->where('lesson', '[0-9a-f-]+');

        Route::put('lessons/{lesson}', [CourseLessonController::class, 'update'])
            ->name('lessons.update')
            ->where('course', '[0-9a-f-]+')
            ->where('lesson', '[0-9a-f-]+');

        Route::delete('lessons/{lesson}', [CourseLessonController::class, 'destroy'])
            ->name('lessons.destroy')
            ->where('course', '[0-9a-f-]+')
            ->where('lesson', '[0-9a-f-]+');

        // Quiz Management Routes
        Route::get('lessons/{lesson}/quiz/questions', [QuizController::class, 'getQuestions'])
            ->name('lessons.quiz.questions')
            ->where('lesson', '[0-9a-f-]+');

        Route::post('lessons/{lesson}/quiz/questions', [QuizController::class, 'storeQuestion'])
            ->name('lessons.quiz.questions.store')
            ->where('lesson', '[0-9a-f-]+');

        Route::get('quiz/questions/{question}/edit', [QuizController::class, 'edit'])
            ->name('lessons.quiz.questions.edit');

        Route::put('quiz/questions/{question}', [QuizController::class, 'updateQuestion'])
            ->name('lessons.quiz.questions.update');

        Route::delete('quiz/questions/{question}', [QuizController::class, 'destroyQuestion'])
            ->name('lessons.quiz.questions.destroy');

        Route::post('lessons/{lesson}/quiz/reorder', [QuizController::class, 'reorderQuestions'])
            ->name('lessons.quiz.reorder')
            ->where('lesson', '[0-9a-f-]+');

        // Course Topics Routes
        Route::post('modules/{module}/topics', [CourseTopicController::class, 'store'])->name('modules.topics.store');
        Route::get('topics/{topic}/edit', [CourseTopicController::class, 'edit'])->name('topics.edit');
        Route::put('topics/{topic}', [CourseTopicController::class, 'update'])->name('topics.update');
        Route::delete('topics/{topic}', [CourseTopicController::class, 'destroy'])->name('topics.destroy');

        // Course Schedule Routes
        Route::post('schedules', [CourseScheduleController::class, 'store'])->name('schedules.store');
        Route::get('schedules/{schedule}/edit', [CourseScheduleController::class, 'edit'])->name('schedules.edit');
        Route::put('schedules/{schedule}', [CourseScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('schedules/{schedule}', [CourseScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Course Exams Routes
        Route::get('exams', [CourseExamController::class, 'index'])->name('exams.index');
        Route::get('exams/create', [CourseExamController::class, 'create'])->name('exams.create');
        Route::post('exams', [CourseExamController::class, 'store'])->name('exams.store');
        Route::get('exams/{exam}', [CourseExamController::class, 'show'])->name('exams.show');
        Route::get('exams/{exam}/edit', [CourseExamController::class, 'edit'])->name('exams.edit');
        Route::put('exams/{exam}', [CourseExamController::class, 'update'])->name('exams.update');
        Route::delete('exams/{exam}', [CourseExamController::class, 'destroy'])->name('exams.destroy');
    });

    // Course Enrollments Notice
    Route::post('/courses/{course}/enrollments/send-notices', [CourseController::class, 'sendBulkNotices'])
        ->name('admin.courses.enrollments.send-notices');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('/orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('admin.orders.update-tracking');

    // Project routes
    Route::prefix('projects')->group(function () {
        Route::patch('{project}', [ProjectController::class, 'update'])->name('admin.projects.update');
        Route::patch('{project}/progress', [ProjectController::class, 'updateProgress'])->name('admin.projects.update-progress');
    });
});

// Temporary debug route list
Route::get('/debug-routes', function () {
    $routes = collect(\Route::getRoutes())->map(function ($route) {
        return [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'name' => $route->getName()
        ];
    });
    dd($routes->toArray());
});

// Temporary debug route
Route::get('/test-lesson-route/{course}/{module}', function ($course, $module) {
    return response()->json([
        'message' => 'Route is accessible',
        'course' => $course,
        'module' => $module
    ]);
})->where(['course' => '[0-9a-f-]+', 'module' => '[0-9a-f-]+']);

Route::get('/admin/courses/{course}/enrollments', [CourseController::class, 'enrollments'])
    ->name('admin.courses.enrollments');

Route::post('/admin/courses/{course}/enrollments/bulk-update', [CourseController::class, 'bulkUpdateEnrollments'])
    ->name('admin.courses.enrollments.bulk-update');

Route::get('/admin/courses/{course}/enrollments/{enrollment}', [CourseController::class, 'showEnrollment'])
    ->name('admin.courses.enrollments.show');

// Route::post('/workstation/plans/{plan}/toggle-status', [WorkstationPlanController::class, 'toggleStatus'])
//     ->name('admin.workstation.plans.toggle-status');

// Route::patch('/workstation/subscriptions/{subscription}/status', [WorkstationSubscriptionController::class, 'updateStatus'])
//     ->name('admin.workstation.subscriptions.update-status');

Route::get('/login', function () {
    return response('Login page not implemented', 404);
})->name('login');
