<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get('/get-language-list-for-ddl', [App\Http\Controllers\API\FrontController::class,'getLanguageListForDDL']);
Route::get('/get-language-list-for-ddl-wv', [App\Http\Controllers\API\FrontController::class,'getLanguageListForDDLwv']);

Route::get('/get-default-language', [App\Http\Controllers\API\FrontController::class,'getDefaultLanguage']);


//Stripe Webhook
Route::get('/klarna/order-info/{order_id}', [App\Http\Controllers\API\OrderController::class,'getKlarnaOrderInfo']);
Route::post('/stripe/webhook', [App\Http\Controllers\WebhookController::class,'stripeWebhook']);
Route::post('/swish-payment-callback', [App\Http\Controllers\SwishController::class,'swishPaymentCallback']);

Route::get('/initial-screen', [App\Http\Controllers\API\LandingPageController::class,'initialScreen']);

Route::get('/packages', [App\Http\Controllers\API\PackageController::class, 'index']);
Route::get('/packages/{id}', [App\Http\Controllers\API\PackageController::class, 'show']);
Route::get('/package-by-type/{package_for}', [App\Http\Controllers\API\PackageController::class, 'packageByType']);
Route::get('/package-by-module/{module}', [App\Http\Controllers\API\PackageController::class, 'packageByModule']);
Route::get('/modules', [App\Http\Controllers\API\PackageController::class, 'modules']);

Route::get('/countries', [App\Http\Controllers\API\CountryStateCityController::class, 'countries']);
Route::get('/states/{countryID}', [App\Http\Controllers\API\CountryStateCityController::class, 'states']);
Route::get('/cities/{stateId}', [App\Http\Controllers\API\CountryStateCityController::class, 'cities']);
Route::get('/countryCities/{countryID}', [App\Http\Controllers\API\CountryStateCityController::class, 'countryCities']);
Route::get('/citiesByCountryName/{countryName}', [App\Http\Controllers\API\CountryStateCityController::class, 'citiesByCountryName']);

Route::get('/get-educational-institutes', [App\Http\Controllers\API\FrontController::class,'getEducationInstitutes']);
Route::get('/get-job-tags', [App\Http\Controllers\API\FrontController::class,'getJobTags']);

Route::get('/reason-for-action', [App\Http\Controllers\API\OrderController::class,'reasonForAction']);

Route::get('/email-verification/{email}/{otp}', [App\Http\Controllers\API\AuthController::class,'emailVerification']);

Route::post('/get-otp', [App\Http\Controllers\API\AuthController::class,'getOtp']);
Route::post('/otp-verification', [App\Http\Controllers\API\AuthController::class,'otpVerification']);
Route::post('/email-validate', [App\Http\Controllers\API\AuthController::class,'emailValidate']);
Route::post('/register', [App\Http\Controllers\API\AuthController::class,'register']);
Route::post('/save-user-details', [App\Http\Controllers\API\AuthController::class,'saveUserDetails']);
Route::post('/login', [App\Http\Controllers\API\AuthController::class,'login']);
Route::post('/forgot-password', 'App\Http\Controllers\API\AuthController@forgotPassword');
Route::post('/save-new-password', 'App\Http\Controllers\API\AuthController@saveNewPassword');
Route::get('/get-user-type', [App\Http\Controllers\API\FrontController::class,'getUserType']);
Route::get('/get-service-provider-type', [App\Http\Controllers\API\FrontController::class,'getServiceProviderType']);
Route::get('/get-registration-type', [App\Http\Controllers\API\FrontController::class,'getRegistrationType']);
Route::get('/user-qr/{qr_code}', [App\Http\Controllers\API\FrontController::class,'userQr'])->name('user-qr');
Route::post('/label-by-group-name', [App\Http\Controllers\API\FrontController::class,'labelByGroupName'])->name('label-by-group-name');
Route::post('/get-info-by-group-and-label-name', [App\Http\Controllers\API\FrontController::class,'getInfoByGroupAndLabelName']);
Route::post('/update-user-language', [App\Http\Controllers\API\FrontController::class,'updateUserLanguage']);
Route::get('/get-languages', [App\Http\Controllers\API\FrontController::class,'getLanguages'])->name('get-languages');
Route::get('/get-labels', [App\Http\Controllers\API\FrontController::class,'getLabels'])->name('get-labels');

Route::get('/user-detail/{id}', [App\Http\Controllers\API\LandingPageController::class,'userDetail']);

Route::get('/get-jobs', [App\Http\Controllers\API\LandingPageController::class,'getJobs'])->name('get-jobs');
Route::get('/job-detail/{id}', [App\Http\Controllers\API\LandingPageController::class,'jobDetail']);
Route::get('/get-job-landing-page', [App\Http\Controllers\API\LandingPageController::class, 'jobLandingPage']);
Route::post('/get-jobs-filter', [App\Http\Controllers\API\LandingPageController::class, 'jobFilter']);


