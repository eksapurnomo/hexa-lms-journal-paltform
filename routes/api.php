<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\NewslatterSubscriptionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\PaymentController;

// Public routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::post('/reset-password/validate', [ResetPasswordController::class, 'validateOtp']);
Route::patch('/update-password', [UserController::class, 'updatePassword']);

Route::post('/contact/submit', [ContactMessageController::class, 'submit']);
Route::post('/newslatter/subscribe', [NewslatterSubscriptionController::class, 'subscribe']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/instructor/list', [InstructorController::class, 'index']);
Route::get('/testimonial/list', [TestimonialController::class, 'index']);

Route::get('/course/list', [CourseController::class, 'index']);
Route::get('/course/show/{course}', [CourseController::class, 'show']);

Route::get('/journals', [\App\Http\Controllers\Api\JournalController::class, 'index']);
Route::get('/journals/{slug}', [\App\Http\Controllers\Api\JournalController::class, 'show']);

Route::get('/coupon/validate', [EnrollController::class, 'verifyCoupon']);

// Protected routes (JWT API guard)
Route::middleware('auth:api')->group(function () {
    Route::get('/enroll/{course}', [EnrollController::class, 'initiateTransaction']);
    Route::get('/free/enroll/{course}', [EnrollController::class, 'freeEnrollment']);
    
    Route::get('/quiz/start/{quiz}', [QuizController::class, 'start']);
    Route::get('/view_content/{content}', [CourseController::class, 'viewContent']);
    
    Route::patch('/profile/update', [UserController::class, 'update']);
    
    // UNCONFIRMED: /transactions could map to EnrollController@index or PaymentController@index
    Route::get('/transactions', [PaymentController::class, 'index']);

    Route::prefix('submissions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SubmissionController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\SubmissionController::class, 'store']);
        Route::get('/{submission}', [\App\Http\Controllers\Api\SubmissionController::class, 'show']);
        Route::put('/{submission}', [\App\Http\Controllers\Api\SubmissionController::class, 'update']);
        Route::delete('/{submission}', [\App\Http\Controllers\Api\SubmissionController::class, 'destroy']);
        Route::post('/{submission}/submit', [\App\Http\Controllers\Api\SubmissionController::class, 'submit']);
        Route::post('/{submission}/revision', [\App\Http\Controllers\Api\SubmissionController::class, 'submitRevision']);
        Route::post('/{submission}/files', [\App\Http\Controllers\Api\SubmissionController::class, 'uploadFile'])->name('submissions.files.store');
        Route::get('/{submission}/files/{file}/download', [\App\Http\Controllers\Api\SubmissionController::class, 'downloadFile'])->name('submissions.files.download');
    });
});

// Phase 5E-B Editorial Desk Routes
Route::middleware(['web', 'auth:web'])->prefix('editorial/submissions')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\EditorialDeskController::class, 'index']);
    Route::get('/{submission}', [\App\Http\Controllers\Api\EditorialDeskController::class, 'show']);
    Route::get('/{submission}/eligible-editors', [\App\Http\Controllers\Api\EditorialDeskController::class, 'eligibleEditors']);
    Route::patch('/{submission}/assign', [\App\Http\Controllers\Api\EditorialDeskController::class, 'assignEditor']);
    Route::patch('/{submission}/status', [\App\Http\Controllers\Api\EditorialDeskController::class, 'updateStatus']);
    Route::post('/{submission}/rounds/{round}/decision', [\App\Http\Controllers\Api\EditorialDeskController::class, 'recordEditorialDecision']);
    
    // Review Assignment routes for Editor
    Route::get('/{submission}/eligible-reviewers', [\App\Http\Controllers\Api\EditorialDeskController::class, 'eligibleReviewers']);
    Route::post('/{submission}/review-assignments', [\App\Http\Controllers\Api\EditorialDeskController::class, 'assignReviewer']);
    Route::delete('/{submission}/review-assignments/{assignment}', [\App\Http\Controllers\Api\EditorialDeskController::class, 'cancelReviewAssignment']);
});

// Phase 5F Reviewer Desk Routes
Route::middleware(['web', 'auth:web'])->prefix('reviewer/assignments')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'index']);
    Route::get('/{assignment}', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'show']);
    Route::post('/{assignment}/accept', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'accept']);
    Route::post('/{assignment}/decline', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'decline']);
    Route::post('/{assignment}/submit', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'submitReview']);
    Route::get('/{assignment}/files/{file}/download', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'downloadFile']);
    Route::get('/{assignment}/criteria', [\App\Http\Controllers\Api\ReviewerDeskController::class, 'getCriteria']);
});
