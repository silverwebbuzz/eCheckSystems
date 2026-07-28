<?php
use App\Http\Controllers\BulkEmailController;
use App\Http\Controllers\HowItWorksController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\CheckBlockedIP;
use App\Http\Middleware\EnsurePaymentIsComplete;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\laravel_example\UserManagement;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\Crm;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\layouts\CollapsedMenu;
use App\Http\Controllers\layouts\ContentNavbar;
use App\Http\Controllers\layouts\ContentNavSidebar;
use App\Http\Controllers\layouts\NavbarFull;
use App\Http\Controllers\layouts\NavbarFullSidebar;
use App\Http\Controllers\layouts\Horizontal;
use App\Http\Controllers\layouts\Vertical;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\front_pages\Landing;
use App\Http\Controllers\front_pages\Pricing;
use App\Http\Controllers\front_pages\Payment;
use App\Http\Controllers\front_pages\Checkout;
use App\Http\Controllers\front_pages\HelpCenter;
use App\Http\Controllers\front_pages\HelpCenterArticle;
use App\Http\Controllers\apps\Email;
use App\Http\Controllers\apps\Chat;
use App\Http\Controllers\apps\Calendar;
use App\Http\Controllers\apps\Kanban;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\EcommerceProductList;
use App\Http\Controllers\apps\EcommerceProductAdd;
use App\Http\Controllers\apps\EcommerceProductCategory;
use App\Http\Controllers\apps\EcommerceOrderList;
use App\Http\Controllers\apps\EcommerceOrderDetails;
use App\Http\Controllers\apps\EcommerceCustomerAll;
use App\Http\Controllers\apps\EcommerceCustomerDetailsOverview;
use App\Http\Controllers\apps\EcommerceCustomerDetailsSecurity;
use App\Http\Controllers\apps\EcommerceCustomerDetailsBilling;
use App\Http\Controllers\apps\EcommerceCustomerDetailsNotifications;
use App\Http\Controllers\apps\EcommerceManageReviews;
use App\Http\Controllers\apps\EcommerceReferrals;
use App\Http\Controllers\apps\EcommerceSettingsDetails;
use App\Http\Controllers\apps\EcommerceSettingsPayments;
use App\Http\Controllers\apps\EcommerceSettingsCheckout;
use App\Http\Controllers\apps\EcommerceSettingsShipping;
use App\Http\Controllers\apps\EcommerceSettingsLocations;
use App\Http\Controllers\apps\EcommerceSettingsNotifications;
use App\Http\Controllers\apps\AcademyDashboard;
use App\Http\Controllers\apps\AcademyCourse;
use App\Http\Controllers\apps\AcademyCourseDetails;
use App\Http\Controllers\apps\LogisticsDashboard;
use App\Http\Controllers\apps\LogisticsFleet;
use App\Http\Controllers\apps\InvoiceList;
use App\Http\Controllers\apps\InvoicePreview;
use App\Http\Controllers\apps\InvoicePrint;
use App\Http\Controllers\apps\InvoiceEdit;
use App\Http\Controllers\apps\InvoiceAdd;
use App\Http\Controllers\apps\UserList;
use App\Http\Controllers\apps\UserViewAccount;
use App\Http\Controllers\apps\UserViewSecurity;
use App\Http\Controllers\apps\UserViewBilling;
use App\Http\Controllers\apps\UserViewNotifications;
use App\Http\Controllers\apps\UserViewConnections;
use App\Http\Controllers\apps\AccessRoles;
use App\Http\Controllers\apps\AccessPermission;
use App\Http\Controllers\pages\UserProfile;
use App\Http\Controllers\pages\UserTeams;
use App\Http\Controllers\pages\UserProjects;
use App\Http\Controllers\pages\UserConnections;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsSecurity;
use App\Http\Controllers\pages\AccountSettingsBilling;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\Faq;
use App\Http\Controllers\pages\Pricing as PagesPricing;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\pages\MiscComingSoon;
use App\Http\Controllers\pages\MiscNotAuthorized;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\LoginCover;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\RegisterCover;
use App\Http\Controllers\authentications\RegisterMultiSteps;
use App\Http\Controllers\authentications\VerifyEmailBasic;
use App\Http\Controllers\authentications\VerifyEmailCover;
use App\Http\Controllers\authentications\ResetPasswordBasic;
use App\Http\Controllers\authentications\ResetPasswordCover;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\authentications\ForgotPasswordCover;
use App\Http\Controllers\authentications\TwoStepsBasic;
use App\Http\Controllers\authentications\TwoStepsCover;
use App\Http\Controllers\wizard_example\Checkout as WizardCheckout;
use App\Http\Controllers\wizard_example\PropertyListing;
use App\Http\Controllers\wizard_example\CreateDeal;
use App\Http\Controllers\modal\ModalExample;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\cards\CardAdvance;
use App\Http\Controllers\cards\CardStatistics;
use App\Http\Controllers\cards\CardAnalytics;
use App\Http\Controllers\cards\CardGamifications;
use App\Http\Controllers\cards\CardActions;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\Avatar;
use App\Http\Controllers\extended_ui\BlockUI;
use App\Http\Controllers\extended_ui\DragAndDrop;
use App\Http\Controllers\extended_ui\MediaPlayer;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\StarRatings;
use App\Http\Controllers\extended_ui\SweetAlert;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\extended_ui\TimelineBasic;
use App\Http\Controllers\extended_ui\TimelineFullscreen;
use App\Http\Controllers\extended_ui\Tour;
use App\Http\Controllers\extended_ui\Treeview;
use App\Http\Controllers\extended_ui\Misc;
use App\Http\Controllers\icons\Tabler;
use App\Http\Controllers\icons\FontAwesome;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_elements\CustomOptions;
use App\Http\Controllers\form_elements\Editors;
use App\Http\Controllers\form_elements\FileUpload;
use App\Http\Controllers\form_elements\Picker;
use App\Http\Controllers\form_elements\Selects;
use App\Http\Controllers\form_elements\Sliders;
use App\Http\Controllers\form_elements\Switches;
use App\Http\Controllers\form_elements\Extras;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\form_layouts\StickyActions;
use App\Http\Controllers\form_wizard\Numbered as FormWizardNumbered;
use App\Http\Controllers\form_wizard\Icons as FormWizardIcons;
use App\Http\Controllers\form_validation\Validation;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\tables\DatatableBasic;
use App\Http\Controllers\tables\DatatableAdvanced;
use App\Http\Controllers\tables\DatatableExtensions;
use App\Http\Controllers\charts\ApexCharts;
use App\Http\Controllers\charts\ChartJs;
use App\Http\Controllers\maps\Leaflet;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PayorsController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\BillingAndPlanController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SignController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\QuickBooksController;

