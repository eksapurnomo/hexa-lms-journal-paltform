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

Route::post('/account/activate', [\App\Http\Controllers\AccountActivationController::class, 'activateAccount']);
Route::post('/account/activation-code/resend', [\App\Http\Controllers\AccountActivationController::class, 'sendActivationCode']);

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
    Route::get('/user/dashboard-context', [\App\Http\Controllers\Api\UserDashboardController::class, 'context']);
    Route::get('/user/journals/{slug}/management/overview', [\App\Http\Controllers\Api\UserDashboardController::class, 'journalManagementOverview']);
    Route::get('/user/journals/{slug}/management/submissions', [\App\Http\Controllers\Api\UserDashboardController::class, 'journalSubmissions']);
    Route::get('/user/journals/{slug}/management/submissions/{submission}', [\App\Http\Controllers\Api\UserDashboardController::class, 'journalSubmissionDetail']);
    Route::get('/user/journals/{slug}/management/editorial-process', [\App\Http\Controllers\Api\UserDashboardController::class, 'journalEditorialProcess']);
    Route::get('/user/journals/{slug}/management/members', [\App\Http\Controllers\Api\UserDashboardController::class, 'journalMembers']);
    Route::get('/user/journals/{slug}/management/settings', [\App\Http\Controllers\Api\UserDashboardController::class, 'journalSettings']);
    Route::patch('/user/journals/{slug}/management/settings', [\App\Http\Controllers\Api\UserDashboardController::class, 'updateJournalSettings']);
    Route::get('/enroll_summary', [\App\Http\Controllers\EnrollController::class, 'summary']);
    Route::get('/enrolled_courses', [\App\Http\Controllers\EnrollController::class, 'index']);

    Route::get('/enroll/{course}', [EnrollController::class, 'initiateTransaction']);
    Route::get('/free/enroll/{course}', [EnrollController::class, 'freeEnrollment']);
    
    Route::get('/quiz/start/{quiz}', [QuizController::class, 'start']);
    Route::get('/view_content/{content}', [CourseController::class, 'viewContent']);
    
    Route::patch('/profile/update', [UserController::class, 'update']);
    Route::get('/profile/academic', [\App\Http\Controllers\Api\AcademicProfileController::class, 'show']);
    Route::patch('/profile/academic', [\App\Http\Controllers\Api\AcademicProfileController::class, 'update']);
    
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

    Route::prefix('admin/journals/{journal}/memberships')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\JournalMembershipController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\JournalMembershipController::class, 'store']);
        Route::put('/{membership}', [\App\Http\Controllers\Api\JournalMembershipController::class, 'update']);
        Route::delete('/{membership}', [\App\Http\Controllers\Api\JournalMembershipController::class, 'destroy']);
    });

    // Phase JRAF-3 Step 2 - Journal Membership Applications & Status API
    Route::prefix('journals/{journal}')->group(function () {
        Route::get('/membership-application', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'show']);
        Route::post('/membership-application', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'store']);
        Route::patch('/membership-application', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'update']);
        Route::post('/membership-application/submit', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'submit']);
        Route::get('/membership-application/status', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'status']);
        
        Route::get('/membership', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'membership']);
        Route::get('/reviewer-capability', [\App\Http\Controllers\Api\JournalMembershipApplicationController::class, 'reviewerCapability']);
    });
});

// Phase 5E-B Editorial Desk Routes
Route::middleware(['web', 'auth:web'])->prefix('editorial/submissions')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\EditorialDeskController::class, 'index']);
    Route::get('/{submission}', [\App\Http\Controllers\Api\EditorialDeskController::class, 'show']);
    Route::post('/{submission}/rounds', [\App\Http\Controllers\Api\EditorialDeskController::class, 'startReviewRound']);
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