Route::post('/get-products-services-books', [App\Http\Controllers\API\LandingPageController::class,'products']);
Route::get('/products-services-books-detail/{id}', [App\Http\Controllers\API\LandingPageController::class,'productDetail']);
Route::get('/get-service-providers', [App\Http\Controllers\API\LandingPageController::class,'getServiceProviders'])->name('get-service-providers');
Route::get('/get-product-landing-page', [App\Http\Controllers\API\LandingPageController::class, 'productLandingPage']);
Route::get('/get-student-product-landing-page', [App\Http\Controllers\API\LandingPageController::class, 'studentProductLandingPage']);
Route::post('/get-company-products-filter', [App\Http\Controllers\API\LandingPageController::class, 'companyProductsFilter']);
Route::post('/get-company-services-filter', [App\Http\Controllers\API\LandingPageController::class, 'companyServicesFilter']);
Route::post('/get-student-products-filter', [App\Http\Controllers\API\LandingPageController::class, 'studentProductsFilter']);
Route::post('/get-student-services-filter', [App\Http\Controllers\API\LandingPageController::class, 'studentServicesFilter']);
Route::post('/get-company-books-filter', [App\Http\Controllers\API\LandingPageController::class, 'companyBooksFilter']);
Route::post('/get-student-books-filter', [App\Http\Controllers\API\LandingPageController::class, 'studentBooksFilter']);
Route::post('/get-similar-products', [App\Http\Controllers\API\LandingPageController::class, 'similarProducts']);


Route::get('/get-contests', [App\Http\Controllers\API\LandingPageController::class,'getContests'])->name('get-contests');
Route::get('/contest-detail/{id}', [App\Http\Controllers\API\LandingPageController::class,'contestDetail']);
Route::get('/get-contest-landing-page', [App\Http\Controllers\API\LandingPageController::class, 'contestLandingPage']);
Route::get('/get-student-contest-landing-page', [App\Http\Controllers\API\LandingPageController::class, 'studentContestLandingPage']);
Route::post('/get-contests-filter', [App\Http\Controllers\API\LandingPageController::class, 'contestFilter']);


Route::post('/push-notification-klarna', [App\Http\Controllers\API\NotificationController::class, 'pushNotificationKlarna']);





Route::post('/job-search', [App\Http\Controllers\API\SearchController::class,'jobSearch']);
Route::post('/product-search', [App\Http\Controllers\API\SearchController::class,'productSearch']);
Route::post('/contest-search', [App\Http\Controllers\API\SearchController::class,'contestSearch']);
Route::post('/common-search', [App\Http\Controllers\API\SearchController::class,'commonSearch']);


//category & subcategory
Route::get('/category-list/{moduleId}/{language_id}', [App\Http\Controllers\API\CategoryController::class, 'categoryList']);
Route::get('/sub-category-list/{catId}/{language_id}', [App\Http\Controllers\API\CategoryController::class, 'subCategoryList']);
Route::get('/attribute-list/{catId}/{language_id}', [App\Http\Controllers\API\CategoryController::class, 'attributeList']);
Route::get('/brands/{catId}', [App\Http\Controllers\API\CategoryController::class, 'brands']);

Route::apiResource('/upload-doc', 'App\Http\Controllers\API\UploadDocController', ['as' => 'api'])->only('store');