// Stripe routes

Route::middleware([CheckBlockedIP::class])->group(function () {

    Route::get('/stripe/subscriptions', [StripeController::class, 'list'])->name('stripe.subscription.list');

    Route::get('/stripe/webhooks', [StripeController::class, 'webhookList'])->name('stripe.webhook.list');

    Route::get('/stripe/webhooks/all', [StripeController::class, 'getWebhooks'])->name('stripe.webhook.get');

    Route::get('/stripe/webhooks/add/{endpoint_id}', [StripeController::class, 'addWebhook'])->name('stripe.webhook.add');

    Route::get('/stripe/subscriptions/all', [StripeController::class, 'getSubscriptions'])->name('stripe.subscription.get');

    Route::get('/stripe/subscriptions/{subscription_id}', [StripeController::class, 'viewSubscription'])->name('stripe.subscription.view');

    // End Stripe routes

    //Quickbook routes

    Route::get('/quickbooks/connect', [QuickBooksController::class, 'connect'])->name('qbo.connect');
    Route::get('/quickbooks/callback', [QuickBooksController::class, 'callback'])->name('qbo.callback');


    // End Quickbook routes

    Route::get('/admin/login', [AdminAuthController::class, 'adminLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login-action');
    Route::get('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/profile', [AdminDashboardController::class, 'profile'])->name('admin.profile');
        Route::post('/admin/profile', [AdminDashboardController::class, 'updateProfile'])->name('admin.update_profile');
        Route::post('/admin/change-password', [AdminDashboardController::class, 'changePassword'])->name('admin.change-password');
        Route::get('/admin/package', [PackageController::class, 'index'])->name('admin.package');
        Route::get('/admin/add', [PackageController::class, 'create'])->name('admin.package.add');
        Route::post('/admin/add', [PackageController::class, 'store'])->name('admin.package.store');
        Route::get('/admin/edit/{id}', [PackageController::class, 'edit'])->name('admin.package.edit');
        Route::post('/admin/edit/{id}', [PackageController::class, 'update'])->name('admin.package.update');
        Route::delete('/admin/delete/{id}', [PackageController::class, 'delete'])->name('admin.package.delete');
        Route::get('/admin/users', [AdminDashboardController::class, 'users'])->name('admin.users');
        Route::get('/admin/users/edit/{id}', [AdminDashboardController::class, 'user_edit'])->name('admin.user.edit');
        Route::get('/admin/users/view/{id}', [AdminDashboardController::class, 'user_view'])->name('admin.user.view');
        Route::post('admin/user/profile', [AdminDashboardController::class, 'updateUserProfile'])->name('admin.user.update_profile');
        Route::post('admin/user/change-password', [AdminDashboardController::class, 'changeUserPassword'])->name('admin.user.change-password');
        Route::delete('/admin/users/delete/{id}', [AdminDashboardController::class, 'user_delete'])->name('admin.user.delete');
        Route::post('admin/user/plan', [AdminDashboardController::class, 'change_plan'])->name('admin.user.plan');
        Route::get('/admin/users/profile/edit/{id}', [AdminDashboardController::class, 'user_profile_edit'])->name('admin.user_profile_edit');
        Route::get('/admin/users/plan/upgrade/{id}', [AdminDashboardController::class, 'upgragde_plan'])->name('admin.user_upgragde_plan');
        Route::get('/company/{id}', [AdminDashboardController::class, 'company'])->name('admin.user.company');
        Route::get('/invoice/{id}', [AdminDashboardController::class, 'invoice'])->name('admin.user.invoice');
        Route::get('/client/{id}', [AdminDashboardController::class, 'client'])->name('admin.user.client');
        Route::get('/vendor/{id}', [AdminDashboardController::class, 'vendor'])->name('admin.user.vendor');
        Route::get('change-plan/{id}/{plan}', [AdminDashboardController::class, 'change_plan'])->name('admin.user.select-package');
        Route::get('/admin/email-template', [EmailTemplateController::class, 'index'])->name('admin.email-template');
        Route::get('/admin/email-template/edit/{id}', [EmailTemplateController::class, 'edit'])->name('admin.email-template-edit');
        Route::post('/admin/email-template/update/{id}', [EmailTemplateController::class, 'update'])->name('admin.email-template-update');
        Route::post('/admin/users/change-status', [AdminDashboardController::class, 'changeStatus'])->name('changeStatus');
        Route::get('/admin/setting', [SettingController::class, 'index'])->name('admin.setting');
        Route::post('/update-setting', [SettingController::class, 'updateSettings'])->name('admin.update_setting');

        Route::get('/admin/suggestions', [SuggestionController::class, 'index'])->name('admin.suggestions.index');
        Route::get('/admin/suggestions/list', [SuggestionController::class, 'list'])->name('admin.suggestions.list');
        Route::get('/admin/suggestions/view/{id}', [SuggestionController::class, 'view'])->name('admin.suggestions.view');

        Route::get('/admin/how-it-works', [HowItWorksController::class, 'index'])->name('admin.how_it_works');
        Route::get('/admin/how-it-works/list', [HowItWorksController::class, 'list'])->name('admin.how_it_works.list');
        Route::get('/admin/how-it-works/edit/{howItWork}', [HowItWorksController::class, 'edit'])->name('admin.how_it_works.edit');
        Route::post('/admin/how-it-works/update/{howItWork}', [HowItWorksController::class, 'update'])->name('admin.how_it_works.update');

        Route::get('/admin/transactions', [TransactionController::class, 'index'])->name('admin.transactions');
        Route::get('/admin/transactions/list', [TransactionController::class, 'list'])->name('admin.transactions.list');

        Route::get('/admin/bulk-email', [BulkEmailController::class, 'create'])->name('admin.bulk-email.create');
        Route::post('/admin/bulk-email', [BulkEmailController::class, 'send'])->name('admin.bulk-email.send');
    });

    Route::get('verify-email/{id}/{hash}', [UserAuthController::class, 'verify_email'])->name('user.verify_email');
    Route::get('resend-verify-email/{hash}', [UserAuthController::class, 'resend_verify_email'])->name('user.resend_verify_email');
    Route::get('/login', [UserAuthController::class, 'login'])->name('user.login');
    Route::post('/login', [UserAuthController::class, 'login_action'])->name('user.login-action');
    Route::get('/register', [UserAuthController::class, 'register'])->name('user.register');
    Route::post('/register', [UserAuthController::class, 'store'])->name('register.store');
    Route::get('/package', [UserAuthController::class, 'package'])->name('user.package');
    // Route::get('/select-package/{id}/{plan}', [UserAuthController::class, 'select_package'])->name('user-select-package');
    Route::get('/select-free-package/{id}/{plan}', [UserAuthController::class, 'select_free_package'])->name('user-select-free-package');
    Route::get('/select-package/{id}/{plan}', [SubscriptionController::class, 'checkout'])->name('user-select-package');
    Route::get('/user/logout', [UserAuthController::class, 'logout'])->name('user.logout');
    Route::get('/email', [UserAuthController::class, 'email'])->name('user.email');
    Route::get('forgot-password', [UserAuthController::class, 'showForgotPasswordForm'])->name('user.forgot-password');
    Route::post('forgot-password', [UserAuthController::class, 'sendResetLink']);
    Route::get('reset-password/{token}', [UserAuthController::class, 'showResetForm'])->name('user.showResetForm');
    Route::post('reset-password', [UserAuthController::class, 'resetPassword'])->name('user.resetPassword');
    Route::middleware([UserMiddleware::class])->group(function () {

        Route::middleware([EnsurePaymentIsComplete::class])->group(function () {

            Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
            Route::get('/user/profile', [UserDashboardController::class, 'profile'])->name('user.profile');
            Route::post('/user/profile', [UserDashboardController::class, 'updateProfile'])->name('user.update_profile');
            Route::post('/user/change-password', [UserDashboardController::class, 'changePassword'])->name('user.change-password');
            Route::get('/user/company', [CompanyController::class, 'index'])->name('user.company');
            Route::get('/user/company/add', [CompanyController::class, 'create'])->name('user.company.add');
            Route::post('/user/company/add', [CompanyController::class, 'store'])->name('user.company.store');
            Route::get('/user/company/edit/{id}', [CompanyController::class, 'edit'])->name('user.company.edit');
            Route::post('/user/company/edit/{id}', [CompanyController::class, 'update'])->name('user.company.update');
            Route::delete('/user/company/delete/{id}', [CompanyController::class, 'delete'])->name('user.company.delete');
            Route::get('/user/client', [PayorsController::class, 'client_index'])->name('user.Payee');
            Route::get('/user/vendor', [PayorsController::class, 'vendor_index'])->name('user.Payors');
            //
            Route::get('/user/payors/Payors/add', [PayorsController::class, 'payor_create'])->name('user.payors.add');
            Route::post('/user/payors/Payors/add', [PayorsController::class, 'payor_store'])->name('user.payors.store');
            Route::get('/user/payors/Payors/edit/{id}', [PayorsController::class, 'payor_edit'])->name('user.payors.edit');
            Route::post('/user/payors/Payors/edit/{id}', [PayorsController::class, 'payor_update'])->name('user.payors.update');
            Route::delete('/user/payors/Payors/delete/{id}', [PayorsController::class, 'payor_delete'])->name('user.payors.delete');
            Route::get('/user/payee/Payee/add', [PayorsController::class, 'payee_create'])->name('user.payee.add');
            Route::post('/user/payee/Payee/add', [PayorsController::class, 'payee_store'])->name('user.payee.store');
            Route::get('/user/payors/Payee/edit/{id}', [PayorsController::class, 'payee_edit'])->name('user.payee.edit');
            Route::post('/user/payors/Payee/edit/{id}', [PayorsController::class, 'payee_update'])->name('user.payee.update');
            Route::delete('/user/payors/Payee/delete/{id}', [PayorsController::class, 'payee_delete'])->name('user.payee.delete');
            Route::get('/check/process-payment', [CheckController::class, 'process_payment'])->name('check.process_payment');
            Route::get('/check/process-payment/generate', [CheckController::class, 'process_payment_check'])->name('check.process_payment.check');
            Route::post('/check/process-payment/generate', [CheckController::class, 'process_payment_check_generate'])->name('check.process_payment_check_generate');
            Route::get('/check/process-payment/edit/{id}', [CheckController::class, 'process_payment_check_edit'])->name('check.process_payment_check_edit');
            Route::get('/check/send-payment', [CheckController::class, 'send_payment'])->name('check.send_payment');
            Route::get('/check/send-payment/generate', [CheckController::class, 'send_payment_check'])->name('check.send_payment_check');
            Route::post('/check/send-payment/generate', [CheckController::class, 'send_payment_check_generate'])->name('check.send_payment_check_generate');
            Route::get('/check/send-payment/edit/{id}', [CheckController::class, 'process_send_check_edit'])->name('check.process_send_check_edit');
            Route::get('check_pdf', [CheckController::class, 'check'])->name('check_pdf');
            Route::post('change-status', [CheckController::class, 'change_status'])->name('change_status');
            Route::get('check-history', [CheckController::class, 'history'])->name('check_history');
            Route::get('billing-and-plan', [BillingAndPlanController::class, 'index'])->name('billing_and_plan');
            Route::post('add-payee', [PayorsController::class, 'add_payee'])->name('user.add-payee');
            Route::post('add-payor', [PayorsController::class, 'add_payor'])->name('user.add-payor');
            Route::post('check-payor-email', [PayorsController::class, 'check_payor_email'])->name('user.check-payor-email');
            Route::post('amount-word', [CheckController::class, 'amount_word'])->name('amount_word');
            Route::get('get-payee/{id}', [CheckController::class, 'get_payee'])->name('get_payee');
            Route::get('get-payor/{id}', [CheckController::class, 'get_payor'])->name('get_payor');
            Route::get('check-generate/{id}', [CheckController::class, 'check_generate'])->name('check_generate');
            Route::get('send-payment-check-generate/{id}', [CheckController::class, 'send_check_generate'])->name('send_check_generate');
            Route::get('setting', [CheckController::class, 'get_web_forms'])->name('get_web_forms');
            Route::get('webform/new', [CheckController::class, 'new_web_form'])->name('new_web_form');
            Route::post('webform/new', [CheckController::class, 'new_web_form_store'])->name('store_web_form');
            Route::post('web-form-slug', [CheckController::class, 'web_form_slug'])->name('web_form_slug');
            Route::get('users/plan/upgrade/{id}', [BillingAndPlanController::class, 'upgragde_plan'])->name('user_upgragde_plan');
            Route::get('user/change-plan/{id}/{plan}', [BillingAndPlanController::class, 'change_plan'])->name('user.select-package');
            Route::get('user/cancel-plan', [BillingAndPlanController::class, 'cancel_plan'])->name('user_cancel_plan');
            Route::delete('/web_form/delete/{id}', [CheckController::class, 'web_form_delete'])->name('web_form.delete');
            // Route::post('webform/new', [CheckController::class, 'new_web_form_store'])->name('store_web_form');
            Route::post('/update-records-per-page', [CheckController::class, 'updateRecordsPerPage'])->name('update_records_per_page');
            Route::post('bulk-generate', [CheckController::class, 'bulk_generate'])->name('bulk_generate');
            Route::get('/web_form/edit/{id}', [CheckController::class, 'web_form_edit'])->name('web_form.edit');
            Route::post('bulk-download', [CheckController::class, 'bulk_download'])->name('bulk_download');
            Route::get('add-sign', [SignController::class, 'create'])->name('add_sign');
            Route::get('edit-sign/{id}', [SignController::class, 'edit'])->name('edit_sign');
            Route::post('store-sign', [SignController::class, 'addupdate'])->name('store_sign');
            Route::get('get-sign', [SignController::class, 'get'])->name('get_sign');
            Route::delete('delete-sign/{id}', [SignController::class, 'delete'])->name('delete_sign');
            Route::get('get-signature/{id}', [CheckController::class, 'get_signature'])->name('get_signature');
            Route::get('send-check-email/{id}', [CheckController::class, 'send_check_email'])->name('send_check_email');
            Route::post('stripe-subscribe', [SubscriptionController::class, 'subscribe'])->name('stripe.subscribe');
            Route::post('add-card', [SubscriptionController::class, 'add_card'])->name('stripe.add_card');
            Route::get('delete-card/{id}', [SubscriptionController::class, 'delete_card'])->name('stripe.delete_card');
            Route::get('set-default/{id}', [SubscriptionController::class, 'set_default'])->name('stripe.set_default');

            Route::get('view-pdf/{id}', [CheckController::class, 'view_pdf'])->name('view.pdf');
            Route::get('download-pdf/{id}', [CheckController::class, 'download_pdf'])->name('download.pdf');
            Route::get('user/suggestion/add', [SuggestionController::class, 'add'])->name('user.suggestion.add');
            Route::post('user/suggestion/store', [SuggestionController::class, 'store'])->name('user.suggestion.store');

            Route::get('check/exists', [CheckController::class, 'isExists'])->name('check.check_number_exists');

            Route::post('/save-grid', [CheckController::class, 'saveGrid'])->name('save_grid');
            Route::get('/get-grids', [CheckController::class, 'getGrids'])->name('get_grids');
            // Route::get('/get-default-grids', [CheckController::class, 'getDefaultGrids'])->name('get_default_grids');

            Route::get('new-signature', [CheckController::class, 'new_signature'])->name('new_signature');

            Route::get('/quickbooks/companies', [QuickBooksController::class, 'getCompanies'])->name('qbo.getCompanies');
            Route::get('/quickbooks/companies/connect/{id}', [QuickBooksController::class, 'connectCompany'])->name('qbo.connect.company');
            Route::get('//quickbooks/sync/{qbo_company_id}', [QuickBooksController::class, 'sync'])->name('qbo.sync');

            Route::get('/check/delete/{id}', [CheckController::class, 'delete'])->name('check.delete');
        });

        Route::get('/invoice', [BillingAndPlanController::class, 'invoice'])->name('user_invoice');
        Route::get('pending', [UserAuthController::class, 'pending_sub'])->name('pending_sub');
    });

    Route::get('web-form/{slug}', [CheckController::class, 'web_form'])->name('web_form');
    Route::post('store_web_form_data', [CheckController::class, 'store_web_form_data'])->name('store_web_form_data');
    Route::get('thank-you', [CheckController::class, 'thankyou'])->name('thankyou');
    Route::get('expired', [UserAuthController::class, 'expired_sub'])->name('expired_sub');
    Route::get('test', [TestController::class, 'test'])->name('test');
    Route::get('card', [TestController::class, 'card'])->name('card');
    Route::get('checkout-test', [TestController::class, 'checkout'])->name('checkout-test');
    Route::get('subscription-update', [TestController::class, 'subscription_update'])->name('subscription_update');
    Route::get('/smtp-test', [TestController::class, 'index']);
    Route::post('/smtp-test', [TestController::class, 'send'])->name('smtp.test.send');
    Route::get('/subscribe/success', [SubscriptionController::class, 'success'])->name('stripe.success');
    Route::get('/subscribe/cancel', [SubscriptionController::class, 'cancel'])->name('stripe.cancel');

    Route::get('check-smtp', [TestController::class, 'smtp_checker_view'])->name('check_smtp');
    Route::post('smtp-checker', [TestController::class, 'smtp_checker'])->name('smtp_checker');
    // Main Page Route
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::get('/dashboard/analytics', [Analytics::class, 'index'])->name('dashboard-analytics');
    Route::get('/dashboard/crm', [Crm::class, 'index'])->name('dashboard-crm');
    // locale
    Route::get('/lang/{locale}', [LanguageController::class, 'swap']);

    // layout
    Route::get('/layouts/collapsed-menu', [CollapsedMenu::class, 'index'])->name('layouts-collapsed-menu');
    Route::get('/layouts/content-navbar', [ContentNavbar::class, 'index'])->name('layouts-content-navbar');
    Route::get('/layouts/content-nav-sidebar', [ContentNavSidebar::class, 'index'])->name('layouts-content-nav-sidebar');
    Route::get('/layouts/navbar-full', [NavbarFull::class, 'index'])->name('layouts-navbar-full');
    Route::get('/layouts/navbar-full-sidebar', [NavbarFullSidebar::class, 'index'])->name('layouts-navbar-full-sidebar');
    Route::get('/layouts/horizontal', [Horizontal::class, 'index'])->name('dashboard-analytics');
    Route::get('/layouts/vertical', [Vertical::class, 'index'])->name('dashboard-analytics');
    Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
    Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
    Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
    Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
    Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

    // Front Pages
    Route::get('/front-pages/landing', [Landing::class, 'index'])->name('front-pages-landing');
    Route::get('/front-pages/pricing', [Pricing::class, 'index'])->name('front-pages-pricing');
    Route::get('/front-pages/payment', [Payment::class, 'index'])->name('front-pages-payment');
    Route::get('/front-pages/checkout', [Checkout::class, 'index'])->name('front-pages-checkout');
    Route::get('/front-pages/help-center', [HelpCenter::class, 'index'])->name('front-pages-help-center');
    Route::get('/front-pages/help-center-article', [HelpCenterArticle::class, 'index'])->name('front-pages-help-center-article');

    // apps
    Route::get('/app/email', [Email::class, 'index'])->name('app-email');
    Route::get('/app/chat', [Chat::class, 'index'])->name('app-chat');
    Route::get('/app/calendar', [Calendar::class, 'index'])->name('app-calendar');
    Route::get('/app/kanban', [Kanban::class, 'index'])->name('app-kanban');
    Route::get('/app/ecommerce/dashboard', [EcommerceDashboard::class, 'index'])->name('app-ecommerce-dashboard');
    Route::get('/app/ecommerce/product/list', [EcommerceProductList::class, 'index'])->name('app-ecommerce-product-list');
    Route::get('/app/ecommerce/product/add', [EcommerceProductAdd::class, 'index'])->name('app-ecommerce-product-add');
    Route::get('/app/ecommerce/product/category', [EcommerceProductCategory::class, 'index'])->name('app-ecommerce-product-category');
    Route::get('/app/ecommerce/order/list', [EcommerceOrderList::class, 'index'])->name('app-ecommerce-order-list');
    Route::get('/app/ecommerce/order/details', [EcommerceOrderDetails::class, 'index'])->name('app-ecommerce-order-details');
    Route::get('/app/ecommerce/customer/all', [EcommerceCustomerAll::class, 'index'])->name('app-ecommerce-customer-all');
    Route::get('/app/ecommerce/customer/details/overview', [EcommerceCustomerDetailsOverview::class, 'index'])->name('app-ecommerce-customer-details-overview');
    Route::get('/app/ecommerce/customer/details/security', [EcommerceCustomerDetailsSecurity::class, 'index'])->name('app-ecommerce-customer-details-security');
    Route::get('/app/ecommerce/customer/details/billing', [EcommerceCustomerDetailsBilling::class, 'index'])->name('app-ecommerce-customer-details-billing');
    Route::get('/app/ecommerce/customer/details/notifications', [EcommerceCustomerDetailsNotifications::class, 'index'])->name('app-ecommerce-customer-details-notifications');
    Route::get('/app/ecommerce/manage/reviews', [EcommerceManageReviews::class, 'index'])->name('app-ecommerce-manage-reviews');
    Route::get('/app/ecommerce/referrals', [EcommerceReferrals::class, 'index'])->name('app-ecommerce-referrals');
    Route::get('/app/ecommerce/settings/details', [EcommerceSettingsDetails::class, 'index'])->name('app-ecommerce-settings-details');
    Route::get('/app/ecommerce/settings/payments', [EcommerceSettingsPayments::class, 'index'])->name('app-ecommerce-settings-payments');
    Route::get('/app/ecommerce/settings/checkout', [EcommerceSettingsCheckout::class, 'index'])->name('app-ecommerce-settings-checkout');
    Route::get('/app/ecommerce/settings/shipping', [EcommerceSettingsShipping::class, 'index'])->name('app-ecommerce-settings-shipping');
    Route::get('/app/ecommerce/settings/locations', [EcommerceSettingsLocations::class, 'index'])->name('app-ecommerce-settings-locations');
    Route::get('/app/ecommerce/settings/notifications', [EcommerceSettingsNotifications::class, 'index'])->name('app-ecommerce-settings-notifications');
    Route::get('/app/academy/dashboard', [AcademyDashboard::class, 'index'])->name('app-academy-dashboard');
    Route::get('/app/academy/course', [AcademyCourse::class, 'index'])->name('app-academy-course');
    Route::get('/app/academy/course-details', [AcademyCourseDetails::class, 'index'])->name('app-academy-course-details');
    Route::get('/app/logistics/dashboard', [LogisticsDashboard::class, 'index'])->name('app-logistics-dashboard');
    Route::get('/app/logistics/fleet', [LogisticsFleet::class, 'index'])->name('app-logistics-fleet');
    Route::get('/app/invoice/list', [InvoiceList::class, 'index'])->name('app-invoice-list');
    Route::get('/app/invoice/preview', [InvoicePreview::class, 'index'])->name('app-invoice-preview');
    Route::get('/app/invoice/print', [InvoicePrint::class, 'index'])->name('app-invoice-print');
    Route::get('/app/invoice/edit', [InvoiceEdit::class, 'index'])->name('app-invoice-edit');
    Route::get('/app/invoice/add', [InvoiceAdd::class, 'index'])->name('app-invoice-add');
    Route::get('/app/user/list', [UserList::class, 'index'])->name('app-user-list');
    Route::get('/app/user/view/account', [UserViewAccount::class, 'index'])->name('app-user-view-account');
    Route::get('/app/user/view/security', [UserViewSecurity::class, 'index'])->name('app-user-view-security');
    Route::get('/app/user/view/billing', [UserViewBilling::class, 'index'])->name('app-user-view-billing');
    Route::get('/app/user/view/notifications', [UserViewNotifications::class, 'index'])->name('app-user-view-notifications');
    Route::get('/app/user/view/connections', [UserViewConnections::class, 'index'])->name('app-user-view-connections');
    Route::get('/app/access-roles', [AccessRoles::class, 'index'])->name('app-access-roles');
    Route::get('/app/access-permission', [AccessPermission::class, 'index'])->name('app-access-permission');

    // pages
    Route::get('/pages/profile-user', [UserProfile::class, 'index'])->name('pages-profile-user');
    Route::get('/pages/profile-teams', [UserTeams::class, 'index'])->name('pages-profile-teams');
    Route::get('/pages/profile-projects', [UserProjects::class, 'index'])->name('pages-profile-projects');
    Route::get('/pages/profile-connections', [UserConnections::class, 'index'])->name('pages-profile-connections');
    Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
    Route::get('/pages/account-settings-security', [AccountSettingsSecurity::class, 'index'])->name('pages-account-settings-security');
    Route::get('/pages/account-settings-billing', [AccountSettingsBilling::class, 'index'])->name('pages-account-settings-billing');
    Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
    Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
    Route::get('/pages/faq', [Faq::class, 'index'])->name('pages-faq');
    Route::get('/pages/pricing', [PagesPricing::class, 'index'])->name('pages-pricing');
    Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
    Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');
    Route::get('/pages/misc-comingsoon', [MiscComingSoon::class, 'index'])->name('pages-misc-comingsoon');
    Route::get('/pages/misc-not-authorized', [MiscNotAuthorized::class, 'index'])->name('pages-misc-not-authorized');

    // authentication
    Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
    Route::get('/auth/login-cover', [LoginCover::class, 'index'])->name('auth-login-cover');
    Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
    Route::get('/auth/register-cover', [RegisterCover::class, 'index'])->name('auth-register-cover');
    Route::get('/auth/register-multisteps', [RegisterMultiSteps::class, 'index'])->name('auth-register-multisteps');
    Route::get('/auth/verify-email-basic', [VerifyEmailBasic::class, 'index'])->name('auth-verify-email-basic');
    Route::get('/auth/verify-email-cover', [VerifyEmailCover::class, 'index'])->name('auth-verify-email-cover');
    Route::get('/auth/reset-password-basic', [ResetPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
    Route::get('/auth/reset-password-cover', [ResetPasswordCover::class, 'index'])->name('auth-reset-password-cover');
    Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
    Route::get('/auth/forgot-password-cover', [ForgotPasswordCover::class, 'index'])->name('auth-forgot-password-cover');
    Route::get('/auth/two-steps-basic', [TwoStepsBasic::class, 'index'])->name('auth-two-steps-basic');
    Route::get('/auth/two-steps-cover', [TwoStepsCover::class, 'index'])->name('auth-two-steps-cover');

    // wizard example
    Route::get('/wizard/ex-checkout', [WizardCheckout::class, 'index'])->name('wizard-ex-checkout');
    Route::get('/wizard/ex-property-listing', [PropertyListing::class, 'index'])->name('wizard-ex-property-listing');
    Route::get('/wizard/ex-create-deal', [CreateDeal::class, 'index'])->name('wizard-ex-create-deal');

    // modal
    Route::get('/modal-examples', [ModalExample::class, 'index'])->name('modal-examples');

    // cards
    Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');
    Route::get('/cards/advance', [CardAdvance::class, 'index'])->name('cards-advance');
    Route::get('/cards/statistics', [CardStatistics::class, 'index'])->name('cards-statistics');
    Route::get('/cards/analytics', [CardAnalytics::class, 'index'])->name('cards-analytics');
    Route::get('/cards/gamifications', [CardGamifications::class, 'index'])->name('cards-gamifications');
    Route::get('/cards/actions', [CardActions::class, 'index'])->name('cards-actions');

    // User Interface
    Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
    Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
    Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
    Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
    Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
    Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
    Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
    Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
    Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
    Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
    Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
    Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
    Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
    Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
    Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
    Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
    Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
    Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
    Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

    // extended ui
    Route::get('/extended/ui-avatar', [Avatar::class, 'index'])->name('extended-ui-avatar');
    Route::get('/extended/ui-blockui', [BlockUI::class, 'index'])->name('extended-ui-blockui');
    Route::get('/extended/ui-drag-and-drop', [DragAndDrop::class, 'index'])->name('extended-ui-drag-and-drop');
    Route::get('/extended/ui-media-player', [MediaPlayer::class, 'index'])->name('extended-ui-media-player');
    Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
    Route::get('/extended/ui-star-ratings', [StarRatings::class, 'index'])->name('extended-ui-star-ratings');
    Route::get('/extended/ui-sweetalert2', [SweetAlert::class, 'index'])->name('extended-ui-sweetalert2');
    Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');
    Route::get('/extended/ui-timeline-basic', [TimelineBasic::class, 'index'])->name('extended-ui-timeline-basic');
    Route::get('/extended/ui-timeline-fullscreen', [TimelineFullscreen::class, 'index'])->name('extended-ui-timeline-fullscreen');
    Route::get('/extended/ui-tour', [Tour::class, 'index'])->name('extended-ui-tour');
    Route::get('/extended/ui-treeview', [Treeview::class, 'index'])->name('extended-ui-treeview');
    Route::get('/extended/ui-misc', [Misc::class, 'index'])->name('extended-ui-misc');

    // icons
    Route::get('/icons/tabler', [Tabler::class, 'index'])->name('icons-tabler');
    Route::get('/icons/font-awesome', [FontAwesome::class, 'index'])->name('icons-font-awesome');

    // form elements
    Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
    Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');
    Route::get('/forms/custom-options', [CustomOptions::class, 'index'])->name('forms-custom-options');
    Route::get('/forms/editors', [Editors::class, 'index'])->name('forms-editors');
    Route::get('/forms/file-upload', [FileUpload::class, 'index'])->name('forms-file-upload');
    Route::get('/forms/pickers', [Picker::class, 'index'])->name('forms-pickers');
    Route::get('/forms/selects', [Selects::class, 'index'])->name('forms-selects');
    Route::get('/forms/sliders', [Sliders::class, 'index'])->name('forms-sliders');
    Route::get('/forms/switches', [Switches::class, 'index'])->name('forms-switches');
    Route::get('/forms/extras', [Extras::class, 'index'])->name('forms-extras');

    // form layouts
    Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
    Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');
    Route::get('/form/layouts-sticky', [StickyActions::class, 'index'])->name('form-layouts-sticky');

    // form wizards
    Route::get('/form/wizard-numbered', [FormWizardNumbered::class, 'index'])->name('form-wizard-numbered');
    Route::get('/form/wizard-icons', [FormWizardIcons::class, 'index'])->name('form-wizard-icons');
    Route::get('/form/validation', [Validation::class, 'index'])->name('form-validation');

    // tables
    Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
    Route::get('/tables/datatables-basic', [DatatableBasic::class, 'index'])->name('tables-datatables-basic');
    Route::get('/tables/datatables-advanced', [DatatableAdvanced::class, 'index'])->name('tables-datatables-advanced');
    Route::get('/tables/datatables-extensions', [DatatableExtensions::class, 'index'])->name('tables-datatables-extensions');

    // charts
    Route::get('/charts/apex', [ApexCharts::class, 'index'])->name('charts-apex');
    Route::get('/charts/chartjs', [ChartJs::class, 'index'])->name('charts-chartjs');

    // maps
    Route::get('/maps/leaflet', [Leaflet::class, 'index'])->name('maps-leaflet');

    // laravel example
    Route::get('/laravel/user-management', [UserManagement::class, 'UserManagement'])->name('laravel-example-user-management');
    Route::resource('/user-list', UserManagement::class);
});
