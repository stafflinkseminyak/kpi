<?php

use App\Http\Controllers\AdminAppointmentController;
use App\Http\Controllers\AdminVendorController;
use App\Http\Controllers\AdminBusinessController;
use App\Http\Controllers\AdminBusinessLogController;
use App\Http\Controllers\AdminBusinessServiceController;
use App\Http\Controllers\AdminBusinessInvoiceController;
use App\Http\Controllers\AdminBusinessMeetingController;
use App\Http\Controllers\AdminBusinessManagementController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminJobseekerController;
use App\Http\Controllers\AdminClientDocumentController;
use App\Http\Controllers\AdminClientInvoiceController;
use App\Http\Controllers\AdminClientServiceController;
use App\Http\Controllers\AdminClientLogController;
use App\Http\Controllers\AdminClientMeetingController;

use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminBlogPostController;
use App\Http\Controllers\AdminCareerController;
use App\Http\Controllers\AdminContactInquiryController;
use App\Http\Controllers\AdminContractController;
use App\Http\Controllers\AdminKpiJobController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDivisionPositionController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminForgotPasswordController;
use App\Http\Controllers\AdminHeaderFooterController;
use App\Http\Controllers\AdminJobApplicationController;
use App\Http\Controllers\AdminLeadController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\JobseekerPortalController;
use App\Http\Controllers\AdminNannyInquiryController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AdminPageWordingController;
use App\Http\Controllers\AdminPasswordController;
use App\Http\Controllers\AdminRolePageWordingController;
use App\Http\Controllers\AdminServiceAreaController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CareerCategoryController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\GlobalStaffingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NannyInquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\ServiceDetailController;
use App\Http\Controllers\SitemapPageController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\RosterPlanController;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [HomeController::class, 'index']);
Route::view('/contact', 'contact')->name('contact');
Route::post('/contact', [ContactInquiryController::class, 'store'])->middleware('throttle:3,60')->name('contact.store');
Route::get('/forms/nannies-inquiry', [NannyInquiryController::class, 'create'])->name('forms.nannies-inquiry');
Route::post('/forms/nannies-inquiry', [NannyInquiryController::class, 'store'])->name('forms.nannies-inquiry.store');
Route::view('/who-we-are', 'who-we-are')->name('who-we-are');
Route::view('/what-we-offer', 'what-we-offer')->name('what-we-offer');
Route::view('/our-people-your-dream-team', 'our-people-your-dream-team')->name('our-people-your-dream-team');
Route::view('/our-purpose-business-principles', 'our-purpose-business-principles')->name('our-purpose-business-principles');
Route::view('/terms-and-condition', 'terms-and-condition')->name('terms-and-condition');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::get('/airport-services/nanny-concierge', [ServiceDetailController::class, 'airportServices'])->name('airport-services.nanny-concierge');
Route::view('/airport-services/baggage-drop-off', 'airport-baggage-drop-off')->name('airport-services.baggage-drop-off');
Route::get('/airport-services/nanny-concierge/areas/{areaSlug}', [ServiceDetailController::class, 'airportServicesArea'])->name('airport-services.nanny-concierge.area');
Route::redirect('/airport-services', '/airport-services/nanny-concierge');
Route::view('/services/wedding-organizer', 'wedding-organizer')->name('services.wedding-organizer');
Route::view('/services/destination-weddings-australians-bali', 'destination-weddings-australians-bali')->name('services.destination-weddings-australians-bali');
Route::view('/services/bali-relocation-support', 'bali-relocation-support')->name('services.bali-relocation-support');
Route::view('/services/retire-in-bali', 'retire-in-bali')->name('services.retire-in-bali');
Route::view('/services/schoolies-australia-bali', 'schoolies-australia-bali')->name('services.schoolies-australia-bali');
Route::view('/services/schoolies-parents', 'schoolies-parents')->name('services.schoolies-parents');
Route::view('/services/schoolies-bali-packages', 'schoolies-bali-packages')->name('services.schoolies-bali-packages');
Route::view('/services/sectors/remote-worker', 'roles.remote-worker')->name('services.sectors.remote-worker');
Route::get('/services/sectors/{slug}', [ServiceDetailController::class, 'sector'])->name('services.sectors.show');
Route::get('/services/sectors/{slug}/areas/{areaSlug}', [ServiceDetailController::class, 'sectorArea'])->name('services.sectors.areas.show');
Route::redirect('/services/roles/remote-worker', '/services/sectors/remote-worker');
Route::get('/services/roles/{slug}', [ServiceDetailController::class, 'role'])->name('services.roles.show');
Route::get('/services/roles/{slug}/areas/{areaSlug}', [ServiceDetailController::class, 'roleArea'])->name('services.roles.areas.show');
Route::get('/services/areas/{areaSlug}', [ServiceDetailController::class, 'area'])->name('services.areas.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{blogPost:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/jobs', [CareerController::class, 'index'])->name('jobs.index');
Route::get('/appointment', [AppointmentController::class, 'create'])->name('appointments.create');
Route::get('/appointment/availability', [AppointmentController::class, 'availability'])->name('appointments.availability');
Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointments.store');
Route::get('/apply-now', [JobApplicationController::class, 'create'])->name('applications.create');
Route::post('/apply-now', [JobApplicationController::class, 'store'])->name('applications.store');
Route::get('/references/{token}', [ReferenceController::class, 'show'])->name('references.show');
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/sitemap', SitemapPageController::class)->name('sitemap.page');
Route::get('/global-staffing/{country}', [GlobalStaffingController::class, 'show'])->name('global-staffing.country');
Route::redirect('/p/australia', '/global-staffing/australia');
Route::get('/{country}', [GlobalStaffingController::class, 'show'])
    ->where('country', 'australia|america|usa|us|united-states|united-states-of-america|america-usa|indonesia|bali|canada|malaysia|germany|singapore');