Route::group(['middleware' => 'auth:api'],function () {
	Route::post('logout',[App\Http\Controllers\API\AuthController::class,'logout']);

	Route::post('chat-list-count',[App\Http\Controllers\API\MessageController::class,'chatListCount']);

	Route::get('/generate-invoice/{order_id}', 'App\Http\Controllers\API\OrderController@generateInvoice');
	Route::get('/generate-item-invoice/{order_item_id}', 'App\Http\Controllers\API\OrderController@generateItemInvoice');
    
    Route::get('/product-landing-page', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'productLandingPage']);
    Route::get('/student-product-landing-page', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'studentProductLandingPage']);

    Route::get('/book-landing-page', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'bookLandingPage']);
    Route::get('/student-book-landing-page', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'studentBookLandingPage']);

    Route::get('/job-landing-page', [App\Http\Controllers\API\Jobs\JobController::class, 'jobLandingPage']);
    Route::get('/contest-landing-page', [App\Http\Controllers\API\Contests\ContestController::class, 'contestLandingPage']);
    Route::get('/student-contest-landing-page', [App\Http\Controllers\API\Contests\ContestController::class, 'studentContestLandingPage']);
    
	Route::apiResource('/user', 'App\Http\Controllers\API\UserController', ['as' => 'api']);
	Route::get('/user-by-type/{id}', [App\Http\Controllers\API\UserController::class,'userByType']);
	Route::post('/user/{id}/status-update', [App\Http\Controllers\API\UserController::class,'statusUpdate']);
	Route::post('/service-providers-filter', [App\Http\Controllers\API\UserController::class, 'serviceProvidersFilter']);

	//---------------------Auth-User-Routes----------------------------------------///
	Route::post('/password-update', 'App\Http\Controllers\API\UserProfileController@passwordUpdate');
	Route::post('/basic-detail-update', 'App\Http\Controllers\API\UserProfileController@basicDetailUpdate');
	Route::post('/extra-detail-update', 'App\Http\Controllers\API\UserProfileController@extraDetailUpdate');
	Route::post('/add-package', [App\Http\Controllers\API\UserProfileController::class,'addPackage']);
	Route::post('/cv-detail-update', 'App\Http\Controllers\API\UserCvDetailController@cvDetailUpdate');
	Route::apiResource('/address-detail', 'App\Http\Controllers\API\UserAddressDetailController', ['as' => 'api']);
	Route::apiResource('/payment-card-detail', 'App\Http\Controllers\API\PaymentCardDetailController', ['as' => 'api']);
	Route::apiResource('/user-work-experience', 'App\Http\Controllers\API\UserWorkExperienceController', ['as' => 'api']);
	Route::apiResource('/user-education-detail', 'App\Http\Controllers\API\UserEducationDetailController', ['as' => 'api']);
	Route::get('/reward-points-detail', 'App\Http\Controllers\API\UserProfileController@rewardPointDetails');
	Route::post('/language-update', 'App\Http\Controllers\API\UserProfileController@languageUpdate');
	Route::post('/share-reward-points', 'App\Http\Controllers\API\UserProfileController@shareRewardPoints');
	
	Route::get('/download-resume/{user_id}', 'App\Http\Controllers\API\UserCvDetailController@downloadResume');
	
	Route::get('/cool-company-freelancer', 'App\Http\Controllers\API\UserProfileController@coolCompanyFreelancer');

	Route::apiResource('/shipping-condition', 'App\Http\Controllers\API\ShippingConditionController', ['as' => 'api']);

	Route::get('/get-cvs-view', [App\Http\Controllers\API\UserProfileController::class,'getCvsView']);
	Route::get('/update-job-viewed/{job_application_id}', [App\Http\Controllers\API\UserProfileController::class,'updateJobViewed']);

	Route::get('/cvs-view/{id}', [App\Http\Controllers\API\UserProfileController::class,'cvsView']);
	Route::get('/unread-notifications', [App\Http\Controllers\API\UserProfileController::class,'unreadNotifications']);
	Route::get('/transaction-details', 'App\Http\Controllers\API\UserProfileController@transactionDetails');
	Route::get('/earning-details', 'App\Http\Controllers\API\UserProfileController@earningDetails');

	//Notification
	Route::apiResource('/notification', 'App\Http\Controllers\API\NotificationController', ['as' => 'api'])->only('store','index','destroy','show');
	Route::get('/notification/{id}/read', [App\Http\Controllers\API\NotificationController::class,'read']);
	Route::get('/user-notification-delete', [App\Http\Controllers\API\NotificationController::class,'userNotificationDelete']);

	Route::apiResource('/products-services-books', 'App\Http\Controllers\API\Products\ProductsServicesBookController', ['as' => 'api']);
	Route::post('/products-services-books-action/{id}', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'action']);
	Route::post('/products-services-books-stock-update/{id}', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'stockUpdate']);
	Route::apiResource('/favourite-products', 'App\Http\Controllers\API\Products\FavouriteProductController', ['as' => 'api'])->only('index','store','destroy');
	Route::get('/all-products-by-user', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'allProductsByUser']);
	Route::get('/all-services-by-user', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'allServicesByUser']);
	Route::get('/all-books-by-user', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'allBooksByUser']);
	Route::get('/used-products', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'usedProducts']);
	Route::post('/company-products-filter', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'companyProductsFilter']);
	Route::post('/company-services-filter', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'companyServicesFilter']);
	Route::post('/student-products-filter', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'studentProductsFilter']);
	Route::post('/student-services-filter', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'studentServicesFilter']);
    Route::post('products-import', [App\Http\Controllers\API\Products\ProductsServicesBookController::class,'productsImport']);
    Route::post('/similar-products', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'similarProducts']);
    Route::post('/company-books-filter', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'companyBooksFilter']);
	Route::post('/student-books-filter', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'studentBooksFilter']);
	Route::post('/product-mark-as-sold/{id}', [App\Http\Controllers\API\Products\ProductsServicesBookController::class, 'markAsSold']);



	
	Route::post('/create-brand', [App\Http\Controllers\API\CategoryController::class, 'createBrand']);

	//Product Tags
	//Tags
	Route::apiResource('/product-service-book-tags', 'App\Http\Controllers\API\Products\ProductsServicesBookTagController', ['as' => 'api'])->only('index');
	Route::post('product-tags/filter', [App\Http\Controllers\API\Products\ProductsServicesBookTagController::class, 'productTagsFilter']); //pagination

	//Jobs
	Route::get('/jobs/sp-jobs', [App\Http\Controllers\API\Jobs\JobController::class, 'jobSPJobs']); // pagination  (service provider)

	Route::get('/jobs/sp-jobs-applications', [App\Http\Controllers\API\Jobs\JobController::class, 'jobSPJobsApplications']);

	Route::apiResource('/jobs', 'App\Http\Controllers\API\Jobs\JobController', ['as' => 'api']);
	Route::post('/jobs-action/{id}', [App\Http\Controllers\API\Jobs\JobController::class, 'jobAction']);
	
	Route::post('/filter/jobs', [App\Http\Controllers\API\Jobs\JobController::class, 'jobFilter']);

	Route::apiResource('/favourite-jobs', 'App\Http\Controllers\API\Jobs\FavouriteJobController', ['as' => 'api'])->only('index','store','destroy');

	Route::post('/filter/jobapplicant', [App\Http\Controllers\API\Jobs\JobController::class, 'applicantsFilter']); // pagination
	
	Route::get('/jobs/{id}/job-applications', [App\Http\Controllers\API\Jobs\JobController::class, 'jobApplications']); // pagination

	//Tags
	Route::apiResource('/job-tags', 'App\Http\Controllers\API\Jobs\JobTagController', ['as' => 'api']);
	Route::post('job-tags/filter', [App\Http\Controllers\API\Jobs\JobTagController::class, 'jobTagsFilter']); //pagination

	//Job Application
	Route::apiResource('/job-application', 'App\Http\Controllers\API\Jobs\JobApplicationController', ['as' => 'api'])->only('store','index','destroy','show');
	Route::post('/job-application/{id}/update-status', [App\Http\Controllers\API\Jobs\JobApplicationController::class,'statusUpdate']);
 
	
	////////Ashok Routes end

	Route::get('/contact-lists', [App\Http\Controllers\API\MessageController::class,'contactList']);
	Route::get('/chat-lists/{contact_list_id}', [App\Http\Controllers\API\MessageController::class,'chatList']);
	Route::post('/save-message', [App\Http\Controllers\API\MessageController::class,'saveMessage']);
	Route::get('/read-message/{id}', [App\Http\Controllers\API\MessageController::class,'readMessage']);


	Route::apiResource('/cart-detail', 'App\Http\Controllers\API\CartDetailController', ['as' => 'api']);
	Route::get('/empty-cart', [App\Http\Controllers\API\CartDetailController::class,'emptyCart']);
	Route::apiResource('/order', 'App\Http\Controllers\API\OrderController', ['as' => 'api']);

	Route::post('/update-order-payment-status/{order_id}', [App\Http\Controllers\API\OrderController::class,'updateOrderPaymentStatus']);

	Route::get('/all-orders-by-user', [App\Http\Controllers\API\OrderController::class,'allOrdersByUser']);
	Route::get('/all-orders-for-user', [App\Http\Controllers\API\OrderController::class,'allOrdersForUser']);
	Route::post('/order-status-update/{id}', [App\Http\Controllers\API\OrderController::class,'orderStatusUpdate']);
	Route::post('/order-item-status-update/{id}', [App\Http\Controllers\API\OrderController::class,'orderItemStatusUpdate']);
	Route::get('/orders-count', [App\Http\Controllers\API\OrderController::class,'ordersCount']);
	

	
	Route::post('/create-stripe-intent', [App\Http\Controllers\API\OrderController::class,'createStripeIntent']);
	Route::post('/create-stripe-subscription', [App\Http\Controllers\API\OrderController::class,'createStripeSubscription']);
	Route::post('/cancel-stripe-subscription', [App\Http\Controllers\API\OrderController::class,'cancelStripeSubscription']);
	Route::get('/temp-order-delete/{id}', [App\Http\Controllers\API\OrderController::class,'tempOrderDelete']);
	Route::get('/get-temp-order/{id}', [App\Http\Controllers\API\OrderController::class,'getTempOrder']);

	//Checkin API
	Route::post('/checkin-swish', [App\Http\Controllers\API\OrderController::class,'checkinSwish']);
	Route::post('/create-commerce-sale-transaction', [App\Http\Controllers\API\OrderController::class,'createCommerceSaleTransaction']);
	Route::post('/get-the-payment-status', [App\Http\Controllers\API\OrderController::class,'getThePaymentStatus']);
	Route::get('/get-swish-transactions/{transaction_id}', [App\Http\Controllers\API\OrderController::class,'getSwishTransactions']);

	//Swedbankpay capture
	Route::post('/swedbankpay-capture', [App\Http\Controllers\API\OrderController::class,'swedbankpayCapture']);

	Route::apiResource('/rating-and-feedback', 'App\Http\Controllers\API\RatingAndFeedbackController', ['as' => 'api']);
	Route::get('/rating-and-feedback-approve/{id}', [App\Http\Controllers\API\RatingAndFeedbackController::class,'approve']);
	Route::post('/gtin-isbn-search', [App\Http\Controllers\API\FrontController::class,'gtinIsbnSearch']);



	//--------------------------Contest Routes-------------------------------//
	Route::apiResource('/contests', 'App\Http\Controllers\API\Contests\ContestController');
	Route::post('/contests-action/{id}', [App\Http\Controllers\API\Contests\ContestController::class, 'contestAction']);
	Route::post('/filter/contests', [App\Http\Controllers\API\Contests\ContestController::class, 'contestFilter']);
	Route::get('/contests/{id}/contest-applications', [App\Http\Controllers\API\Contests\ContestController::class, 'contestApplications']); 
	Route::apiResource('/contest-application', 'App\Http\Controllers\API\Contests\ContestApplicationController', ['as' => 'api'])->only('store','index','destroy','show');
	Route::post('/contest-application/{id}/update-status', [App\Http\Controllers\API\Contests\ContestApplicationController::class,'statusUpdate']);
	Route::apiResource('/contest-tags', 'App\Http\Controllers\API\Contests\ContestTagController', ['as' => 'api'])->only('index');

	Route::apiResource('/contest-winner', 'App\Http\Controllers\API\Contests\ContestWinnerController', ['as' => 'api'])->only('store');


	
	Route::post('/abuse', [App\Http\Controllers\API\AbuseController::class,'store']);

	Route::post('/products-export', [App\Http\Controllers\API\ExportController::class, 'productsExport']);
	Route::post('/jobs-export', [App\Http\Controllers\API\ExportController::class, 'jobsExport']);
	Route::post('/contests-export', [App\Http\Controllers\API\ExportController::class, 'contestsExport']);
	Route::post('/orders-export', [App\Http\Controllers\API\ExportController::class, 'ordersExport']);


	Route::post('/dashboard', [App\Http\Controllers\API\DashboardController::class, 'index']);
	Route::post('/dashboard-sales-report', [App\Http\Controllers\API\DashboardController::class, 'salesReport']);
	Route::post('/dashboard-recent-orders-list', [App\Http\Controllers\API\DashboardController::class, 'recentOrderList']);
	Route::post('/dashboard-top-selling-list', [App\Http\Controllers\API\DashboardController::class, 'topSellingList']);
	Route::post('/dashboard-sale-amount', [App\Http\Controllers\API\DashboardController::class, 'saleAmount']);

	Route::post('/dashboard-jobs-list', [App\Http\Controllers\API\DashboardController::class, 'jobsList']);


	Route::get('/cool-company-assignment-list', [App\Http\Controllers\API\CoolCompanyController::class,'index']);
	Route::get('/get-assignment-info/{assignmentId}', [App\Http\Controllers\API\CoolCompanyController::class,'getAssignmentInfo']);
	Route::get('/cool-company-statistics', [App\Http\Controllers\API\CoolCompanyController::class,'coolCompanyStatistics']);

	//Stripe
	Route::get('/create-stripe-account', [App\Http\Controllers\API\StripeController::class,'createStripeAccount']);
	Route::get('/check-stripe-account-current-status/{user_id}', [App\Http\Controllers\API\StripeController::class,'checkStripeAccountCurrentStatus']);
	Route::get('/regenerate-stripe-account-link', [App\Http\Controllers\API\StripeController::class,'regenerateStripeAccountLink']);

	Route::get('/vendor-fund-transfer-list', [App\Http\Controllers\API\StripeController::class,'vendorFundTransferList']);

	Route::get('/user-package-subscription-order/{id}', [App\Http\Controllers\API\UserController::class, 'userPackageSubscriptionOrder']);

	Route::post('/user-filter', [App\Http\Controllers\API\Admin\UserController::class,'userFilter']);

});

