<?php

use Illuminate\Support\Facades\Route;

// Import all WebAdmin controllers
use App\Http\Controllers\WebAdmin\LoginController;
use App\Http\Controllers\WebAdmin\DashboardController;
use App\Http\Controllers\WebAdmin\UserController;
use App\Http\Controllers\WebAdmin\InstructorController;
use App\Http\Controllers\WebAdmin\CourseController;
use App\Http\Controllers\WebAdmin\ChapterController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\WebAdmin\CategoryController;
use App\Http\Controllers\WebAdmin\QuizController;
use App\Http\Controllers\WebAdmin\ExamController;
use App\Http\Controllers\WebAdmin\EnrollmentController;
use App\Http\Controllers\WebAdmin\ReviewController;
use App\Http\Controllers\WebAdmin\CouponController;
use App\Http\Controllers\WebAdmin\BlogController;
use App\Http\Controllers\WebAdmin\PageController;
use App\Http\Controllers\WebAdmin\NotificationController;
use App\Http\Controllers\WebAdmin\CustomNotificationController;
use App\Http\Controllers\WebAdmin\ReportController;
use App\Http\Controllers\WebAdmin\TransactionController;
use App\Http\Controllers\WebAdmin\PaymentGatewayController;
use App\Http\Controllers\WebAdmin\SettingController;
use App\Http\Controllers\WebAdmin\TestimonialController;
use App\Http\Controllers\WebAdmin\UserRoleController;
use App\Http\Controllers\WebAdmin\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\WebAdmin\NewslatterController;
use App\Http\Controllers\WebAdmin\ManageCertificateController;
use App\Http\Controllers\WebAdmin\ContactController;
use App\Http\Controllers\WebAdmin\StorageLinkController;