// Admin auth
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::get('/admin/register/activate/{token}', [AdminAuthController::class, 'showActivateForm'])->name('admin.register.activate');
Route::post('/admin/register/activate', [AdminAuthController::class, 'activateSubmit'])->name('admin.register.activate.submit');
Route::get('/client/activate/{token}', [AdminAuthController::class, 'showClientActivateForm'])->name('client.activate.form');
Route::post('/client/activate', [AdminAuthController::class, 'clientActivateSubmit'])->name('client.activate.submit');
Route::get('/client/login', [AdminAuthController::class, 'showClientLogin'])->name('client.login');
Route::post('/client/login', [AdminAuthController::class, 'clientLogin'])->name('client.login.submit');
Route::get('/jobseeker/activate/{token}', [AdminAuthController::class, 'showJobseekerActivateForm'])->name('jobseeker.activate.form');
Route::post('/jobseeker/activate', [AdminAuthController::class, 'jobseekerActivateSubmit'])->name('jobseeker.activate.submit');
Route::get('/jobseeker/login', [AdminAuthController::class, 'showJobseekerLogin'])->name('jobseeker.login');
Route::post('/jobseeker/login', [AdminAuthController::class, 'jobseekerLogin'])->name('jobseeker.login.submit');
Route::middleware('auth')->group(function () {
    Route::get('/client/dashboard', [ClientPortalController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/client/requests/{service}', [ClientPortalController::class, 'viewCandidates'])->name('client.requests.show');
    Route::get('/client/candidates/{assignment}/download', [ClientPortalController::class, 'downloadCandidateFile'])->name('client.candidates.download');
    Route::post('/client/candidates/{assignment}/respond', [ClientPortalController::class, 'respondToCandidate'])->name('client.candidates.respond');
    Route::post('/client/candidates/{assignment}/stage', [ClientPortalController::class, 'updateStage'])->name('client.candidates.stage');
    Route::post('/client/requests/{service}/notes', [ClientPortalController::class, 'updateNotes'])->name('client.requests.notes');
    Route::get('/client/requests/{service}/export', [ClientPortalController::class, 'exportCandidates'])->name('client.requests.export');
    Route::get('/client/account', [ClientPortalController::class, 'accountSettings'])->name('client.account');
    Route::post('/client/account', [ClientPortalController::class, 'updateAccount'])->name('client.account.update');
    Route::post('/client/account/password', [ClientPortalController::class, 'updatePassword'])->name('client.account.password');

    Route::get('/jobseeker/dashboard', [JobseekerPortalController::class, 'dashboard'])->name('jobseeker.dashboard');
    Route::get('/jobseeker/summary', [JobseekerPortalController::class, 'summary'])->name('jobseeker.summary');
    Route::get('/jobseeker/profile/{step}', [JobseekerPortalController::class, 'showStep'])->name('jobseeker.profile.step')->where('step', '[1-8]');
    Route::post('/jobseeker/profile', [JobseekerPortalController::class, 'updateProfile'])->name('jobseeker.profile.update');
    Route::get('/jobseeker/documents/{type}', [JobseekerPortalController::class, 'downloadDocument'])->name('jobseeker.document.download');
    Route::post('/jobseeker/status/toggle', [JobseekerPortalController::class, 'toggleLookingStatus'])->name('jobseeker.status.toggle');
    Route::post('/jobseeker/health-questionnaire', [JobseekerPortalController::class, 'submitHealthQuestionnaire'])->name('jobseeker.health.submit');
    Route::get('/jobseeker/health-questionnaire/{id}/signature', [JobseekerPortalController::class, 'viewSignature'])->name('jobseeker.health.signature');
    Route::get('/jobseeker/photo', [JobseekerPortalController::class, 'viewPhoto'])->name('jobseeker.photo');
    Route::get('/jobseeker/account', [JobseekerPortalController::class, 'accountSettings'])->name('jobseeker.account');
    Route::post('/jobseeker/account', [JobseekerPortalController::class, 'updateAccount'])->name('jobseeker.account.update');
    Route::post('/jobseeker/account/password', [JobseekerPortalController::class, 'updatePassword'])->name('jobseeker.account.password');
});
Route::middleware('guest')->group(function () {
    Route::get('/admin/forgot-password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
    Route::post('/admin/forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('admin.password.email');
    Route::get('/admin/reset-password/{token}', [AdminForgotPasswordController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('/admin/reset-password', [AdminForgotPasswordController::class, 'reset'])->name('admin.password.reset.submit');
});

// Admin (protected)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/linkers-hub', [AdminController::class, 'linkersHub'])->name('linkers-hub.index');
        Route::get('/linkers-hub/add-employee', [AdminController::class, 'addEmployee'])->name('linkers-hub.add-employee');
        Route::get('/linkers-hub/employees/{id}/profile', [AdminController::class, 'showEmployeeProfile'])->name('linkers-hub.employee-profile');
        Route::get('/linkers-hub/employees/{id}/add-absence/{type}', [AdminController::class, 'showAddAbsence'])->name('linkers-hub.add-absence');
        Route::post('/linkers-hub/add-employee', [AdminController::class, 'storeEmployee'])->name('linkers-hub.store-employee');
        Route::post('/linkers-hub/check-duplicate-employee', [AdminController::class, 'checkDuplicateEmployee'])->name('linkers-hub.check-duplicate-employee');
        Route::put('/linkers-hub/employees/{id}', [AdminController::class, 'updateEmployee'])->name('linkers-hub.update-employee');
        Route::delete('/linkers-hub/employees/{id}', [AdminController::class, 'destroyEmployee'])->name('linkers-hub.destroy-employee');
        Route::post('/linkers-hub/employees/{id}/avatar', [AdminController::class, 'uploadAvatar'])->name('linkers-hub.upload-avatar');
        Route::delete('/linkers-hub/employees/{id}/avatar', [AdminController::class, 'deleteAvatar'])->name('linkers-hub.delete-avatar');
        Route::get('/linkers-hub/employees/{id}/avatar', [AdminController::class, 'serveAvatar'])->name('linkers-hub.serve-avatar');
        Route::post('/linkers-hub/employees/{id}/emergency-contacts', [AdminController::class, 'storeEmergencyContact'])->name('linkers-hub.emergency-contacts.store');
        Route::put('/linkers-hub/employees/{employeeId}/emergency-contacts/{contactId}', [AdminController::class, 'updateEmergencyContact'])->name('linkers-hub.emergency-contacts.update');
        Route::delete('/linkers-hub/employees/{employeeId}/emergency-contacts/{contactId}', [AdminController::class, 'destroyEmergencyContact'])->name('linkers-hub.emergency-contacts.destroy');
        Route::post('/linkers-hub/send-registration-email', [AdminController::class, 'sendRegistrationEmail'])->name('linkers-hub.send-registration-email');

        // Employee Files
        Route::post('/linkers-hub/employees/{id}/absences', [AdminController::class, 'storeAbsence'])->name('linkers-hub.store-absence');
        Route::delete('/linkers-hub/absences/{absenceId}', [AdminController::class, 'destroyAbsence'])->name('linkers-hub.destroy-absence');
        Route::get('/linkers-hub/employees/{id}/files', [AdminController::class, 'listEmployeeFiles'])->name('linkers-hub.files.index');
        Route::post('/linkers-hub/employees/{id}/files', [AdminController::class, 'uploadEmployeeFile'])->name('linkers-hub.files.upload');
        Route::get('/linkers-hub/employees/{id}/files/{fileId}/download', [AdminController::class, 'downloadEmployeeFile'])->name('linkers-hub.files.download');
        Route::get('/linkers-hub/employees/{id}/files/{fileId}/view', [AdminController::class, 'viewEmployeeFile'])->name('linkers-hub.files.view');
        Route::delete('/linkers-hub/employees/{id}/files/{fileId}', [AdminController::class, 'deleteEmployeeFile'])->name('linkers-hub.files.delete');
        Route::post('/linkers-hub/employees/{id}/files/{fileId}/delete', [AdminController::class, 'deleteEmployeeFile'])->name('linkers-hub.files.delete-post');
        Route::post('/linkers-hub/employees/{id}/files/bulk-download', [AdminController::class, 'bulkDownloadEmployeeFiles'])->name('linkers-hub.files.bulk-download');

        // Employee Folders
        Route::post('/linkers-hub/employees/{id}/folders', [AdminController::class, 'storeEmployeeFolder'])->name('linkers-hub.folders.store');
        Route::put('/linkers-hub/employees/{id}/folders/{folderId}', [AdminController::class, 'updateEmployeeFolder'])->name('linkers-hub.folders.update');
        Route::post('/linkers-hub/employees/{id}/folders/{folderId}/update', [AdminController::class, 'updateEmployeeFolder'])->name('linkers-hub.folders.update-post');
        Route::delete('/linkers-hub/employees/{id}/folders/{folderId}', [AdminController::class, 'deleteEmployeeFolder'])->name('linkers-hub.folders.delete');
        Route::post('/linkers-hub/employees/{id}/folders/{folderId}/delete', [AdminController::class, 'deleteEmployeeFolder'])->name('linkers-hub.folders.delete-post');

        // Linkers Hub - Team (Division) CRUD
        Route::post('/linkers-hub/teams', [AdminController::class, 'storeTeam'])->name('linkers-hub.store-team');
        Route::put('/linkers-hub/teams/{id}', [AdminController::class, 'updateTeam'])->name('linkers-hub.update-team');
        Route::delete('/linkers-hub/teams/{id}', [AdminController::class, 'deleteTeam'])->name('linkers-hub.delete-team');

    // Performance
    Route::get('/performance', [AdminController::class, 'performance'])->name('performance.index');

    // E-Learning
    Route::get('/elearning', [\App\Http\Controllers\ELearningController::class, 'index'])->name('elearning.index');
    Route::get('/elearning/manage', [\App\Http\Controllers\ELearningController::class, 'manage'])->name('elearning.manage');
    Route::post('/elearning/store', [\App\Http\Controllers\ELearningController::class, 'store'])->name('elearning.store');
    Route::delete('/elearning/{id}', [\App\Http\Controllers\ELearningController::class, 'destroy'])->name('elearning.destroy');
    Route::post('/elearning/progress', [\App\Http\Controllers\ELearningController::class, 'updateProgress'])->name('elearning.progress');
    Route::get('/elearning/{slug}', [\App\Http\Controllers\ELearningController::class, 'show'])->name('elearning.show');

    // E-Learning - Course create/edit
    Route::get('/elearning-courses/create', [\App\Http\Controllers\ELearningController::class, 'create'])->name('elearning.course.create');
    Route::get('/elearning-courses/{id}/edit', [\App\Http\Controllers\ELearningController::class, 'edit'])->name('elearning.course.edit');
    Route::put('/elearning-courses/{id}', [\App\Http\Controllers\ELearningController::class, 'update'])->name('elearning.course.update');
    Route::get('/elearning-courses/{id}/questions', [\App\Http\Controllers\ELearningController::class, 'courseQuestions'])->name('elearning.course.questions');
    Route::post('/elearning-courses/{id}/questions', [\App\Http\Controllers\ELearningController::class, 'saveCourseQuestions'])->name('elearning.course.saveQuestions');

    // E-Learning - Test management
    Route::get('/elearning-tests', [\App\Http\Controllers\TestController::class, 'index'])->name('elearning.test.index');
    Route::get('/elearning-tests/create', [\App\Http\Controllers\TestController::class, 'create'])->name('elearning.test.create');
    Route::post('/elearning-tests', [\App\Http\Controllers\TestController::class, 'store'])->name('elearning.test.store');
    Route::get('/elearning-tests/{id}/questions', [\App\Http\Controllers\TestController::class, 'questions'])->name('elearning.test.questions');
    Route::post('/elearning-tests/{id}/questions', [\App\Http\Controllers\TestController::class, 'saveQuestions'])->name('elearning.test.saveQuestions');
    Route::delete('/elearning-tests/{id}', [\App\Http\Controllers\TestController::class, 'destroy'])->name('elearning.test.destroy');
    Route::get('/elearning-tests/{id}/edit', [\App\Http\Controllers\TestController::class, 'edit'])->name('elearning.test.edit');
    Route::put('/elearning-tests/{id}', [\App\Http\Controllers\TestController::class, 'update'])->name('elearning.test.update');

    // E-Learning - Quiz/Test system (prototype: English Proficiency Test)
    Route::get('/elearning/quiz/{slug}/instructions', [\App\Http\Controllers\QuizController::class, 'instructions'])->name('elearning.quiz.instructions');
    Route::get('/elearning/quiz/{slug}/take', [\App\Http\Controllers\QuizController::class, 'take'])->name('elearning.quiz.take');
    Route::get('/elearning/quiz/{slug}/result', [\App\Http\Controllers\QuizController::class, 'result'])->name('elearning.quiz.result');
    Route::post('/elearning/quiz/{slug}/submit-result', [\App\Http\Controllers\QuizController::class, 'submitResult'])->name('elearning.quiz.submitResult');

    // Certificates
    Route::get('/elearning/certificate/{certificateNumber}', [\App\Http\Controllers\CertificateController::class, 'show'])->name('elearning.certificate.show');
    Route::get('/elearning/my-certificates', [\App\Http\Controllers\CertificateController::class, 'myCertificates'])->name('elearning.certificate.mine');

    Route::get('/password', [AdminPasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [AdminPasswordController::class, 'update'])->name('password.update');

    // Profile
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // Shared for super admin and admin
    Route::middleware(EnsureUserRole::class.':super_admin,admin')->group(function () {
        Route::resource('careers', AdminCareerController::class)->except(['show']);
        Route::resource('blog-posts', AdminBlogPostController::class)->except(['show']);
        Route::resource('faqs', AdminFaqController::class)->except(['show']);
        Route::get('/applicants', [AdminJobApplicationController::class, 'index'])->name('applicants.index');
        Route::get('/applicants/by-job/{job}', [AdminJobApplicationController::class, 'byJob'])->name('applicants.by-job');
        Route::post('/applicants', [AdminJobApplicationController::class, 'storeCandidate'])->name('applicants.store');
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::patch('/leads/{appointment}', [AdminLeadController::class, 'update'])->name('leads.update');
        Route::post('/leads/{appointment}/convert-to-client', [AdminLeadController::class, 'convertToClient'])->name('leads.convert-to-client');
        Route::post('/clients/{client}/activate-portal', [AdminLeadController::class, 'activatePortalAccount'])->name('clients.activate-portal');
        Route::post('/clients/{client}/assign-candidate', [AdminLeadController::class, 'assignCandidate'])->name('clients.assign-candidate');
        Route::patch('/client-portal-accounts/{portalAccount}/deactivate', [AdminLeadController::class, 'deactivatePortalAccount'])->name('client-portal.deactivate');
        Route::patch('/client-portal-accounts/{portalAccount}/reactivate', [AdminLeadController::class, 'reactivatePortalAccount'])->name('client-portal.reactivate');
        Route::post('/client-candidate-assignments/{assignment}/notes', [AdminLeadController::class, 'updateCandidateNotes'])->name('client-candidate-assignments.notes');
        Route::delete('/client-candidate-assignments/{assignment}', [AdminLeadController::class, 'removeCandidateAssignment'])->name('client-candidate-assignments.destroy');

        // Finance
        Route::get('/finance/sales', [AdminFinanceController::class, 'sales'])->name('finance.sales');
        Route::get('/finance/invoices', [AdminFinanceController::class, 'invoices'])->name('finance.invoices');
        Route::get('/finance/invoices/export', [AdminFinanceController::class, 'exportInvoices'])->name('finance.invoices.export');
        Route::post('/finance/invoices', [AdminFinanceController::class, 'storeInvoice'])->name('finance.invoices.store');
        Route::post('/finance/invoices/generate-all-due', [AdminFinanceController::class, 'generateAllDue'])->name('finance.invoices.generateAllDue');
        Route::get('/finance/liability', [AdminFinanceController::class, 'liability'])->name('finance.liability');
        Route::get('/finance/salary', [AdminFinanceController::class, 'salary'])->name('finance.salary');
        Route::put('/finance/salary/{employee}', [AdminFinanceController::class, 'updateSalary'])->name('finance.salary.update');
        Route::get('/finance/bpjs', [AdminFinanceController::class, 'bpjs'])->name('finance.bpjs');
        Route::put('/finance/bpjs/{employee}', [AdminFinanceController::class, 'updateBpjs'])->name('finance.bpjs.update');

        // Bills (Payables) — Xero-style
        // Note: /finance/bills/create must be registered BEFORE /finance/bills/{bill}
        // to prevent Laravel from treating "create" as a Bill model ID.
        Route::get('/bills',                 [BillController::class, 'index'])->name('finance.bills.index');
        Route::get('/bills/create',          [BillController::class, 'create'])->name('finance.bills.create');
        Route::post('/bills',                [BillController::class, 'store'])->name('finance.bills.store');
        Route::get('/bills/{bill}',          [BillController::class, 'show'])->name('finance.bills.show');
        Route::get('/bills/{bill}/edit',     [BillController::class, 'edit'])->name('finance.bills.edit');
        Route::put('/bills/{bill}',          [BillController::class, 'update'])->name('finance.bills.update');
        Route::post('/bills/{bill}/delete',  [BillController::class, 'destroy'])->name('finance.bills.destroy');
        // Rosters & Shifts
        Route::get('/rosters',                        [RosterController::class, 'index'])->name('rosters.index');
        Route::post('/rosters/templates',             [RosterController::class, 'storeTemplate'])->name('rosters.templates.store');
        Route::post('/rosters/templates/{id}/delete', [RosterController::class, 'destroyTemplate'])->name('rosters.templates.destroy');
        Route::post('/rosters/settings',               [RosterController::class, 'updateSettings'])->name('rosters.settings.update');
        Route::get('/rosters/report',                  [RosterController::class, 'report'])->name('rosters.report');

        // Roster Plans (BrightHR-style "Create roster" flow)
        Route::post('/rosters/plans',                     [RosterPlanController::class, 'store'])->name('rosters.plans.store');
        Route::get('/rosters/plans/{plan}',                [RosterPlanController::class, 'show'])->name('rosters.plans.show');
        Route::post('/rosters/plans/{plan}/shifts',        [RosterPlanController::class, 'storeShift'])->name('rosters.plans.shifts.store');
        Route::post('/rosters/plans/shifts/{shift}/employees',           [RosterPlanController::class, 'addEmployeeToShift'])->name('rosters.plans.shifts.add-employee');
        Route::post('/rosters/plans/shifts/{shift}/employees/{employeeId}/remove', [RosterPlanController::class, 'removeEmployeeFromShift'])->name('rosters.plans.shifts.remove-employee');
        Route::post('/rosters/plans/shifts/{shift}/employees/{employeeId}/accept', [RosterPlanController::class, 'acceptShift'])->name('rosters.plans.shifts.accept');
        Route::post('/rosters/plans/shifts/{shift}/employees/{employeeId}/decline', [RosterPlanController::class, 'declineShift'])->name('rosters.plans.shifts.decline');
        Route::post('/rosters/plans/shifts/{shift}/employees/{employeeId}/request-open', [RosterPlanController::class, 'requestOpenShift'])->name('rosters.plans.shifts.request-open');
        Route::post('/rosters/plans/{plan}/publish',       [RosterPlanController::class, 'publish'])->name('rosters.plans.publish');
        Route::post('/rosters/plans/{plan}/view',          [RosterPlanController::class, 'updateView'])->name('rosters.plans.update-view');
        Route::post('/rosters/plans/{plan}/delete',        [RosterPlanController::class, 'destroy'])->name('rosters.plans.destroy');
        Route::post('/rosters/plans/{plan}/add-week',      [RosterPlanController::class, 'addWeek'])->name('rosters.plans.add-week');
        Route::post('/rosters/plans/{plan}/remove-week',   [RosterPlanController::class, 'removeWeek'])->name('rosters.plans.remove-week');
        Route::post('/rosters/plans/{plan}/open-shifts',           [RosterPlanController::class, 'storeOpenShift'])->name('rosters.plans.open-shifts.store');
        Route::post('/rosters/plans/open-shifts/{shift}/claim/{employeeId}', [RosterPlanController::class, 'toggleClaim'])->name('rosters.plans.open-shifts.toggle-claim');

        // Roster Plans: Drag & drop view (shift patterns as columns)
        Route::post('/rosters/plans/{plan}/patterns',            [RosterPlanController::class, 'storePattern'])->name('rosters.plans.patterns.store');
        Route::post('/rosters/plans/patterns/{pattern}/update',  [RosterPlanController::class, 'updatePattern'])->name('rosters.plans.patterns.update');
        Route::post('/rosters/plans/patterns/{pattern}/delete',  [RosterPlanController::class, 'destroyPattern'])->name('rosters.plans.patterns.destroy');
        Route::post('/rosters/plans/{plan}/dragdrop/assign',     [RosterPlanController::class, 'assignDragDrop'])->name('rosters.plans.dragdrop.assign');
        Route::post('/rosters/plans/dragdrop/unassign/{shift}/{employeeId}', [RosterPlanController::class, 'unassignDragDrop'])->name('rosters.plans.dragdrop.unassign');

        Route::get('/contact-inquiries', [AdminContactInquiryController::class, 'index'])->name('contact-inquiries.index');
        Route::delete('/contact-inquiries/{contactInquiry}', [AdminContactInquiryController::class, 'destroy'])->name('contact-inquiries.destroy');
            Route::patch('/contact-inquiries/{contactInquiry}/approve', [AdminContactInquiryController::class, 'approve'])->name('contact-inquiries.approve');
            Route::patch('/contact-inquiries/{contactInquiry}/reschedule', [AdminContactInquiryController::class, 'reschedule'])->name('contact-inquiries.reschedule');
        Route::get('/contracts', [AdminContractController::class, 'index'])->name('contracts.index');
        Route::get('/contracts/create', [AdminContractController::class, 'create'])->name('contracts.create');
        Route::get('/contracts/preview', [AdminContractController::class, 'preview'])->name('contracts.preview');
        Route::post('/contracts/generate', [AdminContractController::class, 'generate'])->name('contracts.generate');
        Route::post('/contracts/responsibilities', [AdminContractController::class, 'storeResponsibility'])->name('contracts.responsibilities.store');
        Route::delete('/contracts/responsibilities', [AdminContractController::class, 'bulkDestroyResponsibilities'])->name('contracts.responsibilities.bulk-destroy');
        Route::patch('/contracts/responsibilities/{responsibility}', [AdminContractController::class, 'updateResponsibility'])->name('contracts.responsibilities.update');
        Route::delete('/contracts/responsibilities/{responsibility}', [AdminContractController::class, 'destroyResponsibility'])->name('contracts.responsibilities.destroy');
        Route::get('/contracts/{contract}/regenerate', [AdminContractController::class, 'regenerate'])->name('contracts.regenerate');
        Route::get('/contracts/{contract}/download-word', [AdminContractController::class, 'regenerateWord'])->name('contracts.download-word');
        Route::delete('/contracts/{contract}', [AdminContractController::class, 'destroy'])->name('contracts.destroy');
        Route::post('/contracts/{contract}/approve', [AdminContractController::class, 'approve'])->name('contracts.approve');
        Route::post('/contracts/{contract}/reject', [AdminContractController::class, 'reject'])->name('contracts.reject');
        // KPI & Job Description
        Route::get('/kpi-jd', [AdminKpiJobController::class, 'index'])->name('kpi-jd.index');

        Route::get('/kpi-jd/kpi-template/{divisionId}/{subDivisionId?}/{positionId?}', [AdminKpiJobController::class, 'getKpiTemplate'])->name('kpi-jd.kpi-template');
        Route::get('/kpi-jd/kpi-template-employee/{employeeId}', [AdminKpiJobController::class, 'getKpiTemplateForEmployee'])->name('kpi-jd.kpi-template-employee');
        Route::post('/kpi-jd/kpi-template/save', [AdminKpiJobController::class, 'saveKpiTemplate'])->name('kpi-jd.kpi-template.save');
        Route::delete('/kpi-jd/kpi-template/{template}', [AdminKpiJobController::class, 'destroyKpiTemplate'])->name('kpi-jd.kpi-template.destroy');
        Route::get('/kpi-jd/kpi-list', [AdminKpiJobController::class, 'kpiList'])->name('kpi-jd.kpi-list');
        Route::get('/kpi-jd/jd-list', [AdminKpiJobController::class, 'jdList'])->name('kpi-jd.jd-list');
        Route::get('/kpi-jd/{record}/kpi', [AdminKpiJobController::class, 'showKpi'])->name('kpi-jd.kpi');
        Route::post('/kpi-jd/{record}/kpi/save', [AdminKpiJobController::class, 'saveKpi'])->name('kpi-jd.kpi.save');
        Route::post('/kpi-jd/{record}/approve-kpi', [AdminKpiJobController::class, 'approveKpi'])->name('kpi-jd.approve-kpi');
        Route::post('/kpi-jd/{record}/reject-kpi', [AdminKpiJobController::class, 'rejectKpi'])->name('kpi-jd.reject-kpi');
        Route::post('/kpi-jd/{record}/approve-jd', [AdminKpiJobController::class, 'approveJd'])->name('kpi-jd.approve-jd');
        Route::post('/kpi-jd/{record}/reject-jd', [AdminKpiJobController::class, 'rejectJd'])->name('kpi-jd.reject-jd');
        Route::delete('/kpi-jd/{record}', [AdminKpiJobController::class, 'destroy'])->name('kpi-jd.destroy');
        Route::get('/nanny-inquiries', [AdminNannyInquiryController::class, 'index'])->name('nanny-inquiries.index');
        Route::post('/nanny-inquiries/wedding-events', [AdminNannyInquiryController::class, 'storeWeddingEvent'])->name('nanny-inquiries.wedding-events.store');
        Route::delete('/nanny-inquiries/wedding-events/{weddingEvent}', [AdminNannyInquiryController::class, 'destroyWeddingEvent'])->name('nanny-inquiries.wedding-events.destroy');
        Route::get('/nanny-inquiries/export-wedding', [AdminNannyInquiryController::class, 'exportWedding'])->name('nanny-inquiries.export-wedding');
        Route::delete('/nanny-inquiries/{nannyInquiry}', [AdminNannyInquiryController::class, 'destroy'])->name('nanny-inquiries.destroy');
    });

    // Appointment access: booking checker can only view, cannot mutate
    Route::middleware(EnsureUserRole::class.':super_admin,admin,booking_checker')->group(function () {
        Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/leads/export', [AdminAppointmentController::class, 'exportLeadsCsv'])->name('appointments.leads.export');
    });
    Route::middleware(EnsureUserRole::class.':super_admin,admin')->group(function () {
        Route::patch('/appointments/{appointment}/approve', [AdminAppointmentController::class, 'approve'])->name('appointments.approve');
        Route::patch('/appointments/{appointment}/cancel', [AdminAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::delete('/appointments/{appointment}', [AdminAppointmentController::class, 'destroy'])->name('appointments.destroy');
    });

    // Super admin only
    Route::middleware(EnsureUserRole::class.':super_admin')->group(function () {
        // User management
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::get('/users/permissions', [AdminUserController::class, 'permissions'])->name('users.permissions');
        Route::post('/users/permissions', [AdminUserController::class, 'updatePermission'])->name('users.permissions.update');

        Route::get('/division-position', [AdminDivisionPositionController::class, 'index'])->name('division-position.index');
        Route::get('/divisions', [AdminDivisionPositionController::class, 'divisionsIndex'])->name('divisions.index');
        Route::post('/divisions', [AdminDivisionPositionController::class, 'storeDivision'])->name('divisions.store');
        Route::delete('/divisions/{division}', [AdminDivisionPositionController::class, 'destroyDivision'])->name('divisions.destroy');
        Route::post('/divisions/sub-divisions', [AdminDivisionPositionController::class, 'storeSubDivision'])->name('divisions.sub-divisions.store');
        Route::delete('/divisions/sub-divisions/{subDivision}', [AdminDivisionPositionController::class, 'destroySubDivision'])->name('divisions.sub-divisions.destroy');
        Route::get('/roles-responsibilities', [AdminDivisionPositionController::class, 'rolesResponsibilitiesIndex'])->name('roles-responsibilities.index');
        Route::post('/roles-responsibilities/positions', [AdminDivisionPositionController::class, 'storePosition'])->name('roles-responsibilities.positions.store');
        Route::delete('/roles-responsibilities/positions/{position}', [AdminDivisionPositionController::class, 'destroyPosition'])->name('roles-responsibilities.positions.destroy');
        Route::post('/roles-responsibilities/responsibilities', [AdminDivisionPositionController::class, 'storeResponsibility'])->name('roles-responsibilities.responsibilities.store');
        Route::delete('/roles-responsibilities/responsibilities/{responsibility}', [AdminDivisionPositionController::class, 'destroyResponsibility'])->name('roles-responsibilities.responsibilities.destroy');

        // Page Sections
        Route::get('/sections', [AdminController::class, 'index'])->name('sections.index');
        Route::get('/sections/{section}/edit', [AdminController::class, 'edit'])->name('sections.edit');
        Route::put('/sections/{section}', [AdminController::class, 'update'])->name('sections.update');

        // Jobs
        Route::delete('/jobs/bulk-delete', [JobController::class, 'bulkDestroy'])->name('jobs.bulk-destroy');
        Route::post('/jobs/ai-description', [JobController::class, 'generateDescription'])->name('jobs.ai-description');
        Route::resource('jobs', JobController::class);
            Route::patch('applications/{id}/status', [\App\Http\Controllers\JobController::class, 'updateApplicationStatus'])->name('admin.applications.status');
            Route::get('applications/{id}/resume', [\App\Http\Controllers\JobController::class, 'downloadResume'])->name('admin.applications.resume');

        Route::resource('career-categories', CareerCategoryController::class)
            ->parameters(['career-categories' => 'careerCategory'])
            ->except(['show']);
        Route::resource('service-areas', AdminServiceAreaController::class)
            ->parameters(['service-areas' => 'serviceArea'])
            ->except(['show']);
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/db/countries', [LocationController::class, 'countriesFromDatabase'])->name('db.countries');
            Route::get('/db/states', [LocationController::class, 'statesFromDatabase'])->name('db.states');
            Route::get('/countries', [LocationController::class, 'countries'])->name('countries');
            Route::get('/countries/{countryIso2}/states', [LocationController::class, 'states'])->name('states');
            Route::post('/sync', [LocationController::class, 'sync'])->name('sync');
        });

        // Pages
        Route::resource('pages', AdminPageController::class)->except(['show']);
        Route::get('/pages/{page}/builder', [AdminPageController::class, 'builder'])->name('pages.builder');
        Route::put('/pages/{page}/builder', [AdminPageController::class, 'saveBuilder'])->name('pages.builder.update');

        // Header & Footer
        Route::get('/header-footer', [AdminHeaderFooterController::class, 'edit'])->name('header-footer.edit');
        Route::put('/header-footer', [AdminHeaderFooterController::class, 'update'])->name('header-footer.update');
        Route::get('/page-wording', [AdminPageWordingController::class, 'edit'])->name('page-wording.edit');
        Route::put('/page-wording', [AdminPageWordingController::class, 'update'])->name('page-wording.update');
        Route::get('/role-page-wording', [AdminRolePageWordingController::class, 'index'])->name('role-page-wording.index');
        Route::get('/role-page-wording/{slug}', [AdminRolePageWordingController::class, 'edit'])->name('role-page-wording.edit');
        Route::put('/role-page-wording/{slug}', [AdminRolePageWordingController::class, 'update'])->name('role-page-wording.update');

        Route::post('/ai-agent/chat', [AiAgentController::class, 'chat'])->name('ai-agent.chat');
    });

});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

            // Serves photos/documents/videos WhatsApp contacts sent in, from
            // the standalone webhook's media-storage/ folder - shared by both
            // the Jobseeker and Client WhatsApp views.
            Route::get('/wa-media/{filename}', [AdminJobseekerController::class, 'serveWaMedia'])
                ->where('filename', '[A-Za-z0-9_.-]+')
                ->name('wa-media.show');

            // CRM Routes (Vendors, Businesses, Clients)
            Route::resource('vendors', AdminVendorController::class)->except(['create', 'edit']);
            // Must be registered BEFORE the businesses resource route below, otherwise
            // Laravel's /businesses/{business} wildcard would swallow "/businesses/export"
            // and "/businesses/bulk-destroy" and try to resolve them as a business ID.
            Route::get('/businesses/export', [AdminBusinessController::class, 'exportBusinesses'])
                ->name('businesses.export');
            Route::post('/businesses/bulk-destroy', [AdminBusinessController::class, 'bulkDestroy'])
                ->name('businesses.bulk-destroy');

            // Same reasoning as export/bulk-destroy above: must be registered BEFORE
            // the businesses resource route, otherwise /businesses/{business} would
            // swallow "/businesses/whatsapp-inbox" and try to resolve "whatsapp-inbox" as an ID.
            Route::get('/businesses/whatsapp-inbox', [AdminBusinessController::class, 'whatsappInbox'])->name('businesses.whatsapp-inbox');
            Route::get('/businesses/whatsapp-messages-by-phone', [AdminBusinessController::class, 'whatsappMessagesByPhone'])->name('businesses.whatsapp-messages-by-phone');
            Route::post('/businesses/whatsapp-create-profile', [AdminBusinessController::class, 'storeFromWhatsapp'])->name('businesses.whatsapp-create-profile');

            Route::resource('businesses', AdminBusinessController::class)->except(['create', 'edit']);

            // WhatsApp conversation viewer for a specific business (inbox route above, next to the other static /businesses/... routes)
            Route::get('/businesses/{business}/whatsapp-messages', [AdminBusinessController::class, 'whatsappMessages'])->name('businesses.whatsapp-messages');

            // Business Logs
            Route::post('/businesses/{business}/logs', [AdminBusinessLogController::class, 'store'])
                ->name('businesses.logs.store');
            Route::post('/businesses/{business}/logs/{log}/remove', [AdminBusinessLogController::class, 'destroy'])
                ->name('businesses.logs.destroy');

            // Business — assign/remove client
            Route::post('/businesses/{business}/clients/assign', [AdminBusinessController::class, 'assignClient'])
                ->name('businesses.clients.assign');
            Route::post('/businesses/{business}/clients/{client}/remove', [AdminBusinessController::class, 'removeClient'])
                ->name('businesses.clients.remove');

            // Business Services
            Route::post('/businesses/{business}/services', [AdminBusinessServiceController::class, 'store'])
                ->name('businesses.services.store');
            Route::put('/businesses/{business}/services/{service}', [AdminBusinessServiceController::class, 'update'])
                ->name('businesses.services.update');
            Route::post('/businesses/{business}/services/{service}/remove', [AdminBusinessServiceController::class, 'destroy'])
                ->name('businesses.services.destroy');
            Route::post('/businesses/{business}/services/generate-due', [AdminBusinessServiceController::class, 'generateDueInvoices'])
                ->name('businesses.services.generateDue');

            // Business Invoices
            Route::post('/businesses/{business}/invoices', [AdminBusinessInvoiceController::class, 'store'])
                ->name('businesses.invoices.store');
            Route::put('/businesses/{business}/invoices/{invoice}', [AdminBusinessInvoiceController::class, 'update'])
                ->name('businesses.invoices.update');
            Route::post('/businesses/{business}/invoices/{invoice}/send', [AdminBusinessInvoiceController::class, 'send'])
                ->name('businesses.invoices.send');
            Route::post('/businesses/{business}/invoices/{invoice}/remove', [AdminBusinessInvoiceController::class, 'destroy'])
                ->name('businesses.invoices.destroy');

            // Business Meetings
            Route::post('/businesses/{business}/meetings', [AdminBusinessMeetingController::class, 'store'])
                ->name('businesses.meetings.store');
            Route::post('/businesses/{business}/meetings/{meeting}/remove', [AdminBusinessMeetingController::class, 'destroy'])
                ->name('businesses.meetings.destroy');

            // Business Documents
            Route::post('/businesses/{business}/documents', [AdminBusinessDocumentController::class, 'store'])
                ->name('businesses.documents.store');
            Route::get('/businesses/{business}/documents/{document}/download', [AdminBusinessDocumentController::class, 'download'])
                ->name('businesses.documents.download');
            Route::post('/businesses/{business}/documents/{document}/remove', [AdminBusinessDocumentController::class, 'destroy'])
                ->name('businesses.documents.destroy');

            Route::post('/businesses/{business}/alerts', [AdminBusinessController::class, 'updateAlerts'])
                ->name('businesses.updateAlerts');

            // Business Management
            Route::post('/businesses/{business}/management', [AdminBusinessManagementController::class, 'store'])
                ->name('businesses.management.store');
            Route::post('/businesses/{business}/management/{management}/remove', [AdminBusinessManagementController::class, 'destroy'])
                ->name('businesses.management.destroy');
            // Must be registered BEFORE the clients resource route below, otherwise
            // Laravel's /clients/{client} wildcard would swallow "/clients/export"
            // and "/clients/bulk-destroy" and try to resolve them as a client ID.
            Route::get('/clients/export', [AdminClientController::class, 'exportClients'])
                ->name('clients.export');
            Route::post('/clients/bulk-destroy', [AdminClientController::class, 'bulkDestroy'])
                ->name('clients.bulk-destroy');

            // Same reasoning as export/bulk-destroy above: must be registered BEFORE
            // the clients resource route, otherwise /clients/{client} would swallow
            // "/clients/whatsapp-inbox" and try to resolve "whatsapp-inbox" as an ID.
            Route::get('/clients/whatsapp-inbox', [AdminClientController::class, 'whatsappInbox'])->name('clients.whatsapp-inbox');
            Route::get('/clients/whatsapp-messages-by-phone', [AdminClientController::class, 'whatsappMessagesByPhone'])->name('clients.whatsapp-messages-by-phone');

            // Group Chats archive (read-only) - part of Clients, not its own
            // top-level admin section, same reasoning as whatsapp-inbox above.
            Route::get('/clients/group-chats', [AdminClientController::class, 'groupChatsIndex'])->name('group-chats.index');
            Route::get('/clients/group-chats-messages', [AdminClientController::class, 'groupChatsMessages'])->name('group-chats.messages');

            Route::resource('clients', AdminClientController::class)->except(['create', 'edit']);

            // Client — Convert to Business
            Route::post('/clients/{client}/convert-to-business', [AdminClientController::class, 'convertToBusiness'])
                ->name('clients.convertToBusiness');

            // WhatsApp conversation viewer for a specific client (inbox route above, next to the other static /clients/... routes)
            Route::get('/clients/{client}/whatsapp-messages', [AdminClientController::class, 'whatsappMessages'])->name('clients.whatsapp-messages');

            // Jobseekers — manages jobseeker profiles and portal access
            // (mirrors Clients/Businesses, plus a future Jobseeker Portal login).
            Route::get('/jobseekers/export', [AdminJobseekerController::class, 'exportJobseekers'])
                ->name('jobseekers.export');
            Route::post('/jobseekers/bulk-destroy', [AdminJobseekerController::class, 'bulkDestroy'])
                ->name('jobseekers.bulk-destroy');
            Route::get('/jobseekers', [AdminJobseekerController::class, 'index'])->name('jobseekers.index');
            Route::post('/jobseekers', [JobseekerPortalController::class, 'store'])->name('jobseekers.store');

            // Must be registered BEFORE /jobseekers/{application} below, otherwise
            // Laravel's wildcard would swallow "/jobseekers/whatsapp-inbox" and try
            // to resolve "whatsapp-inbox" as a JobApplication ID (404).
            Route::get('/jobseekers/whatsapp-inbox', [AdminJobseekerController::class, 'whatsappInbox'])->name('jobseekers.whatsapp-inbox');
            Route::get('/jobseekers/whatsapp-messages-by-phone', [AdminJobseekerController::class, 'whatsappMessagesByPhone'])->name('jobseekers.whatsapp-messages-by-phone');

            // Jobseeker detail page — moved here from Applicants, since this
            // is the profile of the PERSON (regardless of which job they
            // applied for). Still linked to from Applicants Level 2 too.
            Route::get('/jobseekers/{application}', [AdminJobApplicationController::class, 'show'])->name('jobseekers.show');
            Route::patch('/jobseekers/{application}/status', [AdminJobApplicationController::class, 'updateStatus'])->name('jobseekers.status');
            Route::post('/jobseekers/{application}/alerts', [AdminJobApplicationController::class, 'updateAlerts'])->name('jobseekers.updateAlerts');
            Route::post('/jobseekers/{application}/personal-info', [AdminJobApplicationController::class, 'updatePersonalInfo'])->name('jobseekers.updatePersonalInfo');
            Route::get('/jobseekers/{application}/resume', [AdminJobApplicationController::class, 'resume'])->name('jobseekers.resume');
            Route::get('/jobseekers/{application}/documents/{type}', [AdminJobApplicationController::class, 'document'])->name('jobseekers.document');
            Route::get('/jobseekers/{application}/signature', [AdminJobApplicationController::class, 'signature'])->name('jobseekers.signature');
            Route::get('/jobseekers/{application}/photo', [AdminJobApplicationController::class, 'photo'])->name('jobseekers.photo');
            Route::post('/jobseekers/{application}/documents', [AdminJobApplicationController::class, 'uploadDocument'])->name('jobseekers.documents.upload');
            Route::patch('/jobseekers/{application}/checks/{checkType}', [AdminJobApplicationController::class, 'updateCheckStatus'])->name('jobseekers.checks.update');
            Route::post('/jobseekers/{application}/portal/activate', [AdminJobApplicationController::class, 'activatePortalAccount'])->name('jobseekers.portal.activate');
            Route::post('/jobseeker-portal-accounts/{portalAccount}/deactivate', [AdminJobApplicationController::class, 'deactivatePortalAccount'])->name('jobseeker-portal-accounts.deactivate');
            Route::post('/jobseeker-portal-accounts/{portalAccount}/reactivate', [AdminJobApplicationController::class, 'reactivatePortalAccount'])->name('jobseeker-portal-accounts.reactivate');

            // WhatsApp conversation viewer for a specific jobseeker (inbox route moved above, next to the other static /jobseekers/... routes)
            Route::get('/jobseekers/{application}/whatsapp-messages', [AdminJobseekerController::class, 'whatsappMessages'])->name('jobseekers.whatsapp-messages');

            // Client Documents (upload/download/delete files attached to a Client record)
            Route::post('/clients/{client}/documents', [AdminClientDocumentController::class, 'store'])
                ->name('clients.documents.store');
            Route::get('/clients/{client}/documents/{document}/download', [AdminClientDocumentController::class, 'download'])
                ->name('clients.documents.download');
            Route::post('/clients/{client}/documents/{document}/remove', [AdminClientDocumentController::class, 'destroy'])
                ->name('clients.documents.destroy');

            // Client Invoices (recurring billing records — a client can have many)
            Route::post('/clients/{client}/alerts', [AdminClientController::class, 'updateAlerts'])
                ->name('clients.updateAlerts');
            Route::post('/clients/{client}/invoices', [AdminClientInvoiceController::class, 'store'])
                ->name('clients.invoices.store');
            Route::patch('/clients/{client}/invoices/{invoice}', [AdminClientInvoiceController::class, 'update'])
                ->name('clients.invoices.update');
            Route::post('/clients/{client}/invoices/{invoice}/send', [AdminClientInvoiceController::class, 'send'])
                ->name('clients.invoices.send');
            Route::post('/clients/{client}/invoices/{invoice}/remove', [AdminClientInvoiceController::class, 'destroy'])
                ->name('clients.invoices.destroy');
            Route::post('/clients/{client}/invoices/generate-due', [AdminClientServiceController::class, 'generateDueInvoices'])
                ->name('clients.invoices.generateDue');
            // Client Services (a client can request more than one service type)
            Route::post('/clients/{client}/services', [AdminClientServiceController::class, 'store'])
                ->name('clients.services.store');
            Route::patch('/clients/{client}/services/{service}', [AdminClientServiceController::class, 'update'])
                ->name('clients.services.update');
            Route::post('/clients/{client}/services/{service}/remove', [AdminClientServiceController::class, 'destroy'])
                ->name('clients.services.destroy');

            // Client Logs (past interactions) and Meetings (scheduled)
            Route::post('/clients/{client}/logs', [AdminClientLogController::class, 'store'])
                ->name('clients.logs.store');
            Route::post('/clients/{client}/logs/{log}/remove', [AdminClientLogController::class, 'destroy'])
                ->name('clients.logs.destroy');
            Route::post('/clients/{client}/meetings', [AdminClientMeetingController::class, 'store'])
                ->name('clients.meetings.store');
            Route::post('/clients/{client}/meetings/{meeting}/remove', [AdminClientMeetingController::class, 'destroy'])
                ->name('clients.meetings.destroy');
});