Route::post('/contact-us', [App\Http\Controllers\API\ContactUsController::class,'store']);


#################################################################
################### Admin #####################
#################################################################
Route::group(['prefix' => 'administration', 'middleware' => ['auth:api', 'admin']],function () {
	Route::post('/dashboard', [App\Http\Controllers\API\Admin\DashboardController::class, 'index']);
	Route::post('/dashboard-sales-report', [App\Http\Controllers\API\Admin\DashboardController::class, 'salesReport']);
	Route::post('/dashboard-recent-orders-list', [App\Http\Controllers\API\Admin\DashboardController::class, 'recentOrderList']);
	Route::post('/dashboard-top-selling-list', [App\Http\Controllers\API\Admin\DashboardController::class, 'topSellingList']);
	Route::post('/dashboard-sale-amount', [App\Http\Controllers\API\Admin\DashboardController::class, 'saleAmount']);

	Route::post('/dashboard-jobs-list', [App\Http\Controllers\API\Admin\DashboardController::class, 'jobsList']);

	Route::apiResource('/jobs', 'App\Http\Controllers\API\Admin\JobController');
	// Route::get('/jobs/{job_id}', [App\Http\Controllers\API\Admin\JobController::class, 'jobDetail']);
	Route::get('/job-delete/{job_id}', [App\Http\Controllers\API\Admin\JobController::class, 'destroy']);
	Route::post('/jobs-action/{id}', [App\Http\Controllers\API\Admin\JobController::class, 'jobAction']);
	Route::post('/jobs-multiple-status-update', [App\Http\Controllers\API\Admin\JobController::class, 'multipleStatusUpdate']);
	Route::post('/jobs-multiple-publish-update', [App\Http\Controllers\API\Admin\JobController::class, 'multiplePublishUpdate']);
	Route::post('/jobs-filter', [App\Http\Controllers\API\Admin\JobController::class, 'filter']);

	//address 
	Route::post('/add-address', [App\Http\Controllers\API\Admin\UserAddressMgmtController::class, 'store']);


	//master

	Route::apiResource('/packages', 'App\Http\Controllers\Admin\PackageController');
	Route::get('/purchased-package/{id}', [App\Http\Controllers\Admin\PackageController::class, 'purchasedPackage']);
	Route::apiResource('/bucket-group', 'App\Http\Controllers\Admin\BucketGroupController')->only('index','store','show','update','destroy');
	Route::get('attribute-list-by-bucket-group/{bucketGroupId}/{language_id}', [App\Http\Controllers\Admin\BucketGroupController::class, 'attributeListByBucketGroup']);
	Route::post('bucket-attribute-create', [App\Http\Controllers\Admin\BucketGroupController::class, 'bucketAttributeCreate']);
	Route::post('bucket-attribute-update', [App\Http\Controllers\Admin\BucketGroupController::class, 'bucketAttributeUpdate']);
	Route::delete('bucket-attribute-delete/{id}', [App\Http\Controllers\Admin\BucketGroupController::class, 'bucketAttributeDestroy']);
	Route::post('create-bucket-group-attribute-category-relation', [App\Http\Controllers\Admin\BucketGroupController::class, 'createBucketGroupAttributeCategoryRelation']);
	Route::post('update-bucket-group-attribute-category-relation', [App\Http\Controllers\Admin\BucketGroupController::class, 'updateBucketGroupAttributeCategoryRelation']);

	Route::apiResource('/reward-point-setting', 'App\Http\Controllers\API\Admin\RewardPointSettingController');
	Route::apiResource('/language', 'App\Http\Controllers\API\Admin\LanguageController');
	Route::post('/languages-import', [App\Http\Controllers\API\Admin\LanguageController::class, 'languagesImport']);
	Route::get('/make-default-language/{id}', [App\Http\Controllers\API\Admin\LanguageController::class, 'makeDefaultLanguage']);
	Route::apiResource('/user-type', 'App\Http\Controllers\API\Admin\UserTypeController');
	Route::apiResource('/module-type', 'App\Http\Controllers\API\Admin\ModuleTypeController');
	Route::apiResource('/category-master', 'App\Http\Controllers\API\Admin\CategoryMasterController');
	Route::post('/single-sub-category-update', [App\Http\Controllers\API\Admin\CategoryMasterController::class, 'singleSubCategoryUpdate']);
	Route::get('/sub-category-delete/{id}', [App\Http\Controllers\API\Admin\CategoryMasterController::class, 'subCategorydelete']);
	Route::post('/categories-import', [App\Http\Controllers\API\Admin\CategoryMasterController::class, 'categoriesImport']);
	Route::apiResource('/label-group', 'App\Http\Controllers\API\Admin\LabelGroupController');
	Route::apiResource('/label', 'App\Http\Controllers\API\Admin\LabelController');
	Route::post('/labels-import', [App\Http\Controllers\API\Admin\LabelController::class, 'labelsImport']);
	Route::apiResource('/service-provider-type-detail', 'App\Http\Controllers\API\Admin\ServiceProviderTypeDetailController');
	Route::post('/service-provider-type-update', [App\Http\Controllers\API\Admin\ServiceProviderTypeDetailController::class, 'serviceProviderTypeUpdate']);
	Route::get('/service-provider-type-delete/{id}', [App\Http\Controllers\API\Admin\ServiceProviderTypeDetailController::class, 'serviceProviderTypeDelete']);

	Route::post('/service-provider-type-filter', [App\Http\Controllers\API\Admin\ServiceProviderTypeDetailController::class, 'serviceProviderTypeFilter']);


	Route::apiResource('/registration-type-detail', 'App\Http\Controllers\API\Admin\RegistrationTypeDetailController');
	Route::post('/registration-type-detail-update', [App\Http\Controllers\API\Admin\RegistrationTypeDetailController::class, 'registrationTypeDetailUpdate']);
	Route::get('/registration-type-delete/{id}', [App\Http\Controllers\API\Admin\RegistrationTypeDetailController::class, 'registrationTypeDestroy']);
	Route::post('/registration-type-filter', [App\Http\Controllers\API\Admin\RegistrationTypeDetailController::class, 'registrationTypeFilter']);

	Route::apiResource('/page', 'App\Http\Controllers\API\Admin\PageController');

	Route::get('/page-content', 'App\Http\Controllers\API\Admin\PageController@pageContent');
	Route::post('/create-page-content', 'App\Http\Controllers\API\Admin\PageController@createPageContent');


	Route::apiResource('/faq', 'App\Http\Controllers\API\Admin\FAQController');
	Route::apiResource('/email-template', 'App\Http\Controllers\API\Admin\EmailTemplateController');
	Route::apiResource('/sms-template', 'App\Http\Controllers\API\Admin\SmsTemplateController');
	Route::post('/appSetting', 'App\Http\Controllers\API\Admin\AppSettingController@update');
	Route::get('/appSettings', 'App\Http\Controllers\API\Admin\AppSettingController@appSettings');
	Route::apiResource('/reason-for-action', 'App\Http\Controllers\API\Admin\ReasonForActionController');


	Route::post('/payment-gateway-setting', 'App\Http\Controllers\API\Admin\PaymentGatewaySettingController@update');
	Route::get('/payment-gateway-settings', 'App\Http\Controllers\API\Admin\PaymentGatewaySettingController@paymentGatewaySettings');

	Route::post('/mail-setting', 'App\Http\Controllers\API\Admin\MailSettingController@update');
	Route::get('/mail-settings', 'App\Http\Controllers\API\Admin\MailSettingController@mailSettings');

	Route::apiResource('/user', 'App\Http\Controllers\API\Admin\UserController');
	Route::get('/user-by-type/{id}', [App\Http\Controllers\API\Admin\UserController::class,'userByType']);
	Route::post('/user/{id}/status-update', [App\Http\Controllers\API\Admin\UserController::class,'statusUpdate']);
	Route::post('/users-multiple-status-update', [App\Http\Controllers\API\Admin\UserController::class, 'multipleStatusUpdate']);
	Route::post('/user-filter', [App\Http\Controllers\API\Admin\UserController::class,'userFilter']);

	Route::get('/user-package-subscriptions', [App\Http\Controllers\API\Admin\UserController::class, 'userPackageSubscriptions']);

	Route::post('/user-basic-detail-update/{user_id}', 'App\Http\Controllers\API\Admin\UserController@basicDetailUpdate');
	Route::post('/user-extra-detail-update/{user_id}', 'App\Http\Controllers\API\Admin\UserController@extraDetailUpdate');
	Route::post('/user-language-update/{user_id}', 'App\Http\Controllers\API\Admin\UserController@languageUpdate');
	Route::get('/user-address-list/{user_id}', [App\Http\Controllers\API\Admin\UserController::class, 'addressList']);
	Route::get('/user-payment-card-list/{user_id}', [App\Http\Controllers\API\Admin\UserController::class, 'paymentCardList']);
	Route::get('/user-reward-points-detail/{user_id}', [App\Http\Controllers\API\Admin\UserController::class, 'rewardPointDetails']);
	Route::get('/user-transaction-details/{user_id}', [App\Http\Controllers\API\Admin\UserController::class, 'transactionDetails']);
	Route::get('/user-earning-details/{user_id}', [App\Http\Controllers\API\Admin\UserController::class, 'earningDetails']);


	Route::apiResource('/job-application', 'App\Http\Controllers\API\Admin\JobApplicationController')->only('index','show','destroy');
	Route::post('/applicant-filter', [App\Http\Controllers\API\Admin\JobApplicationController::class,'applicantFilter']);
	Route::get('/messages', [App\Http\Controllers\API\Admin\MessageController::class,'messages']);

	Route::get('/disputes', [App\Http\Controllers\API\Admin\OrderItemDisputeController::class, 'index']);
	Route::get('/dispute-detail/{id}', [App\Http\Controllers\API\Admin\OrderItemDisputeController::class, 'show']);
	Route::post('/dispute-resolved/{id}', [App\Http\Controllers\API\Admin\OrderItemDisputeController::class,'resolve']);

	Route::apiResource('/contests', 'App\Http\Controllers\API\Admin\ContestController', ['as' => 'admin']);
	// Route::get('/contests/{contest_id}', [App\Http\Controllers\API\Admin\ContestController::class, 'contestDetail']);
	Route::post('/contests-action/{id}', [App\Http\Controllers\API\Admin\ContestController::class, 'contestAction']);
	Route::get('/contest-delete/{id}', [App\Http\Controllers\API\Admin\ContestController::class, 'destroy']);
	Route::post('/contests-multiple-status-update', [App\Http\Controllers\API\Admin\ContestController::class, 'multipleStatusUpdate']);
	Route::post('/contests-multiple-publish-update', [App\Http\Controllers\API\Admin\ContestController::class, 'multiplePublishUpdate']);
	Route::apiResource('/contest-application', 'App\Http\Controllers\API\Admin\ContestApplicationController', ['as' => 'admin'])->only('index','show','destroy');
	Route::post('/contests-filter', [App\Http\Controllers\API\Admin\ContestController::class, 'filter']);
	Route::post('/contest-applicant-filter', [App\Http\Controllers\API\Admin\ContestApplicationController::class, 'applicantFilter']);
	Route::post('/contest-application/{id}/update-status', [App\Http\Controllers\API\Admin\ContestApplicationController::class,'statusUpdate']);
	Route::post('/contest-application/multiple-update-status', [App\Http\Controllers\API\Admin\ContestApplicationController::class,'multipleStatusUpdate']);
	Route::apiResource('/contest-winner', 'App\Http\Controllers\API\Admin\ContestWinnerController', ['as' => 'admin'])->only('index','show','destroy');
	Route::post('/contest-winner-filter', [App\Http\Controllers\API\Admin\ContestWinnerController::class, 'winnerFilter']);



	Route::apiResource('/products-services-books', 'App\Http\Controllers\API\Admin\ProductsServicesBookController', ['as' => 'admin']);
	Route::post('/products-services-books-action/{id}', [App\Http\Controllers\API\Admin\ProductsServicesBookController::class, 'action']);
	Route::post('/products-services-books-multiple-status-update', [App\Http\Controllers\API\Admin\ProductsServicesBookController::class, 'multipleStatusUpdate']);
	Route::post('/products-services-books-multiple-publish-update', [App\Http\Controllers\API\Admin\ProductsServicesBookController::class, 'multiplePublishUpdate']);
	Route::post('/products-services-books-filter', [App\Http\Controllers\API\Admin\ProductsServicesBookController::class, 'filter']);
	Route::post('products-import', [App\Http\Controllers\API\Admin\ProductsServicesBookController::class,'productsImport']);

	Route::apiResource('/order', 'App\Http\Controllers\API\Admin\OrderController', ['as' => 'admin'])->only('index','show');
	Route::post('/order-filter', [App\Http\Controllers\API\Admin\OrderController::class, 'filter']);

	Route::get('/contact-us', [App\Http\Controllers\API\Admin\ContactUsController::class, 'index']);
	Route::get('/contact-us/{id}', [App\Http\Controllers\API\Admin\ContactUsController::class, 'destroy']);

	Route::post('/send-notification', [App\Http\Controllers\API\Admin\NotificationController::class, 'sendNotification']);

	Route::apiResource('/transaction-details', 'App\Http\Controllers\API\Admin\TransactionDetailController', ['as' => 'admin'])->only('index','show');
	Route::apiResource('/rating-and-feedback', 'App\Http\Controllers\API\Admin\RatingAndFeedbackController', ['as' => 'admin'])->only('index','show','store','destroy');
	Route::post('/rating-and-feedback-status-update/{id}', [App\Http\Controllers\API\Admin\RatingAndFeedbackController::class,'statusUpdate']);
	Route::post('/rating-and-feedback-multiple-status-update', [App\Http\Controllers\API\Admin\RatingAndFeedbackController::class,'multipleStatusUpdate']);
	Route::post('/rating-and-feedback-filter', [App\Http\Controllers\API\Admin\RatingAndFeedbackController::class,'filter']);
	Route::post('/rating-and-feedback-import/', [App\Http\Controllers\API\Admin\RatingAndFeedbackController::class,'import']);

	Route::get('/countries', [App\Http\Controllers\API\Admin\CountryStateCityController::class, 'countries']);
	Route::get('/states/{countryID}', [App\Http\Controllers\API\Admin\CountryStateCityController::class, 'states']);
	Route::get('/cities/{stateId}', [App\Http\Controllers\API\Admin\CountryStateCityController::class, 'cities']);


	Route::post('/products-export', [App\Http\Controllers\API\Admin\ExportController::class, 'productsExport']);
	Route::post('/jobs-export', [App\Http\Controllers\API\Admin\ExportController::class, 'jobsExport']);
	Route::post('/contests-export', [App\Http\Controllers\API\Admin\ExportController::class, 'contestsExport']);
	Route::post('/orders-export', [App\Http\Controllers\API\Admin\ExportController::class, 'ordersExport']);
	Route::post('/languages-export', [App\Http\Controllers\API\Admin\ExportController::class, 'languagesExport']);
	Route::post('/categories-export', [App\Http\Controllers\API\Admin\ExportController::class, 'categoriesExport']);
	Route::post('/sample-categories-export', [App\Http\Controllers\API\Admin\ExportController::class, 'sampleCategoriesExport']);
	Route::post('/labels-export', [App\Http\Controllers\API\Admin\ExportController::class, 'labelsExport']);


	Route::apiResource('/brand', 'App\Http\Controllers\API\Admin\BrandController', ['as' => 'admin']);
	Route::post('/brands-import', [App\Http\Controllers\API\Admin\BrandController::class, 'brandsImport']);
	Route::post('/brand-multiple-status-update', [App\Http\Controllers\API\Admin\BrandController::class,'multipleStatusUpdate']);


	Route::apiResource('/abuse', 'App\Http\Controllers\API\Admin\AbuseController', ['as' => 'admin'])->only('index','show','destroy');
	Route::post('/abuse-status-update/{id}', [App\Http\Controllers\API\Admin\AbuseController::class,'statusUpdate']);
	Route::post('/abuse-multiple-status-update', [App\Http\Controllers\API\Admin\AbuseController::class,'multipleStatusUpdate']);

	Route::apiResource('/slider', 'App\Http\Controllers\API\Admin\SliderController', ['as' => 'admin'])->only('index','store','destroy');


	Route::apiResource('/subscriber', 'App\Http\Controllers\API\Admin\SubscriberController', ['as' => 'admin'])->only('index','destroy');

	//Fund transferred log
	Route::post('/vendor-fund-transfer-list', [App\Http\Controllers\API\Admin\VendorFundLogController::class,'vendorFundTransferList']);
	Route::post('/vendor-wise-fund-transfer-list', [App\Http\Controllers\API\Admin\VendorFundLogController::class,'vendorWiseFundTransferList']);

	Route::get('/pending-vendors-fund-to-transferred', [App\Http\Controllers\API\Admin\VendorFundLogController::class,'pendingVendorsFundToTransferred']);

	Route::get('/pending-vendor-fund-to-transferred/{user_id}', [App\Http\Controllers\API\Admin\VendorFundLogController::class,'pendingVendorFundToTransferred']);
	

});