// Public admin authentication
Route::get('/admin/login', [LoginController::class, 'index'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::get('/admin/instructor/register', [LoginController::class, 'instructorRegister'])->name('instructor.register');
Route::post('/admin/instructor/register', [LoginController::class, 'instructorAuthenticate'])->name('instructor.authenticate');

Route::get('/link-storage', [StorageLinkController::class, 'linkStorage'])->name('link.storage');

// Protected Admin Routes
Route::middleware(['auth', 'adminauth'])->group(function () {
    
    // Dashboard
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/statistics', [DashboardController::class, 'statistics'])->name('admin.statistics');
    
    // Instructor dashboard uses DashboardController (assumed from instructor.dashboard route presence, though method might just be index)
    // Wait, DashboardController has `index` and `statistics`. The middleware separates them based on role.
    
    Route::prefix('admin')->group(function () {
        // Users
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/admin', [UserController::class, 'admin'])->name('user.admin');
        Route::get('/root', [UserController::class, 'admin'])->name('admin.index');
        Route::resource('/academic-reviewers', \App\Http\Controllers\WebAdmin\AcademicReviewerController::class)
            ->only(['index', 'show'])
            ->parameters(['academic-reviewers' => 'user']);
        Route::get('/cache-clear', function() { return back(); })->name('cache.clear');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{user}', [UserController::class, 'delete'])->name('user.destroy');
        Route::post('/user/{id}/restore', [UserController::class, 'restore'])->name('user.restore');

        // Instructors
        Route::get('/instructor', [InstructorController::class, 'index'])->name('instructor.index');
        Route::get('/instructor/featured', [InstructorController::class, 'featured'])->name('instructor.featured');
        Route::get('/instructor/create', [InstructorController::class, 'create'])->name('instructor.create');
        Route::post('/instructor/{user}/promote', [InstructorController::class, 'promote'])->name('instructor.promote');
        Route::post('/instructor/{user}/migrate', [InstructorController::class, 'migrate'])->name('instructor.migrate');
        Route::post('/instructor', [InstructorController::class, 'store'])->name('instructor.store');
        Route::get('/instructor/{instructor}/edit', [InstructorController::class, 'edit'])->name('instructor.edit');
        Route::put('/instructor/{instructor}', [InstructorController::class, 'update'])->name('instructor.update');
        Route::delete('/instructor/{instructor}', [InstructorController::class, 'delete'])->name('instructor.destroy');
        Route::post('/instructor/{id}/restore', [InstructorController::class, 'restore'])->name('instructor.restore');

        // Categories
        Route::get('/category', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/category/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/category/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        Route::put('/category/{category}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/{category}', [CategoryController::class, 'delete'])->name('category.destroy');
        Route::post('/category/sort', [CategoryController::class, 'sort'])->name('category.sort');
        Route::post('/category/{id}/restore', [CategoryController::class, 'restore'])->name('category.restore');

        // Courses
        Route::get('/course', [CourseController::class, 'index'])->name('course.index');
        Route::get('/course/create', [CourseController::class, 'create'])->name('course.create');
        Route::get('/course/{course}', [CourseController::class, 'show'])->name('course.show');
        Route::post('/course', [CourseController::class, 'store'])->name('course.store');
        Route::get('/course/{course}/edit', [CourseController::class, 'edit'])->name('course.edit');
        Route::put('/course/{course}', [CourseController::class, 'update'])->name('course.update');
        Route::delete('/course/{course}', [CourseController::class, 'delete'])->name('course.destroy');
        Route::post('/course/{id}/restore', [CourseController::class, 'restore'])->name('course.restore');

        // Chapters
        Route::get('/chapter/course', [ChapterController::class, 'selectCourse'])->name('chapter.select_course');
        Route::get('/chapter/{course}', [ChapterController::class, 'index'])->name('chapter.index');
        Route::get('/chapter/{course}/create', [ChapterController::class, 'create'])->name('chapter.create');
        Route::post('/chapter', [ChapterController::class, 'store'])->name('chapter.store');
        Route::get('/chapter/{chapter}/edit', [ChapterController::class, 'edit'])->name('chapter.edit');
        Route::put('/chapter/{chapter}', [ChapterController::class, 'update'])->name('chapter.update');
        Route::delete('/chapter/{chapter}', [ChapterController::class, 'delete'])->name('chapter.destroy');

        // Content
        Route::resource('content', ContentController::class);

        // Coupons
        Route::get('/coupon', [CouponController::class, 'index'])->name('coupon.index');
        Route::get('/coupon/create', [CouponController::class, 'create'])->name('coupon.create');
        Route::post('/coupon', [CouponController::class, 'store'])->name('coupon.store');
        Route::get('/coupon/{coupon}/edit', [CouponController::class, 'edit'])->name('coupon.edit');
        Route::put('/coupon/{coupon}', [CouponController::class, 'update'])->name('coupon.update');
        Route::delete('/coupon/{coupon}', [CouponController::class, 'delete'])->name('coupon.destroy');

        // Quizzes
        Route::get('/quiz/course', [QuizController::class, 'selectCourse'])->name('quiz.select_course');
        Route::get('/quiz/{course}', [QuizController::class, 'index'])->name('quiz.index');
        Route::get('/quiz/{course}/create', [QuizController::class, 'create'])->name('quiz.create');
        Route::post('/quiz', [QuizController::class, 'store'])->name('quiz.store');
        Route::get('/quiz/{quiz}/edit', [QuizController::class, 'edit'])->name('quiz.edit');
        Route::put('/quiz/{quiz}', [QuizController::class, 'update'])->name('quiz.update');
        Route::delete('/quiz/{quiz}', [QuizController::class, 'delete'])->name('quiz.destroy');

        // Exams
        Route::get('/exam/course', [ExamController::class, 'selectCourse'])->name('exam.select_course');
        Route::get('/exam/{course}', [ExamController::class, 'index'])->name('exam.index');
        Route::get('/exam/{course}/create', [ExamController::class, 'create'])->name('exam.create');
        Route::post('/exam', [ExamController::class, 'store'])->name('exam.store');
        Route::get('/exam/{exam}/edit', [ExamController::class, 'edit'])->name('exam.edit');
        Route::put('/exam/{exam}', [ExamController::class, 'update'])->name('exam.update');
        Route::delete('/exam/{exam}', [ExamController::class, 'delete'])->name('exam.destroy');

        // Enrollments
        Route::get('/enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::delete('/enrollment/{id}', [EnrollmentController::class, 'delete'])->name('enrollment.destroy');
        Route::post('/enrollment/{id}/suspended', [EnrollmentController::class, 'suspended'])->name('enrollment.suspended');
        Route::post('/enrollment/{id}/restore', [EnrollmentController::class, 'restore'])->name('enrollment.restore');

        // Reviews
        Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
        Route::delete('/review/{review}', [ReviewController::class, 'delete'])->name('review.destroy');

        // Blogs
        Route::resource('blog', BlogController::class);

        // Pages
        Route::get('/page', [PageController::class, 'index'])->name('page.index');
        Route::get('/page/{page}/edit', [PageController::class, 'edit'])->name('page.edit');
        Route::put('/page/{page}', [PageController::class, 'update'])->name('page.update');

        // Notifications
        Route::get('/notification', [NotificationController::class, 'index'])->name('notification.index');
        Route::get('/notification/{notification}/edit', [NotificationController::class, 'edit'])->name('notification.edit');
        Route::put('/notification/{notification}', [NotificationController::class, 'update'])->name('notification.update');
        Route::post('/notification/{notification}/status', [NotificationController::class, 'switchStatus'])->name('notification.switch.status');
        Route::post('/notification/instance/{notificationInstance}/read', [NotificationController::class, 'markAsRead'])->name('notification.read');
        Route::post('/notification/read-all', [NotificationController::class, 'markAsReadAll'])->name('notification.read.all');
        Route::get('/notification/custom', [CustomNotificationController::class, 'index'])->name('notification.custom.index');
        Route::post('/notification/custom', [CustomNotificationController::class, 'send'])->name('notification.custom.send.message');

        // Reports
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::get('/report/filter', [ReportController::class, 'filter'])->name('report.filter');
        Route::get('/report/pdf', [ReportController::class, 'generatePdf'])->name('report.generate.pdf');
        Route::get('/report/csv', [ReportController::class, 'exportCSV'])->name('report.exportCSV');

        // Transactions
        Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');

        // Payment Gateways
        Route::get('/payment-gateway', [PaymentGatewayController::class, 'index'])->name('payment_gateway.index');
        Route::put('/payment-gateway/{paymentGateway}', [PaymentGatewayController::class, 'update'])->name('payment_gateway.update');

        // Settings
        Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::put('/setting', [SettingController::class, 'update'])->name('setting.update');

        // Testimonials
        Route::get('/testimonial', [TestimonialController::class, 'index'])->name('testimonial.index');
        Route::get('/testimonial/create', [TestimonialController::class, 'create'])->name('testimonial.create');
        Route::post('/testimonial', [TestimonialController::class, 'store'])->name('testimonial.store');
        Route::get('/testimonial/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonial.edit');
        Route::put('/testimonial/{testimonial}', [TestimonialController::class, 'update'])->name('testimonial.update');
        Route::delete('/testimonial/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonial.destroy');
        Route::post('/testimonial/{testimonial}/restore', [TestimonialController::class, 'restore'])->name('testimonial.restore');

        // User Roles
        Route::get('/role', [UserRoleController::class, 'index'])->name('role.index');
        Route::get('/role/create', [UserRoleController::class, 'create'])->name('role.create');
        Route::post('/role', [UserRoleController::class, 'store'])->name('role.store');
        Route::put('/role/{role}', [UserRoleController::class, 'update'])->name('role.update');
        Route::delete('/role/{role}', [UserRoleController::class, 'delete'])->name('role.delete');
        Route::get('/role/{role}/permission', [UserRoleController::class, 'getPermission'])->name('role.get_permission');
        Route::post('/role/{role}/assign-permission', [UserRoleController::class, 'assignRoleToPermission'])->name('role.assign_roletopermission');
        Route::post('/user/{user}/assign-role', [UserRoleController::class, 'assignRoleToUser'])->name('role.assign_roletouser');
        Route::post('/user/{user}/remove-role/{role}', [UserRoleController::class, 'removeRoleFromUser'])->name('role.removeRoleFromUser');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
        Route::post('/profile/{user}/image', [ProfileController::class, 'profileImageUpdate'])->name('admin.profile.image.update');

        // Languages
        Route::get('/language', [LanguageController::class, 'index'])->name('language.index');
        Route::get('/language/create', [LanguageController::class, 'create'])->name('language.create');
        Route::post('/language', [LanguageController::class, 'store'])->name('language.store');
        Route::get('/language/{language}/edit', [LanguageController::class, 'edit'])->name('language.edit');
        Route::put('/language/{language}', [LanguageController::class, 'update'])->name('language.update');
        Route::delete('/language/{language}', [LanguageController::class, 'delete'])->name('language.delete');
        Route::post('/language/default', [LanguageController::class, 'setDefault'])->name('language.default');
        Route::get('/language/{language}/export', [LanguageController::class, 'export'])->name('language.export');
        Route::post('/language/import', [LanguageController::class, 'import'])->name('language.import');
        
        // Newsletter
        Route::get('/newsletter', [NewslatterController::class, 'index'])->name('newslatter.index');
        Route::delete('/newsletter/{id}', [NewslatterController::class, 'delete'])->name('newslatter.delete');
        Route::post('/newsletter/{id}/restore', [NewslatterController::class, 'restore'])->name('newslatter.restore');
        Route::post('/newsletter/{id}/mail', [NewslatterController::class, 'sendMail'])->name('newslatter.send.mail');

        // Certificates
        Route::get('/certificate', [ManageCertificateController::class, 'index'])->name('certificate.index');
        Route::put('/certificate', [ManageCertificateController::class, 'update'])->name('certificate.update');
        Route::delete('/certificate/{id}', [ManageCertificateController::class, 'delete'])->name('certificate.delete');

        // Contacts
        Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
        Route::delete('/contact/{id}', [ContactController::class, 'delete'])->name('contact.destroy');

        // Reviewer Applications (Admin)
        Route::get('/journal/reviewer-applications', [\App\Http\Controllers\WebAdmin\ReviewerApplicationController::class, 'index'])->name('admin.reviewer_applications.index');
        Route::get('/journal/reviewer-applications/{id}', [\App\Http\Controllers\WebAdmin\ReviewerApplicationController::class, 'show'])->name('admin.reviewer_applications.show');
        Route::post('/journal/reviewer-applications/{id}/accept', [\App\Http\Controllers\WebAdmin\ReviewerApplicationController::class, 'accept'])->name('admin.reviewer_applications.accept');
        Route::post('/journal/reviewer-applications/{id}/deny', [\App\Http\Controllers\WebAdmin\ReviewerApplicationController::class, 'deny'])->name('admin.reviewer_applications.deny');
        // Membership Verification (Admin)
        Route::get('/journal/membership-verifications', [\App\Http\Controllers\WebAdmin\MembershipVerificationController::class, 'index'])->name('admin.membership-verifications.index');
        Route::get('/journal/membership-verifications/{id}', [\App\Http\Controllers\WebAdmin\MembershipVerificationController::class, 'show'])->name('admin.membership-verifications.show');
        Route::post('/journal/membership-verifications/{id}/status', [\App\Http\Controllers\WebAdmin\MembershipVerificationController::class, 'updateStatus'])->name('admin.membership-verifications.updateStatus');
        Route::get('/journal/verification-evidence/{evidence}/download', [\App\Http\Controllers\VerificationEvidenceController::class, 'download'])->name('admin.verification-evidence.download');
    });
});

// Protected Journal Admin Routes (Not restricted to LMS Instructor/Admin)
Route::middleware(['auth'])->group(function () {
    Route::prefix('admin/journals')->group(function () {
        Route::get('/', [\App\Http\Controllers\JournalController::class, 'index'])->name('journal.index');
        Route::post('/', [\App\Http\Controllers\JournalController::class, 'store'])->name('journal.store');
        Route::put('/{journal}', [\App\Http\Controllers\JournalController::class, 'update'])->name('journal.update');
        Route::delete('/{journal}', [\App\Http\Controllers\JournalController::class, 'delete'])->name('journal.destroy');
    });

    // Reviewer Applications (User)
    Route::get('journal/reviewer/apply', [\App\Http\Controllers\ReviewerApplicationController::class, 'create'])->name('reviewer.apply');
    Route::post('journal/reviewer/apply', [\App\Http\Controllers\ReviewerApplicationController::class, 'store'])->name('reviewer.store');
    Route::get('journal/reviewer/application', [\App\Http\Controllers\ReviewerApplicationController::class, 'show'])->name('reviewer.application.status');

    // Journal Membership Applications (User)
    Route::resource('journal/membership-applications', \App\Http\Controllers\JournalMembershipApplicationController::class)->except(['destroy']);
    Route::post('journal/membership-applications/{id}/submit', [\App\Http\Controllers\JournalMembershipApplicationController::class, 'submit'])->name('membership-applications.submit');
    Route::post('journal/membership-applications/{application}/evidence', [\App\Http\Controllers\VerificationEvidenceController::class, 'store'])->name('verification-evidence.store');
    Route::get('journal/verification-evidence/{evidence}/download', [\App\Http\Controllers\VerificationEvidenceController::class, 'download'])->name('verification-evidence.download');

    Route::get('admin/journal/process-flow', [\App\Http\Controllers\WebAdmin\JournalProcessFlowController::class, 'index'])->name('admin.journal.process-flow');
    Route::get('admin/journal/process-flow/users', [\App\Http\Controllers\WebAdmin\JournalUserProcessMonitorController::class, 'index'])->name('admin.journal.process-flow.users.index');
    Route::get('admin/journal/process-flow/{journal}/users/{user}', [\App\Http\Controllers\WebAdmin\JournalUserProcessMonitorController::class, 'show'])->name('admin.journal.process-flow.users.show');

    Route::get('admin/editorial/{any?}', function () {
        return view('editorial.index');
    })->where('any', '.*')->name('admin.editorial');

    Route::get('reviewer/{any?}', function () {
        return view('reviewer.index');
    })->where('any', '.*')->name('reviewer.desk');
});

Route::get('/change-language/{lang}', function($lang) {
    $supportedLocales = ['en', 'id'];
    if (in_array($lang, $supportedLocales)) {
        session()->put('locale', $lang);
    }
    return back();
})->name('change.language');

Route::get('/{any}', function () {
    return view('website');
})->where('any', '.*');