Route::post('/subscriber', [App\Http\Controllers\API\SubscriberController::class,'store']);

Route::get('/get-team-members', [App\Http\Controllers\API\CoolCompanyController::class,'getTeamMember']);
Route::get('/get-team-member/{teamMemberId}', [App\Http\Controllers\API\CoolCompanyController::class,'getTeamMemberInfo']);
Route::post('/assignment-current-status', [App\Http\Controllers\API\CoolCompanyController::class,'assignmentCurrentStatus']);
Route::post('/payment-current-status', [App\Http\Controllers\API\CoolCompanyController::class,'paymentCurrentStatus']);
Route::get('/get-group-invoices', [App\Http\Controllers\API\CoolCompanyController::class,'getGroupInvoices']);
Route::get('/get-group-invoice-by-id/{groupInvoiceId}', [App\Http\Controllers\API\CoolCompanyController::class,'getGroupInvoiceById']);
Route::get('/get-group-invoice-report-by-id/{groupInvoiceId}', [App\Http\Controllers\API\CoolCompanyController::class,'getGroupInvoiceReportById']);
// Route::get('/orders-export', [App\Http\Controllers\API\Admin\ExportController::class, 'ordersExport']);





Route::get('/get-appSettings', 'App\Http\Controllers\API\FrontController@appSettings');
Route::get('/get-reward-point-currency-value', 'App\Http\Controllers\API\FrontController@getRewardPointCurrencyValue');
Route::get('/get-sliders', 'App\Http\Controllers\API\FrontController@getSliders');
Route::get('/get-faqs', 'App\Http\Controllers\API\FrontController@getFaqs');
Route::get('/get-pages', 'App\Http\Controllers\API\FrontController@getPages');
Route::get('/{slug}', 'App\Http\Controllers\API\FrontController@page');

Route::get('/product-attributes/{products_services_books_id}', 'App\Http\Controllers\API\FrontController@productAttributes');
