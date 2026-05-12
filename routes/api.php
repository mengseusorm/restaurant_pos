<?php


use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentMethodReportController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\TestActivityController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OtpController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\AnalyticController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\ExchangeRateController;
use App\Http\Controllers\Admin\ExchangeRateLogController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\OrderTypeController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MemberPointTransactionController;
use App\Http\Controllers\PointGiftController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\PosOrderController;
use App\Http\Controllers\Admin\TimezoneController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemAddonController;
use App\Http\Controllers\Admin\ItemExtraController;
use App\Http\Controllers\Admin\OfferItemController;
use App\Http\Controllers\Auth\DeactivateController;
use App\Http\Controllers\Admin\ItemStockController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SimpleUserController;
use App\Http\Controllers\Admin\SmsGatewayController;
use App\Http\Controllers\Auth\GuestSignupController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\SettingController;
use App\Http\Controllers\Admin\CountryCodeController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\ItemsReportController;
use App\Http\Controllers\Admin\MenuSectionController;
use App\Http\Controllers\Admin\PosCategoryController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Admin\StockRecordController;
use App\Http\Controllers\Admin\StockReportController;
use App\Http\Controllers\Admin\ItemCategoryController;
use App\Http\Controllers\Admin\MenuTemplateController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\DefaultAccessController;
use App\Http\Controllers\Admin\ItemAttributeController;
use App\Http\Controllers\Admin\ItemAttributeVariationController;
use App\Http\Controllers\Admin\BatchApplyVariationController;
use App\Http\Controllers\Admin\ItemVariationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Frontend\TokenStoreController;
use App\Http\Controllers\Admin\KitchenPrinterController;
use App\Http\Controllers\Admin\MyOrderDetailsController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\AnalyticSectionController;
use App\Http\Controllers\Admin\CustomerAddressController;
use App\Http\Controllers\Admin\EmployeeAddressController;
use App\Http\Controllers\Admin\NotificationAlertController;
use App\Http\Controllers\Admin\CreditBalanceReportController;
use App\Http\Controllers\Admin\AdministratorAddressController;
use App\Http\Controllers\Admin\BranchSaleReportController;
use App\Http\Controllers\Admin\BranchTrendReportController;
use App\Http\Controllers\Admin\BranchDailySaleReportController;
use App\Http\Controllers\Admin\DailySaleReportController;
use App\Http\Controllers\Admin\ItemCategoryReportController;
use App\Http\Controllers\Admin\UserSaleReportController;
use App\Http\Controllers\Admin\PosOrderUnpaidController;
use App\Http\Controllers\Admin\PrinterController;
use App\Http\Controllers\Admin\SalesSummaryReportController;
use App\Http\Controllers\Admin\BranchSalesSummaryController;
use App\Http\Controllers\Table\OrderController as TableOrderController;
use App\Http\Controllers\Frontend\ItemController as FrontendItemController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Frontend\BranchController as FrontendBranchController;
use App\Http\Controllers\Admin\TableOrderController as AdminTableOrderController;
use App\Http\Controllers\Admin\OnlineOrderController as AdminOnlineOrderController;
use App\Http\Controllers\Admin\TelegramMiniAppOrderController as AdminTelegramMiniAppOrderController;

use App\Http\Controllers\Frontend\HuionePaymentController;
use App\Http\Controllers\Frontend\LanguageController as FrontendLanguageController;
use App\Http\Controllers\Table\DiningTableController as TableDiningTableController;
use App\Http\Controllers\Table\ItemCategoryController as TableItemCategoryController;
use App\Http\Controllers\OnlineOrder\OnlineOrderBranchController as OnlineOrderBranchController;

use App\Http\Controllers\Table\ItemCategoryController as OnlineOrderItemCategoryController;
use App\Http\Controllers\Table\OrderController as OnlineOrderOrderController;
use App\Http\Controllers\TelegramMiniApp\TelegramMiniAppBranchController;
use App\Http\Controllers\TelegramMiniApp\TelegramMiniAppItemCategoryController;
use App\Http\Controllers\TelegramMiniApp\TelegramMiniAppOrderController;
use App\Http\Controllers\TelegramMiniApp\TelegramMiniAppPayWayController;
use App\Http\Controllers\TelegramMiniApp\TelegramMiniAppExchangeRateController;
use App\Http\Controllers\TelegramMiniApp\TelegramMiniAppCurrencyController;
use App\Http\Controllers\Admin\OrderTypeReportController;
use App\Http\Controllers\Admin\OrderSourceReportController;
use App\Http\Controllers\Admin\HQDashboardController;
use App\Http\Controllers\Admin\PrinterLabelController;

use App\Http\Controllers\Admin\PointEarnRuleController;
use App\Http\Controllers\Admin\PointUsageRuleController;
use App\Http\Controllers\Admin\ExpenseTypeController;
use App\Http\Controllers\Admin\ExpensePaymentMethodController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseReportController;
use App\Http\Controllers\Admin\ShopExpenseReportController;
use App\Http\Controllers\Admin\OrderPrintLogController;
use App\Http\Controllers\Admin\FloorPlanController;
use App\Http\Controllers\Admin\ShopCategoryController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\LostAndFoundController;
use App\Http\Controllers\Admin\BedController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomScheduleController;
use App\Http\Controllers\Admin\TherapistProfileController;
use App\Http\Controllers\Admin\ServiceReportController;
use App\Http\Controllers\Admin\SubSessionController;
use App\Http\Controllers\Admin\GroupSessionController;
use App\Http\Controllers\Admin\FrontDeskController;
use App\Http\Controllers\Admin\SessionQueueController;
use App\Http\Controllers\Admin\CustomerBeverageStorageController;
use App\Http\Controllers\Admin\DailySaleSummaryReportController;
use App\Http\Controllers\Admin\ItemsDetailReportController;
use App\Http\Controllers\Admin\PosOrderDeletedController;
use App\Http\Controllers\Therapist\TherapistAppController;

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

Route::match(['get', 'post'], '/login', function () {
    return response()->json(['errors' => 'unauthenticated'], 401);
})->name('login');

// Connectivity ping endpoint
Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Test activity logging endpoints (remove in production)
Route::prefix('test-activity')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth', [TestActivityController::class, 'testAuth']);
    Route::get('/system', [TestActivityController::class, 'testSystem']);
});

Route::match(['get', 'post'], '/refresh-token', [RefreshTokenController::class, 'refreshToken'])->middleware(['installed']);

Route::prefix('auth')->middleware(['installed', 'localization'])->name('auth.')->namespace('Auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);

    Route::prefix('forgot-password')->name('forgot-password.')->group(function () {
        Route::post('/', [ForgotPasswordController::class, 'forgotPassword']);
        Route::post('/verify-code', [ForgotPasswordController::class, 'verifyCode']);
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
    });

    Route::prefix('signup')->name('signup.')->group(function () {
        Route::post('/otp', [SignupController::class, 'otp']);
        Route::post('/verify', [SignupController::class, 'verify']);
        Route::post('/register', [SignupController::class, 'register']);
    });

    Route::prefix('guest-signup')->name('guest-signup.')->group(function () {
        Route::post('/otp', [GuestSignupController::class, 'otp']);
        Route::post('/verify', [GuestSignupController::class, 'verify']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('verify.api')->group(function () {
            Route::post('/logout', [LoginController::class, 'logout']);
            Route::post('/delete-account', [DeactivateController::class, 'deleteAccount']);
        });
    });

    Route::post('/authcheck', function () {
        if (Auth::check()) {
            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false]);
    });
});


/* all routes must be singular word*/
Route::prefix('profile')->name('profile.')->middleware(['installed', 'apiKey', 'auth:sanctum', 'localization'])->group(function () {
    Route::get('/', [ProfileController::class, 'profile']);
    Route::match(['put', 'patch'], '/', [ProfileController::class, 'update']);
    Route::match(['put', 'patch'], '/change-password', [ProfileController::class, 'changePassword']);
    Route::post('/change-image', [ProfileController::class, 'changeImage']);
});

Route::prefix('therapist')->name('therapist.')->middleware(['installed', 'auth:sanctum', 'localization'])->group(function () {
    Route::get('/rooms', [TherapistAppController::class, 'rooms']);
    Route::get('/rooms/{room}/tasks', [TherapistAppController::class, 'roomTasks']);
    Route::get('/items', [TherapistAppController::class, 'items']);
    Route::get('/beds', [TherapistAppController::class, 'beds']);
    Route::get('/therapists', [TherapistAppController::class, 'therapists']);
    Route::post('/massage-session/{subSession}/add-item', [TherapistAppController::class, 'addItem']);
    Route::post('/massage-session/{subSession}/start-item/{sessionItem}', [TherapistAppController::class, 'startItem']);
    Route::post('/massage-session/{subSession}/complete-item/{sessionItem}', [TherapistAppController::class, 'completeItem']);
});

Route::prefix('admin')->name('admin.')->middleware(['installed', 'auth:sanctum', 'localization'])->group(function () {

    Route::post('/apply-printer', [ItemCategoryController::class, 'applyItemCategoryPrinter']);

    Route::prefix('default-access')->name('default-access.')->group(function () {
        Route::get('/', [DefaultAccessController::class, 'index']);
        Route::post('/', [DefaultAccessController::class, 'storeOrUpdate']);
    });

    Route::prefix('setting')->name('setting.')->group(function () {
        Route::prefix('company')->name('company.')->group(function () {
            Route::get('/', [CompanyController::class, 'index']);
            Route::match(['put', 'patch'], '/', [CompanyController::class, 'update']);
        });

        Route::prefix('printLabel')->name('printLabel.')->group(function () {
            Route::get('/', [PrinterLabelController::class, 'index']);
            Route::get('/show/{printLabelSetting}', [PrinterLabelController::class, 'show']);
            Route::post('/', [PrinterLabelController::class, 'store']);
            Route::match(['put', 'patch'], '/{printLabelSetting}', [PrinterLabelController::class, 'update']);
            Route::delete('/{printLabelSetting}', [PrinterLabelController::class, 'destroy']);
        });

        Route::prefix('site')->name('site.')->group(function () {
            Route::get('/', [SiteController::class, 'index']);
            Route::match(['put', 'patch'], '/', [SiteController::class, 'update']);
        });

        Route::prefix('mail')->name('mail.')->group(function () {
            Route::get('/', [MailController::class, 'index']);
            Route::match(['put', 'patch'], '/', [MailController::class, 'update']);
        });

        Route::prefix('currency')->name('currency.')->group(function () {
            Route::get('/', [CurrencyController::class, 'index']);
            Route::get('/show/{currency}', [CurrencyController::class, 'show']);
            Route::post('/', [CurrencyController::class, 'store']);
            Route::match(['put', 'patch'], '/{currency}', [CurrencyController::class, 'update']);
            Route::delete('/{currency}', [CurrencyController::class, 'destroy']);
        });

        Route::prefix('exchange-rate')->name('exchange-rate.')->group(function () {
            Route::get('/', [ExchangeRateController::class, 'index']);
            Route::get('/show/{exchangeRate}', [ExchangeRateController::class, 'show']);
            Route::post('/', [ExchangeRateController::class, 'store']);
            Route::match(['put', 'patch'], '/{exchangeRate}', [ExchangeRateController::class, 'update']);
            Route::delete('/{exchangeRate}', [ExchangeRateController::class, 'destroy']);
        });

        Route::prefix('exchange-rate-log')->name('exchange-rate-log.')->group(function () {
            Route::get('/', [ExchangeRateLogController::class, 'index']);
        });

        Route::prefix('order-status')->name('order-status.')->group(function () {
            Route::get('/', [OrderStatusController::class, 'index']);
            Route::get('/show/{orderStatus}', [OrderStatusController::class, 'show']);
            Route::post('/', [OrderStatusController::class, 'store']);
            Route::match(['put', 'patch'], '/{orderStatus}', [OrderStatusController::class, 'update']);
            Route::delete('/{orderStatus}', [OrderStatusController::class, 'destroy']);
        });

        Route::prefix('order-type')->name('order-type.')->group(function () {
            Route::get('/', [OrderTypeController::class, 'index']);
            Route::get('/show/{orderType}', [OrderTypeController::class, 'show']);
            Route::post('/', [OrderTypeController::class, 'store']);
            Route::match(['put', 'patch'], '/{orderType}', [OrderTypeController::class, 'update']);
            Route::delete('/{orderType}', [OrderTypeController::class, 'destroy']);
        });

        Route::prefix('tax')->name('tax.')->group(function () {
            Route::get('/', [TaxController::class, 'index']);
            Route::get('/show/{tax}', [TaxController::class, 'show']);
            Route::post('/', [TaxController::class, 'store']);
            Route::match(['put', 'patch'], '/{tax}', [TaxController::class, 'update']);
            Route::delete('/{tax}', [TaxController::class, 'destroy']);
            Route::delete('/remove-from-item/{tax}', [TaxController::class, 'destroyFromItem']);
            Route::put('/apply-to-all-item/{tax}', [TaxController::class, 'applyTaxToAllItem']);
        });

        Route::prefix('shop-category')->name('shop-category.')->group(function () {
            Route::get('/', [ShopCategoryController::class, 'index']);
            Route::get('/show/{shopCategory}', [ShopCategoryController::class, 'show']);
            Route::post('/', [ShopCategoryController::class, 'store']);
            Route::match(['post', 'put', 'patch'], '/{shopCategory}', [ShopCategoryController::class, 'update']);
            Route::delete('/{shopCategory}', [ShopCategoryController::class, 'destroy']);
            Route::post('/sort/category', [ShopCategoryController::class, 'sortCategory']);
        });

        Route::prefix('item-category')->name('item-category.')->group(function () {
            Route::get('/', [ItemCategoryController::class, 'index']);
            Route::post('/upload-images', [ItemCategoryController::class, 'uploadImages']);
            Route::get('/show/{itemCategory}', [ItemCategoryController::class, 'show']);
            Route::post('/', [ItemCategoryController::class, 'store']);
            Route::match(['post', 'put', 'patch'], '/{itemCategory}', [ItemCategoryController::class, 'update']);
            Route::delete('/{itemCategory}', [ItemCategoryController::class, 'destroy']);
            Route::delete('/destroy-from-item/{itemCategory}', [ItemCategoryController::class, 'destroyFromItem']);
            Route::post('/sort/category', [ItemCategoryController::class, 'sortCategory']);
            Route::get('/export', [ItemCategoryController::class, 'export']);
            Route::get('/download-sample', [ItemCategoryController::class, 'downloadSample']);
            Route::post('/import/file', [ItemCategoryController::class, 'import']);
        });

        Route::prefix('item-attribute')->name('item-attribute.')->group(function () {
            Route::get('/', [ItemAttributeController::class, 'index']);
            Route::get('/show/{itemAttribute}', [ItemAttributeController::class, 'show']);
            Route::post('/', [ItemAttributeController::class, 'store']);
            Route::match(['put', 'patch'], '/{itemAttribute}', [ItemAttributeController::class, 'update']);
            Route::delete('/{itemAttribute}', [ItemAttributeController::class, 'destroy']);
        });

        Route::prefix('item-attribute-variation')->name('item-attribute-variation.')->group(function () {
            Route::get('/', [ItemAttributeVariationController::class, 'index']);
            Route::get('/show/{itemAttributeVariation}', [ItemAttributeVariationController::class, 'show']);
            Route::post('/', [ItemAttributeVariationController::class, 'store']);
            Route::match(['put', 'patch'], '/{itemAttributeVariation}', [ItemAttributeVariationController::class, 'update']);
            Route::delete('/{itemAttributeVariation}', [ItemAttributeVariationController::class, 'destroy']);
        });

        Route::prefix('batch-apply-variation')->name('batch-apply-variation.')->group(function () {
            Route::get('/', [BatchApplyVariationController::class, 'index']);
            Route::post('/', [BatchApplyVariationController::class, 'apply']);
            Route::delete('/clear-all', [BatchApplyVariationController::class, 'clearAll']);
            Route::put('/item/{itemId}/price', [BatchApplyVariationController::class, 'updateItemPrice']);
        });

        Route::prefix('branch')->name('branch.')->group(function () {
            Route::get('/', [BranchController::class, 'index']);
            Route::get('/show/{branch}', [BranchController::class, 'show']);
            Route::post('/', [BranchController::class, 'store']);
            Route::match(['put', 'patch'], '/{branch}', [BranchController::class, 'update']);
            Route::delete('/{branch}', [BranchController::class, 'destroy']);
        });

        Route::prefix('menu-section')->name('menu-section.')->group(function () {
            Route::get('/', [MenuSectionController::class, 'index']);
        });

        Route::prefix('menu-template')->name('menu-template.')->group(function () {
            Route::get('/', [MenuTemplateController::class, 'index']);
            Route::get('/show/{menuTemplate}', [MenuTemplateController::class, 'show']);
            Route::post('/', [MenuTemplateController::class, 'store']);
            Route::match(['put', 'patch'], '/{menuTemplate}', [MenuTemplateController::class, 'update']);
            Route::delete('/{menuTemplate}', [MenuTemplateController::class, 'destroy']);
        });

        Route::prefix('page')->name('page.')->group(function () {
            Route::get('/', [PageController::class, 'index']);
            Route::get('/show/{page}', [PageController::class, 'show']);
            Route::post('/', [PageController::class, 'store']);
            Route::match(['post', 'put', 'patch'], '/{page}', [PageController::class, 'update']);
            Route::delete('/{page}', [PageController::class, 'destroy']);
        });

        Route::prefix('license')->name('license.')->group(function () {
            Route::get('/', [LicenseController::class, 'index']);
            Route::match(['put', 'patch'], '/', [LicenseController::class, 'update']);
        });

        Route::prefix('kitchen-printer')->name('kitchen.')->group(function () {
            Route::get('/', [KitchenPrinterController::class, 'index']);
            Route::get('/show/{id}', [KitchenPrinterController::class, 'show']);
            Route::post('/', [KitchenPrinterController::class, 'store']);
            Route::match(['post', 'put', 'patch'], '/update/{kitchenPrinter}', [KitchenPrinterController::class, 'update']);
            Route::delete('/{kitchenPrinter}',[KitchenPrinterController::class,'destroy']);
        });
        Route::prefix('theme')->name('theme.')->group(function () {
            Route::get('/', [ThemeController::class, 'index']);
            Route::post('/', [ThemeController::class, 'update']);
        });

        Route::prefix('sms-gateway')->name('sms-gateway.')->group(function () {
            Route::get('/', [SmsGatewayController::class, 'index']);
            Route::match(['put', 'patch'], '/', [SmsGatewayController::class, 'update']);
        });

        Route::prefix('payment-gateway')->name('payment-gateway.')->group(function () {
            Route::get('/', [PaymentGatewayController::class, 'index']);
            Route::match(['put', 'patch'], '/', [PaymentGatewayController::class, 'update']);
        });

        Route::prefix('payment-method')->name('payment-method.')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index']);
            Route::get('/show/{paymentMethod}', [PaymentMethodController::class, 'show']);
            Route::post('/', [PaymentMethodController::class, 'store']);
            Route::match(['put', 'patch'], '/{paymentMethod}', [PaymentMethodController::class, 'update']);
            Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy']);
        });


        Route::prefix('analytic')->name('analytic.')->group(function () {
            Route::get('/', [AnalyticController::class, 'index']);
            Route::get('/show/{analytic}', [AnalyticController::class, 'show']);
            Route::post('/', [AnalyticController::class, 'store']);
            Route::match(['put', 'patch'], '/{analytic}', [AnalyticController::class, 'update']);
            Route::delete('/{analytic}', [AnalyticController::class, 'destroy']);
        });

        Route::prefix('analytic-section')->name('analytic-section.')->group(function () {
            Route::get('/{analytic}', [AnalyticSectionController::class, 'index']);
            Route::post('/{analytic}', [AnalyticSectionController::class, 'store']);
            Route::match(
                ['put', 'patch'],
                '/{analytic}/{analyticSection}',
                [AnalyticSectionController::class, 'update']
            );
            Route::delete('/{analytic}/{analyticSection}', [AnalyticSectionController::class, 'destroy']);
        });

        Route::prefix('otp')->name('otp.')->group(function () {
            Route::get('/', [OtpController::class, 'index']);
            Route::match(['put', 'patch'], '/', [OtpController::class, 'update']);
        });

        Route::prefix('role')->name('role.')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/show/{role}', [RoleController::class, 'show']);
            Route::match(['put', 'patch'], '/{role}', [RoleController::class, 'update']);
            Route::delete('/{role}', [RoleController::class, 'destroy']);
        });

        Route::prefix('permission')->name('permission.')->group(function () {
            Route::get('/{role}', [PermissionController::class, 'index']);
            Route::match(['put', 'patch'], '/{role}', [PermissionController::class, 'update']);
        });


        Route::prefix('language')->name('language.')->group(function () {
            Route::get('/', [LanguageController::class, 'index']);
            Route::post('/', [LanguageController::class, 'store']);
            Route::get('/show/{language}', [LanguageController::class, 'show']);
            Route::match(['post', 'put', 'patch'], '/update/{language}', [LanguageController::class, 'update']);
            Route::delete('/{language}', [LanguageController::class, 'destroy']);

            Route::get('/file-list/{language:code}', [LanguageController::class, 'fileList']);
            Route::post('/file-text', [LanguageController::class, 'fileText']);
            Route::post('/file-text/store', [LanguageController::class, 'fileTextStore']);
        });

        Route::prefix('notification-alert')->name('notification-alert.')->group(function () {
            Route::get('/', [NotificationAlertController::class, 'index']);
            Route::match(['put', 'patch'], '/', [NotificationAlertController::class, 'update']);
        });

        Route::prefix('notification')->name('notification.')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/', [NotificationController::class, 'update']);
        });
        /**
         * stock-report
         */
    });

    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/show/{customer}', [CustomerController::class, 'show']);
        Route::match(['post', 'put', 'patch'], '/{customer}', [CustomerController::class, 'update']);
        Route::delete('/{customer}', [CustomerController::class, 'destroy']);

        Route::get('/export', [CustomerController::class, 'export']);
        Route::post('/change-password/{customer}', [CustomerController::class, 'changePassword']);
        Route::post('/change-image/{customer}', [CustomerController::class, 'changeImage']);

        Route::get('/my-order/{customer}', [CustomerController::class, 'myOrder']);

        Route::get('/address/{customer}', [CustomerAddressController::class, 'index']);
        Route::get('/address/show/{customer}/{address}', [CustomerAddressController::class, 'show']);
        Route::post('/address/{customer}', [CustomerAddressController::class, 'store']);
        Route::match(['put', 'patch'], '/address/{customer}/{address}', [CustomerAddressController::class, 'update']);
        Route::delete('/address/{customer}/{address}', [CustomerAddressController::class, 'destroy']);
    });

    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::post('/', [MemberController::class, 'store']);
        Route::get('/export', [MemberController::class, 'export']);
        Route::get('/statistics', [MemberController::class, 'statistics']);
        Route::post('/find', [MemberController::class, 'findByPhoneOrCard']);
        Route::get('/show/{member}', [MemberController::class, 'show']);
        Route::match(['put', 'patch'], '/{member}', [MemberController::class, 'update']);
        Route::delete('/{member}', [MemberController::class, 'destroy']);
        Route::post('/{member}/add-points', [MemberController::class, 'addPoints']);
        Route::post('/{member}/deduct-points', [MemberController::class, 'deductPoints']);
    });

    Route::prefix('member-point-transaction')->name('member-point-transaction.')->group(function () {
        Route::get('/', [MemberPointTransactionController::class, 'index']);
        Route::post('/', [MemberPointTransactionController::class, 'store']);
        Route::get('/export', [MemberPointTransactionController::class, 'export']);
        Route::get('/statistics', [MemberPointTransactionController::class, 'statistics']);
        Route::get('/recent', [MemberPointTransactionController::class, 'recent']);
        Route::get('/summary', [MemberPointTransactionController::class, 'summary']);
        Route::post('/by-reference', [MemberPointTransactionController::class, 'getByReference']);
        Route::get('/member/{memberId}', [MemberPointTransactionController::class, 'getByMember']);
        Route::get('/show/{transaction}', [MemberPointTransactionController::class, 'show']);
        Route::post('/{transaction}/revert', [MemberPointTransactionController::class, 'revert']);
        Route::delete('/{transaction}', [MemberPointTransactionController::class, 'destroy']);
    });

    // Point System Routes
    Route::prefix('point-earn-rule')->name('point-earn-rule.')->group(function () {
        Route::get('/', [PointEarnRuleController::class, 'index']);
        Route::get('/active', [PointEarnRuleController::class, 'active']);
        Route::post('/', [PointEarnRuleController::class, 'store']);
        Route::get('/export', [PointEarnRuleController::class, 'export']);
        Route::get('/statistics', [PointEarnRuleController::class, 'statistics']);
        Route::post('/calculate-points', [PointEarnRuleController::class, 'calculatePoints']);
        Route::post('/bulk-update-status', [PointEarnRuleController::class, 'bulkUpdateStatus']);
        Route::get('/show/{pointEarnRule}', [PointEarnRuleController::class, 'show']);
        Route::match(['post', 'put', 'patch'], '/{pointEarnRule}', [PointEarnRuleController::class, 'update']);
        Route::delete('/{pointEarnRule}', [PointEarnRuleController::class, 'destroy']);
        Route::post('/{pointEarnRule}/toggle-status', [PointEarnRuleController::class, 'toggleStatus']);
    });

    Route::prefix('point-usage-rule')->name('point-usage-rule.')->group(function () {
        Route::get('/', [PointUsageRuleController::class, 'index']);
        Route::get('/active', [PointUsageRuleController::class, 'active']);
        Route::post('/', [PointUsageRuleController::class, 'store']);
        Route::get('/export', [PointUsageRuleController::class, 'export']);
        Route::get('/statistics', [PointUsageRuleController::class, 'statistics']);
        Route::get('/usage-types', [PointUsageRuleController::class, 'usageTypes']);
        Route::post('/calculate-currency', [PointUsageRuleController::class, 'calculateCurrency']);
        Route::post('/bulk-update-status', [PointUsageRuleController::class, 'bulkUpdateStatus']);
        Route::get('/show/{pointUsageRule}', [PointUsageRuleController::class, 'show']);
        Route::match(['post', 'put', 'patch'], '/{pointUsageRule}', [PointUsageRuleController::class, 'update']);
        Route::delete('/{pointUsageRule}', [PointUsageRuleController::class, 'destroy']);
        Route::post('/{pointUsageRule}/toggle-status', [PointUsageRuleController::class, 'toggleStatus']);
    });

    // Expense Type Routes
    Route::prefix('expense-type')->name('expense-type.')->group(function () {
        Route::get('/', [ExpenseTypeController::class, 'index']);
        Route::get('/active', [ExpenseTypeController::class, 'active']);
        Route::post('/', [ExpenseTypeController::class, 'store']);
        Route::get('/export', [ExpenseTypeController::class, 'export']);
        Route::get('/statistics', [ExpenseTypeController::class, 'statistics']);
        Route::get('/show/{expenseType}', [ExpenseTypeController::class, 'show']);
        Route::match(['post', 'put', 'patch'], '/{expenseType}', [ExpenseTypeController::class, 'update']);
        Route::delete('/{expenseType}', [ExpenseTypeController::class, 'destroy']);
    });

    // Expense Payment Method Routes
    Route::prefix('expense-payment-method')->name('expense-payment-method.')->group(function () {
        Route::get('/', [ExpensePaymentMethodController::class, 'index']);
        Route::get('/active', [ExpensePaymentMethodController::class, 'active']);
        Route::post('/', [ExpensePaymentMethodController::class, 'store']);
        Route::get('/export', [ExpensePaymentMethodController::class, 'export']);
        Route::get('/statistics', [ExpensePaymentMethodController::class, 'statistics']);
        Route::get('/show/{expensePaymentMethod}', [ExpensePaymentMethodController::class, 'show']);
        Route::match(['post', 'put', 'patch'], '/{expensePaymentMethod}', [ExpensePaymentMethodController::class, 'update']);
        Route::delete('/{expensePaymentMethod}', [ExpensePaymentMethodController::class, 'destroy']);
    });

    // Expense Routes
    Route::prefix('expense')->name('expense.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/export', [ExpenseController::class, 'export']);
        Route::get('/statistics', [ExpenseController::class, 'statistics']);
        Route::get('/show/{expense}', [ExpenseController::class, 'show']);
        Route::match(['post', 'put', 'patch'], '/{expense}', [ExpenseController::class, 'update']);
        Route::delete('/{expense}', [ExpenseController::class, 'destroy']);
        Route::post('/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::post('/{expense}/reject', [ExpenseController::class, 'reject']);
    });

    // Expense Report Routes
    Route::prefix('expense-report')->name('expense-report.')->group(function () {
        Route::get('/daily-summary', [ExpenseReportController::class, 'dailySummary']);
        Route::get('/breakdown-by-type', [ExpenseReportController::class, 'breakdownByType']);
        Route::get('/payment-method-report', [ExpenseReportController::class, 'paymentMethodReport']);
    });

    // Shop Expense Report Routes
    Route::prefix('shop-expense-report')->name('shop-expense-report.')->group(function () {
        Route::get('/daily-summary', [ShopExpenseReportController::class, 'dailySummary']);
        Route::get('/breakdown-by-type', [ShopExpenseReportController::class, 'breakdownByType']);
        Route::get('/payment-method-report', [ShopExpenseReportController::class, 'paymentMethodReport']);
    });

    Route::prefix('point-gift')->name('point-gift.')->group(function () {
        Route::get('/', [PointGiftController::class, 'index']);
        Route::get('/active', [PointGiftController::class, 'active']);
        Route::post('/', [PointGiftController::class, 'store']);
        Route::get('/export', [PointGiftController::class, 'export']);
        Route::get('/statistics', [PointGiftController::class, 'statistics']);
        Route::post('/affordable-by-member', [PointGiftController::class, 'affordableByMember']);
        Route::post('/bulk-update-status', [PointGiftController::class, 'bulkUpdateStatus']);
        Route::get('/show/{pointGift}', [PointGiftController::class, 'show']);
        Route::match(['put', 'patch'], '/{pointGift}', [PointGiftController::class, 'update']);
        Route::delete('/{pointGift}', [PointGiftController::class, 'destroy']);
        Route::post('/{pointGift}/toggle-status', [PointGiftController::class, 'toggleStatus']);
        Route::post('/{pointGift}/redeem', [PointGiftController::class, 'redeem']);
        Route::post('/{pointGift}/validate-redemption', [PointGiftController::class, 'validateRedemption']);
        Route::get('/{pointGift}/redemption-history', [PointGiftController::class, 'redemptionHistory']);
        Route::patch('/{pointGift}/update-stock-limit', [PointGiftController::class, 'updateStockLimit']);
        Route::patch('/{pointGift}/reset-redeemed-count', [PointGiftController::class, 'resetRedeemedCount']);
    });

    // Activity Log Routes
    Route::prefix('activity-log')->name('activity-log.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::get('/statistics', [ActivityLogController::class, 'statistics']);
        Route::get('/by-type/{logName}', [ActivityLogController::class, 'byType']);
        Route::get('/by-user/{userId}', [ActivityLogController::class, 'byUser']);
        Route::get('/show/{activityLog}', [ActivityLogController::class, 'show']);
        Route::delete('/{activityLog}', [ActivityLogController::class, 'destroy']);
        Route::post('/clean', [ActivityLogController::class, 'clean']);
    });

    Route::prefix('my-order')->name('my-order.')->group(function () {
        Route::get('/show/{user}/{order}', [MyOrderDetailsController::class, 'orderDetails']);
    });

    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/show/{employee}', [EmployeeController::class, 'show']);
        Route::match(['put', 'patch'], '/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);

        Route::get('/export', [EmployeeController::class, 'export']);
        Route::post('/change-password/{employee}', [EmployeeController::class, 'changePassword']);
        Route::post('/change-image/{employee}', [EmployeeController::class, 'changeImage']);

        Route::get('/my-order/{employee}', [EmployeeController::class, 'myOrder']);

        Route::get('/address/{employee}', [EmployeeAddressController::class, 'index']);
        Route::get('/address/show/{employee}/{address}', [EmployeeAddressController::class, 'show']);
        Route::post('/address/{employee}', [EmployeeAddressController::class, 'store']);
        Route::match(['put', 'patch'], '/address/{employee}/{address}', [EmployeeAddressController::class, 'update']);
        Route::delete('/address/{employee}/{address}', [EmployeeAddressController::class, 'destroy']);
    });


    Route::prefix('offer')->name('offer.')->group(function () {
        Route::get('/', [OfferController::class, 'index']);
        Route::get('/show/{offer}', [OfferController::class, 'show']);
        Route::post('/', [OfferController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{offer}', [OfferController::class, 'update']);
        Route::delete('/{offer}', [OfferController::class, 'destroy']);
        Route::get('/export', [OfferController::class, 'export']);
        Route::post('/change-image/{offer}', [OfferController::class, 'changeImage']);

        Route::get('/item/{offer}', [OfferItemController::class, 'index']);
        Route::post('/item/{offer}', [OfferItemController::class, 'store']);
        Route::delete('/item/{offer}/{offerItem}', [OfferItemController::class, 'destroy']);
    });

    Route::prefix('item')->name('item.')->group(function () {
        Route::get('/', [ItemController::class, 'index']);
        Route::get('/show/{item}', [ItemController::class, 'show']);
        Route::post('/', [ItemController::class, 'store']);
        Route::post('/upload-images', [ItemController::class, 'uploadImages']);
        Route::get('/export', [ItemController::class, 'export']);
        Route::get('/download-sample', [ItemController::class, 'downloadSample']);
        Route::post('/import/file', [ItemController::class, 'import']);
        Route::post('/change-image/{item}', [ItemController::class, 'changeImage']);
        Route::match(['post', 'put', 'patch'], '/{item}', [ItemController::class, 'update']);
        Route::delete('/{item}', [ItemController::class, 'destroy']);

        Route::get('/variation/{item}', [ItemVariationController::class, 'index']);
        Route::get('/variation/group-by-attribute/{item}', [ItemVariationController::class, 'listGroupByAttribute']);
        Route::post('/variation/{item}', [ItemVariationController::class, 'store']);
        Route::match(['put', 'patch'], '/variation/{item}/{itemVariation}', [ItemVariationController::class, 'update']);
        Route::delete('/variation/{item}/{itemVariation}', [ItemVariationController::class, 'destroy']);
        Route::get('/variation/{item}/show/{itemVariation}', [ItemVariationController::class, 'show']);

        Route::get('/extra/{item}', [ItemExtraController::class, 'index']);
        Route::post('/extra/{item}', [ItemExtraController::class, 'store']);
        Route::match(['put', 'patch'], '/extra/{item}/{itemExtra}', [ItemExtraController::class, 'update']);
        Route::delete('/extra/{item}/{itemExtra}', [ItemExtraController::class, 'destroy']);
        Route::get('/extra/{item}/show/{itemExtra}', [ItemExtraController::class, 'show']);

        Route::get('/addon/{item}', [ItemAddonController::class, 'index']);
        Route::post('/addon/{item}', [ItemAddonController::class, 'store']);
        Route::delete('/addon/{item}/{itemAddon}', [ItemAddonController::class, 'destroy']);
    });

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::post('/', action: [PosController::class, 'store']);
        Route::post('/addOrderStore', action: [PosController::class, 'addOrderStore']);
        Route::post('/customer', [PosController::class, 'storeCustomer']);

    });

    Route::prefix('pos-order')->name('posOrder.')->group(function () {
        Route::get('/', [PosOrderController::class, 'index']);
        Route::get('show/{order}', [PosOrderController::class, 'show']);
        // Route::delete('/itemDelete/{pos}', [PosOrderController::class, 'destroyPosOrder']);

        Route::delete('/{order}', [PosOrderController::class, 'destroy']);
        Route::get('/export', [PosOrderController::class, 'export']);

        Route::post('/pay-order', [PosOrderController::class, 'payOrder']);
        Route::post('/change-status/{order}', [PosOrderController::class, 'changeStatus']);
        Route::post('/change-payment-status/{order}', [PosOrderController::class, 'changePaymentStatus']);
        Route::post('/change-payment-method/{order}',[PosOrderController::class, 'changePaymentMethod']);
        Route::post('/discount/{order}', [PosOrderController::class, 'discount']);
        Route::post('/release-dining-table/{diningTable}', [PosOrderController::class, 'releaseDiningTable']);
        Route::post('/add-dining-table/{order}',[PosOrderController::class, 'addDiningTable']);

        Route::patch('/order-item/{orderItem}', [PosOrderController::class, 'updateOrderItem']);
        Route::delete('/order-item/{orderItem}', [PosOrderController::class, 'destroyOrderItem']);

        Route::patch('/order-info/{order}', [PosOrderController::class, 'updateOrderInfo']);

        // Member and Points related routes
        Route::post('/find-member', [PosOrderController::class, 'findMember']);
        Route::post('/member-point-summary', [PosOrderController::class, 'getMemberPointSummary']);
        Route::post('/{order}/remove-member', [PosOrderController::class, 'removeMemberFromOrder']);
        Route::post('/{order}/set-member', [PosOrderController::class, 'setMemberToOrder']);

        // Combine orders route
        Route::post('/combine', [PosOrderController::class, 'combineOrders']);

        // Transfer items route
        Route::post('/transfer-items', [PosOrderController::class, 'transferItems']);

        Route::get('/count-pending', [PosOrderController::class, 'countPending']); 
    });

    //OrderDeleted Routes
    Route::prefix('pos-order-deleted')->name('posOrderDeleted.')->group(function () {
        Route::get('/',[PosOrderDeletedController::class, 'index']); 
        Route::get('show/{orderDeleted}', [PosOrderDeletedController::class, 'show']); 
        Route::delete('/{orderDeleted}', [PosOrderDeletedController::class, 'destroy']);
        Route::get('/export', [PosOrderDeletedController::class, 'export']);
    });

    //OrderItemDeleted Routes
    Route::prefix('item-order-deleted')->name('itemOrderDeleted.')->group(function () {
        Route::get('/',[PosOrderDeletedController::class, 'orderItemDeletedIndex']); 
        Route::get('show/{orderDeleted}', [PosOrderDeletedController::class, 'show']); 
        Route::delete('/{orderDeleted}', [PosOrderDeletedController::class, 'destroy']);
        Route::get('/export', [PosOrderDeletedController::class, 'exportItemOrderDeleted']);

    });
    


    /***
     * add order Route unpaid
    */
    Route::prefix('pos-order-unpaid')->name('posOrderUnpaid.')->group(function () {
        Route::get('/',[PosOrderUnpaidController::class, 'listUnpaid']);
        Route::delete('/{order}', [PosOrderController::class, 'destroy']);
    });

    /**
     * PayWay Payment Gateway Integration Routes
     */
    Route::prefix('payway')->name('payway.')->group(function () {
        Route::post('/generate-qr', [\App\Http\Controllers\Admin\PayWayController::class, 'generateQR']);
        Route::post('/check-transaction', [\App\Http\Controllers\Admin\PayWayController::class, 'checkTransaction']);
        Route::post('/close-transaction', [\App\Http\Controllers\Admin\PayWayController::class, 'closeTransaction']);
        Route::post('/link-transaction', [\App\Http\Controllers\Admin\PayWayController::class, 'linkTransactionToOrder']);
        Route::post('/refund', [\App\Http\Controllers\Admin\PayWayController::class, 'refundTransaction']);
    });

    /**
     * ABA Partner Self-Activation / Credential Management Routes
     */
    Route::prefix('aba-partner')->name('aba-partner.')->group(function () {
        Route::post('/register-merchant', [\App\Http\Controllers\Admin\ABAPartnerController::class, 'registerMerchant']);
        Route::post('/inquiry-merchant', [\App\Http\Controllers\Admin\ABAPartnerController::class, 'inquiryMerchant']);
        Route::post('/save-credentials', [\App\Http\Controllers\Admin\ABAPartnerController::class, 'saveCredentials']);
    });

    /**
     * PayWay Transactions List Routes
     */
    Route::prefix('payway-transactions')->name('paywayTransactions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PaywayTransactionController::class, 'index']);
        Route::get('/show/{paywayTransaction}', [\App\Http\Controllers\Admin\PaywayTransactionController::class, 'show']);
        Route::get('/export', [\App\Http\Controllers\Admin\PaywayTransactionController::class, 'export']);
    });

    Route::prefix('order-pending')->name('orderPending.')->group(function () {
        Route::get('/',[PosOrderController::class, 'listPending']);
        Route::delete('/{order}', [PosOrderController::class, 'destroy']);
    });

    /**
     * Order Print Logs Routes
     */
    Route::prefix('order-print-logs')->name('orderPrintLog.')->group(function () {
        Route::get('/', [OrderPrintLogController::class, 'index']);
        Route::post('/', [OrderPrintLogController::class, 'store']);
        Route::get('/{id}', [OrderPrintLogController::class, 'show']);
        Route::delete('/{id}', [OrderPrintLogController::class, 'destroy']);
        Route::get('/export', [OrderPrintLogController::class, 'export']);
    });

    /**
     * edd
    */

    Route::prefix('table-order')->name('tableOrder.')->group(function () {
        Route::get('/', [AdminTableOrderController::class, 'index']);
        Route::get('/show/{order}', [AdminTableOrderController::class, 'show']);
        Route::delete('/{order}', [AdminTableOrderController::class, 'destroy']);
        Route::get('/export', [AdminTableOrderController::class, 'export']);
        Route::post('/change-status/{order}', [AdminTableOrderController::class, 'changeStatus']);
        Route::post('/change-payment-status/{order}', [AdminTableOrderController::class, 'changePaymentStatus']);
        Route::post('/token-create/{order}', [AdminTableOrderController::class, 'tokenCreate']);
    });

    Route::prefix('online-order')->name('onlineOrder.')->group(function () {
        Route::get('/', [AdminOnlineOrderController::class, 'index']);
        Route::get('/show/{order}', [AdminOnlineOrderController::class, 'show']);
        Route::delete('/{order}', [AdminOnlineOrderController::class, 'destroy']);
        Route::get('/export', [AdminOnlineOrderController::class, 'export']);
        Route::get('/count-pending', [AdminOnlineOrderController::class, 'countPending']);
        Route::post('/change-status/{order}', [AdminOnlineOrderController::class, 'changeStatus']);
        Route::post('/change-payment-status/{order}', [AdminOnlineOrderController::class, 'changePaymentStatus']);
        Route::post('/token-create/{order}', [AdminOnlineOrderController::class, 'tokenCreate']);
        Route::post('/reject/{order}', [AdminOnlineOrderController::class, 'rejectOrder']);
    });

    Route::prefix('telegram-mini-app-order')->name('telegramMiniAppOrder.')->group(function () {
        Route::get('/', [AdminTelegramMiniAppOrderController::class, 'index']);
        Route::get('/show/{order}', [AdminTelegramMiniAppOrderController::class, 'show']);
        Route::delete('/{order}', [AdminTelegramMiniAppOrderController::class, 'destroy']);
        Route::get('/export', [AdminTelegramMiniAppOrderController::class, 'export']);
        Route::get('/count-pending', [AdminTelegramMiniAppOrderController::class, 'countPending']);
        Route::post('/change-status/{order}', [AdminTelegramMiniAppOrderController::class, 'changeStatus']);
        Route::post('/change-payment-status/{order}', [AdminTelegramMiniAppOrderController::class, 'changePaymentStatus']);
        Route::post('/token-create/{order}', [AdminTelegramMiniAppOrderController::class, 'tokenCreate']);
        Route::post('/reject/{order}', [AdminTelegramMiniAppOrderController::class, 'rejectOrder']);
    });


    Route::prefix('administrator')->name('administrator.')->group(function () {
        Route::get('/', [AdministratorController::class, 'index']);
        Route::get('/show/{administrator}', [AdministratorController::class, 'show']);
        Route::post('/', [AdministratorController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{administrator}', [AdministratorController::class, 'update']);
        Route::delete('/{administrator}', [AdministratorController::class, 'destroy']);

        Route::get('/export', [AdministratorController::class, 'export']);
        Route::post('/change-password/{administrator}', [AdministratorController::class, 'changePassword']);
        Route::post('/change-image/{administrator}', [AdministratorController::class, 'changeImage']);

        Route::get('/my-order/{administrator}', [AdministratorController::class, 'myOrder']);

        Route::get('/address/{administrator}', [AdministratorAddressController::class, 'index']);
        Route::get('/address/show/{administrator}/{address}', [AdministratorAddressController::class, 'show']);
        Route::post('/address/{administrator}', [AdministratorAddressController::class, 'store']);
        Route::match(
            ['put', 'patch'],
            '/address/{administrator}/{address}',
            [AdministratorAddressController::class, 'update']
        );
        Route::delete('/address/{administrator}/{address}', [AdministratorAddressController::class, 'destroy']);
    });

    Route::prefix('timezone')->name('timezone.')->group(function () {
        Route::get('/', [TimezoneController::class, 'index']);
    });

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/total-sales', [DashboardController::class, 'totalSales']);
        Route::get('/total-orders', [DashboardController::class, 'totalOrders']);
        Route::get('/total-customers', [DashboardController::class, 'totalCustomers']);
        Route::get('/total-menu-items', [DashboardController::class, 'totalMenuItems']);
        Route::get('/sales-summary', [DashboardController::class, 'salesSummary']);
        Route::get('/customer-states', [DashboardController::class, 'customerStates']);
        Route::get('/featured-items', [DashboardController::class, 'featuredItems']);
        Route::get('/popular-items', [DashboardController::class, 'mostPopularItems']);
    });

    Route::prefix('hq-dashboard')->name('hq-dashboard.')->group(function () {
        Route::get('/', [HQDashboardController::class, 'index']);
        Route::get('/total-sales', [HQDashboardController::class, 'totalSales']);
        Route::get('/total-orders', [HQDashboardController::class, 'totalOrders']);
        Route::get('/total-customers', [HQDashboardController::class, 'totalCustomers']);
        Route::get('/total-branches', [HQDashboardController::class, 'totalBranches']);
        Route::get('/branch-sales-comparison', [HQDashboardController::class, 'branchSalesComparison']);
        Route::get('/top-performing-branches', [HQDashboardController::class, 'topPerformingBranches']);
        Route::get('/order-status-summary', [HQDashboardController::class, 'orderStatusSummary']);
        Route::get('/payment-method-summary', [HQDashboardController::class, 'paymentMethodSummary']);
        Route::get('/sales-trend', [HQDashboardController::class, 'salesTrend']);
        Route::get('/shop-category-sales-summary', [HQDashboardController::class, 'shopCategorySalesSummary']);
    });

    Route::prefix('sales-report')->name('sales-report.')->group(function () {
        Route::get('/', [SalesReportController::class, 'index']);
        Route::get('/export', [SalesReportController::class, 'export']);
        Route::get('/pdf', [SalesReportController::class, 'pdf']);
    });

    Route::prefix('sales-summary-report')->name('sales-summary-report.')->group(function () {
        Route::get('/salesSummaryReport', [SalesSummaryReportController::class, 'salesSummaryReport']);
        Route::get('/salesSummaryReportExport', [SalesSummaryReportController::class, 'salesSummaryReportExport']);
        Route::get('/salesSummaryReportPDF', [SalesSummaryReportController::class, 'salesSummaryReportPdf']);


        Route::get('/salesSummaryByStaffReport', [SalesSummaryReportController::class, 'salesSummaryStaffReport']);
        Route::get('/salesSummaryByReportStaffExport', [SalesSummaryReportController::class, 'salesSummaryReportByStaffExport']);
        Route::get('/salesSummaryByReportStaffPDF', [SalesSummaryReportController::class, 'salesSummaryReportByStaffPDF']);

    });

    Route::prefix('branch-sales-summary-report')->name('branch-sales-summary-report.')->group(function () {
        Route::get('/', [BranchSalesSummaryController::class, 'index']);
        Route::get('/export', [BranchSalesSummaryController::class, 'export']);
        Route::get('/pdf', [BranchSalesSummaryController::class, 'pdf']);
    });

    Route::prefix('items-report')->name('items-report.')->group(function () {
        Route::get('/', [ItemsReportController::class, 'index']);
        Route::get('/export', [ItemsReportController::class,'export']);
        Route::get('/export-detail', [ItemsReportController::class,'exportDetail']);
        Route::get('/pdf', [ItemsReportController::class, 'pdf']);
    });

    Route::prefix('items-detail-report')->name('items-detail-report.')->group(function () {
        Route::get('/', [ItemsDetailReportController::class, 'index']); 
        Route::get('/export-detail', [ItemsDetailReportController::class,'exportDetail']);
        Route::get('/pdf', [ItemsDetailReportController::class, 'pdf']);
    });

    Route::prefix('items-category-report')->name('items-category-report.')->group(function () {
        Route::get('/', [ItemCategoryReportController::class, 'index']);
        Route::get('/export', [ItemCategoryReportController::class,'export']);
        Route::get('/pdf', [ItemCategoryReportController::class, 'pdf']);
    });

    Route::prefix('credit-balance-report')->name('credit-balance-report.')->group(function () {
        Route::get('/', [CreditBalanceReportController::class, 'index']);
        Route::get('/export', [CreditBalanceReportController::class, 'export']);
    });


    Route::prefix('country-code')->name('country-code.')->group(function () {
        Route::get('/', [CountryCodeController::class, 'index']);
        Route::get('/show/{country}', [CountryCodeController::class, 'show']);
    });

    Route::prefix('transaction')->name('transaction.')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/export', [TransactionController::class, 'export']);
    });

    Route::prefix('transactions')->name('transactions.')->middleware(['auth:sanctum'])->group(function () {
        Route::post('/void', [TransactionController::class, 'voidTransaction']);
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [SimpleUserController::class, 'index']);
    });

    Route::prefix('pos-category')->name('pos-category.')->group(function () {
        Route::get('/', [PosCategoryController::class, 'index']);
    });

    Route::prefix('dining-table')->name('dining-table.')->group(function () {
        Route::get('/', [DiningTableController::class, 'index']);
        Route::get('/show/{diningTable}', [DiningTableController::class, 'show']);
        Route::post('/', [DiningTableController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{diningTable}', [DiningTableController::class, 'update']);
        Route::delete('/{diningTable}', [DiningTableController::class, 'destroy']);
        Route::post('/change-image/{diningTable}', [DiningTableController::class, 'changeImage']);
        Route::get('/export', [DiningTableController::class, 'export']);
    });

    Route::prefix('floor-plan')->name('floor-plan.')->group(function () {
        Route::get('/', [FloorPlanController::class, 'index']);
        Route::get('/groups', [FloorPlanController::class, 'getGroups']);
        Route::get('/groups/{group}/tables', [FloorPlanController::class, 'getTablesForGroup']);
        Route::get('/analytics', [FloorPlanController::class, 'getAnalytics']);
        Route::get('/tables/{table}', [FloorPlanController::class, 'getTableDetails']);

        Route::post('/groups', [FloorPlanController::class, 'store']);
        Route::post('/groups/{floorPlanGroup}', [FloorPlanController::class, 'update']);
        Route::delete('/groups/{floorPlanGroup}', [FloorPlanController::class, 'destroy']);

        // Image change routes
        Route::post('/groups/change-image/{floorPlanGroup}', [FloorPlanController::class, 'changeGroupImage']);
        Route::post('/tables/change-image/{diningTable}', [FloorPlanController::class, 'changeTableImage']);

        Route::patch('/tables/{table}/position', [FloorPlanController::class, 'updateTablePosition']);
        Route::patch('/tables/{table}/properties', [FloorPlanController::class, 'updateTableProperties']);
        Route::patch('/tables/{table}/guests', [FloorPlanController::class, 'updateCurrentGuests']);
    });

    Route::prefix('reservation')->name('reservation.')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::get('/show/{reservation}', [ReservationController::class, 'show']);
        Route::post('/', [ReservationController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{reservation}', [ReservationController::class, 'update']);
        Route::delete('/{reservation}', [ReservationController::class, 'destroy']);
        Route::post('/{reservation}/check-in', [ReservationController::class, 'checkIn']);
        Route::post('/{reservation}/check-out', [ReservationController::class, 'checkOut']);
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel']);
    });

    Route::prefix('lost-and-found')->name('lost-and-found.')->group(function () {
        Route::get('/', [LostAndFoundController::class, 'index']);
        Route::get('/show/{lostAndFound}', [LostAndFoundController::class, 'show']);
        Route::post('/', [LostAndFoundController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{lostAndFound}', [LostAndFoundController::class, 'update']);
        Route::delete('/{lostAndFound}', [LostAndFoundController::class, 'destroy']);
        Route::post('/{lostAndFound}/mark-as-claimed', [LostAndFoundController::class, 'markAsClaimed']);
        Route::post('/{lostAndFound}/mark-as-disposed', [LostAndFoundController::class, 'markAsDisposed']);
    });

    Route::prefix('customer-beverage-storage')->name('customer-beverage-storage.')->group(function () {
        Route::get('/', [CustomerBeverageStorageController::class, 'index']);
        Route::get('/show/{customerBeverageStorage}', [CustomerBeverageStorageController::class, 'show']);
        Route::post('/', [CustomerBeverageStorageController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{customerBeverageStorage}', [CustomerBeverageStorageController::class, 'update']);
        Route::delete('/{customerBeverageStorage}', [CustomerBeverageStorageController::class, 'destroy']);
        Route::post('/{customerBeverageStorage}/mark-as-claimed', [CustomerBeverageStorageController::class, 'markAsClaimed']);
        Route::post('/{customerBeverageStorage}/mark-as-disposed', [CustomerBeverageStorageController::class, 'markAsDisposed']);
    });

    Route::prefix('payment-method-report')->name('payment-method-report.')->group(function () {
        Route::get('/', [PaymentMethodReportController::class, 'index']);
        Route::get('/export', [PaymentMethodReportController::class, 'export']);
        Route::get('/pdf', [PaymentMethodReportController::class, 'pdf']);
    });

    Route::prefix('order-type-report')->name('order-type-report.')->group(function () {
        Route::get('/', [OrderTypeReportController::class, 'index']);
        Route::get('/export', [OrderTypeReportController::class,'export']);
        Route::get('/pdf', [OrderTypeReportController::class, 'pdf']);
    });

    Route::prefix('order-source-report')->name('order-source-report.')->group(function () {
        Route::get('/', [OrderSourceReportController::class, 'index']);
        Route::get('/export', [OrderSourceReportController::class,'export']);
        Route::get('/pdf', [OrderSourceReportController::class, 'pdf']);
    });

    Route::prefix('branch-sale-report')->name('branch-sale-report.')->group(function () {
        Route::get('/', [BranchSaleReportController::class, 'index']);
        Route::get('/export', [BranchSaleReportController::class, 'export']);
        Route::get('/pdf', [BranchSaleReportController::class, 'pdf']);
    });

    Route::prefix('branch-trend-report')->name('branch-trend-report.')->group(function () {
        Route::get('/', [BranchTrendReportController::class, 'index']);
        Route::get('/trend-data', [BranchTrendReportController::class, 'trendData']);
        Route::get('/summary-data', [BranchTrendReportController::class, 'summaryData']);
        Route::get('/export', [BranchTrendReportController::class, 'export']);
        Route::get('/pdf', [BranchTrendReportController::class, 'pdf']);
    });

    Route::prefix('branch-daily-sale-report')->name('branch-daily-sale-report.')->group(function () {
        Route::get('/', [BranchDailySaleReportController::class, 'index']);
        Route::get('/export', [BranchDailySaleReportController::class, 'export']);
        Route::get('/pdf', [BranchDailySaleReportController::class, 'pdf']);
    });

    Route::prefix('user-sales-report')->name('user-sales-report.')->group(function () {
        Route::get('/', [UserSaleReportController::class, 'index']);
        Route::get('/export', [UserSaleReportController::class, 'export']);
        Route::get('/pdf', [UserSaleReportController::class, 'pdf']);
    });

    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/', [ItemStockController::class, 'index']);
        Route::post('/', [ItemStockController::class, 'store']);
        Route::get('/show/{itemStock}', [ItemStockController::class, 'show']);
        Route::match(['post','put', 'patch'], '/update/{ItemStock}',[ItemStockController::class, 'update']);
        Route::delete('/warehouse-delete/{itemStock}', [ItemStockController::class, 'destroy']);
        Route::get('/export', [ItemStockController::class, 'export']);
        Route::get('/pdf', [ItemStockController::class, 'pdf']);
    });

    Route::prefix('stock-record')->name('stock-record.')->group(function () {
        Route::get('/', [StockRecordController::class, 'index']);
        Route::get('/show/{stockRecord}', [StockRecordController::class, 'show']);
        Route::post('/', [StockRecordController::class, 'store']);
        Route::post('/stock-transfer', [StockRecordController::class, 'storeStockTransfer']);

        Route::match(['post','put', 'patch'], '/update/{stockRecord}',[StockRecordController::class, 'update']);
        Route::delete('/stock-record-delete/{stockRecord}', [StockRecordController::class, 'destroy']);
        Route::post('/cut-stock',[StockRecordController::class,'cutstock']);
        Route::get('/export', [StockRecordController::class, 'export']);
        Route::get('/pdf', [StockRecordController::class, 'pdf']);
    });

    Route::prefix('stock-report')->name('stock-report.')->group(function () {
        Route::get('/', [StockReportController::class, 'index']);
        Route::get('/export', [StockReportController::class, 'export']);
        Route::get('/pdf', [StockReportController::class, 'pdf']);
    });

    Route::prefix('daily-sale-report')->name('daily-sale-report.')->group(function () {
        Route::get('/', [DailySaleReportController::class, 'index']);
        Route::get('/export', [DailySaleReportController::class,'export']);
        Route::get('/pdf', [DailySaleReportController::class, 'pdf']);
    });

    Route::prefix('daily-sale-summary-report')->name('daily-sale-summary-report.')->group(function () {
        Route::get('/', [DailySaleSummaryReportController::class, 'index']);
        Route::get('/export', [DailySaleSummaryReportController::class,'export']);
        Route::get('/pdf', [DailySaleSummaryReportController::class, 'pdf']);
    });

    // Massage Shop Routes
    Route::prefix('room')->name('room.')->group(function () {
        Route::get('/', [RoomController::class, 'index']);
        Route::get('/show/{room}', [RoomController::class, 'show']);
        Route::post('/', [RoomController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{room}', [RoomController::class, 'update']);
        Route::delete('/{room}', [RoomController::class, 'destroy']);
        Route::post('/{room}/change-status', [RoomController::class, 'changeStatus']);
    });

    // Bed routes (unit of operation within a room)
    Route::prefix('bed')->name('bed.')->group(function () {
        Route::get('/', [BedController::class, 'index']);
        Route::get('/show/{bed}', [BedController::class, 'show']);
        Route::post('/', [BedController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{bed}', [BedController::class, 'update']);
        Route::delete('/{bed}', [BedController::class, 'destroy']);
        Route::post('/{bed}/change-status', [BedController::class, 'changeStatus']);
    });

    Route::prefix('therapist-profile')->name('therapist-profile.')->group(function () {
        Route::get('/', [TherapistProfileController::class, 'index']);
        Route::post('/verify', [TherapistProfileController::class, 'verifyByCode']);
        Route::get('/show/{therapistProfile}', [TherapistProfileController::class, 'show']);
        Route::post('/', [TherapistProfileController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{therapistProfile}', [TherapistProfileController::class, 'update']);
        Route::delete('/{therapistProfile}', [TherapistProfileController::class, 'destroy']);
        Route::post('/{therapistProfile}/change-status', [TherapistProfileController::class, 'changeStatus']);
    });

    Route::prefix('service-report')->name('service-report.')->group(function () {
        Route::get('/', [ServiceReportController::class, 'index']);
        Route::get('/export', [ServiceReportController::class, 'export']);
        Route::get('/pdf', [ServiceReportController::class, 'pdf']);
    });
    
    Route::prefix('massage-session')->name('massage-session.')->group(function () {
        Route::get('/', [SubSessionController::class, 'index']);
        Route::get('/show/{subSession}', [SubSessionController::class, 'show']);
        Route::post('/', [SubSessionController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{subSession}', [SubSessionController::class, 'update']);
        Route::delete('/{subSession}', [SubSessionController::class, 'destroy']);
        Route::post('/{subSession}/start', [SubSessionController::class, 'start']);
        Route::post('/{subSession}/complete', [SubSessionController::class, 'complete']);
        Route::post('/{subSession}/start-item/{sessionItem}', [SubSessionController::class, 'startItem']);
        Route::post('/{subSession}/complete-item/{sessionItem}', [SubSessionController::class, 'completeItem']);
        Route::post('/{subSession}/extend', [SubSessionController::class, 'extend']);
        Route::post('/{subSession}/change-therapist', [SubSessionController::class, 'changeTherapist']);
        Route::post('/{subSession}/change-start-time', [SubSessionController::class, 'changeStartTime']);
        Route::post('/{subSession}/add-item', [SubSessionController::class, 'addItem']);
        Route::match(['post', 'put', 'patch'], '/{subSession}/update-item/{sessionItem}', [SubSessionController::class, 'updateItem']);
        Route::post('/{subSession}/add-items', [SubSessionController::class, 'addItems']);
        Route::delete('/{subSession}/remove-item/{sessionItem}', [SubSessionController::class, 'removeItem']);
        Route::post('/{subSession}/checkout', [SubSessionController::class, 'checkout']);
        // list massage by room id
        Route::get('/room/{room}', [SubSessionController::class, 'listByRoom']);
    });

    // Group Session Routes (multi-person booking)
    Route::prefix('group-session')->name('group-session.')->group(function () {
        Route::get('/', [GroupSessionController::class, 'index']);
        Route::get('/show/{groupSession}', [GroupSessionController::class, 'show']);
        Route::post('/', [GroupSessionController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{groupSession}', [GroupSessionController::class, 'update']);
        Route::delete('/{groupSession}', [GroupSessionController::class, 'destroy']);
        Route::post('/{groupSession}/add-sub-session', [GroupSessionController::class, 'addSubSession']);
        Route::delete('/{groupSession}/remove-sub-session/{subSession}', [GroupSessionController::class, 'removeSubSession']);
        Route::post('/{groupSession}/checkout', [GroupSessionController::class, 'checkout']);
        Route::post('/{groupSession}/checkout-split', [GroupSessionController::class, 'checkoutSplit']);
    });

    Route::prefix('front-desk')->name('front-desk.')->group(function () {
        Route::get('/board', [FrontDeskController::class, 'board']);
        Route::get('/room-board/{roomId}', [FrontDeskController::class, 'roomBoard']);
    });

    Route::prefix('room-schedule')->name('room-schedule.')->group(function () {
        Route::get('/', [RoomScheduleController::class, 'index']);
    });

    Route::prefix('session-queue')->name('session-queue.')->group(function () {
        Route::get('/', [SessionQueueController::class, 'index']);
        Route::get('/show/{sessionQueue}', [SessionQueueController::class, 'show']);
        Route::post('/', [SessionQueueController::class, 'store']);
        Route::match(['post', 'put', 'patch'], '/{sessionQueue}', [SessionQueueController::class, 'update']);
        Route::delete('/{sessionQueue}', [SessionQueueController::class, 'destroy']);
        Route::post('/{sessionQueue}/call', [SessionQueueController::class, 'call']);
        Route::post('/{sessionQueue}/seat', [SessionQueueController::class, 'seat']);
        Route::post('/{sessionQueue}/cancel', [SessionQueueController::class, 'cancel']);
    });
});

Route::prefix('frontend')->name('frontend.')->middleware(['installed', 'apiKey', 'localization'])->group(function () {
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
    });

    Route::prefix('page')->name('page.')->group(function () {
        Route::get('/', [FrontendPageController::class, 'index']);
        Route::get('/show/{page:slug}', [FrontendPageController::class, 'show']);
        Route::get('/page-info/{page}', [FrontendPageController::class, 'show']);
    });

    Route::prefix('branch')->name('branch.')->group(function () {
        Route::get('/', [FrontendBranchController::class, 'index']);
        Route::get('/show/{branch}', [FrontendBranchController::class, 'show']);
    });

    Route::prefix('language')->name('language.')->group(function () {
        Route::get('/', [FrontendLanguageController::class, 'index']);
        Route::get('/show/{language}', [FrontendLanguageController::class, 'show']);
    });

    Route::prefix('item')->name('item.')->group(function () {
        Route::get('/', [FrontendItemController::class, 'index']);
        Route::get('/featured-items', [FrontendItemController::class, 'featuredItems']);
        Route::get('/popular-items', [FrontendItemController::class, 'mostPopularItems']);
    });

    Route::prefix('device-token')->name('device-token.')->middleware(['auth:sanctum'])->group(function () {
        Route::post('/web', [TokenStoreController::class, 'webToken']);
        Route::post('/mobile', [TokenStoreController::class, 'deviceToken']);
    });
});

Route::prefix('table')->name('table.')->middleware(['installed', 'apiKey', 'localization'])->group(function () {

    Route::prefix('item-category')->name('item-category.')->group(function () {
        Route::get('/', [TableItemCategoryController::class, 'index']);
        Route::get('/show/{itemCategory:slug}', [TableItemCategoryController::class, 'show']);
    });

    Route::prefix('dining-table')->name('dining-table.')->group(function () {
        Route::get('/', [TableDiningTableController::class, 'index']);
        Route::get('/show/{frontendDiningTable:slug}', [TableDiningTableController::class, 'show']);
    });

    Route::prefix('dining-order')->name('dining-order.')->group(function () {
        Route::get('/show/{frontendOrder}', [TableOrderController::class, 'show']);
        Route::post('/', [TableOrderController::class, 'store']);
    });
});


Route::prefix('online-order')->name('online-order.')->middleware(['installed', 'apiKey', 'localization'])->group(function () {

    Route::prefix('item-category')->name('item-category.')->group(function () {
        Route::get('/', [OnlineOrderItemCategoryController::class, 'index']);
        Route::get('/show/{itemCategory:slug}', [OnlineOrderItemCategoryController::class, 'show']);
    });

    Route::prefix('branch')->name('branch.')->group(function () {
        Route::get('/', [OnlineOrderBranchController::class, 'index']);
        Route::get('/show/{branch:online_order_slug}', [OnlineOrderBranchController::class, 'show']);
    });

    Route::prefix('order')->name('order.')->group(function () {
        Route::get('/show/{frontendOrder}', [OnlineOrderOrderController::class, 'show']);
        Route::post('/', [OnlineOrderOrderController::class, 'storeOnlineOrder']);
    });



});

Route::prefix('huione-payment')->name('huione-payment.')->group(function () {
    // Route::get('/{order}/pay', [HuionePaymentController::class, 'index'])->name('index');
    Route::post('/{order}/place-order', [HuionePaymentController::class, 'placeOrder'])->name('placeOrder');
    Route::get('/{order}/payment-status', [HuionePaymentController::class, 'paymentStatus'])->name('paymentStatus');
    Route::post('/callback', [HuionePaymentController::class, 'callback'])->name('callback');
    Route::get('/check-payment-order-status', [HuionePaymentController::class, 'checkPaymentOrderStatus'])->name('checkPaymentOrderStatus');

    // Route::match(['get', 'post'], '/{paymentGateway:slug}/{order}/success', [PaymentController::class, 'success'])->name('success');
    // Route::match(['get', 'post'], '/{paymentGateway:slug}/{order}/fail', [PaymentController::class, 'fail'])->name('fail');
    // Route::match(['get', 'post'], '/{paymentGateway:slug}/{order}/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    // Route::get('/successful/{order}', [PaymentController::class, 'successful'])->name('successful');
});

// PayWay Public Callback Route (no authentication required)
Route::post('/payway/callback', [\App\Http\Controllers\Admin\PayWayController::class, 'callback'])->name('payway.callback');

// ABA Partner Pushback Route (no authentication required – called by ABA Bank)
Route::post('/aba-partner/pushback', [\App\Http\Controllers\Admin\ABAPartnerController::class, 'pushbackCallback'])->name('aba-partner.pushback');

Route::prefix('payment-method')->name('payment-method.')->group(function () {
    Route::get('/online-payment', [PaymentMethodController::class, 'listOnlinePayment']);
    Route::get('/table-payment', [PaymentMethodController::class, 'listTablePayment']);
    Route::get('/show/{paymentMethod}', [PaymentMethodController::class, 'show']);
});

Route::post('/print_receipt',[PrinterController::class,'print']);



// customer routes for telegram mini app
Route::prefix('telegram-mini-app')->name('telegram-mini-app.')->middleware(['installed', 'apiKey', 'localization'])->group(function () {

    Route::prefix('item-category')->name('item-category.')->group(function () {
        Route::get('/', [TelegramMiniAppItemCategoryController::class, 'index']);
        Route::get('/show/{itemCategory:slug}', [TelegramMiniAppItemCategoryController::class, 'show']);
    });

    Route::prefix('branch')->name('branch.')->group(function () {
        Route::get('/', [TelegramMiniAppBranchController::class, 'index']);
        Route::get('/show/{branch:telegram_mini_app_slug}', [TelegramMiniAppBranchController::class, 'show']);
    });

    Route::prefix('order')->name('order.')->group(function () {
        Route::get('/show/{frontendOrder}', [TelegramMiniAppOrderController::class, 'show']);
        Route::post('/', [TelegramMiniAppOrderController::class, 'store']);
        Route::get('/count/{telegramUserId}', [TelegramMiniAppOrderController::class, 'getOrderCount']);
        Route::get('/user/{telegramUserId}', [TelegramMiniAppOrderController::class, 'getUserOrders']);
    });

    Route::prefix('payway')->name('payway.')->group(function () {
        Route::post('/generate-qr', [TelegramMiniAppPayWayController::class, 'generateQR']);
        Route::post('/check-transaction', [TelegramMiniAppPayWayController::class, 'checkTransaction']);
        Route::post('/close-transaction', [TelegramMiniAppPayWayController::class, 'closeTransaction']);
        Route::post('/link-transaction', [TelegramMiniAppPayWayController::class, 'linkTransactionToOrder']);
    });

    Route::prefix('exchange-rate')->name('exchange-rate.')->group(function () {
        Route::get('/', [TelegramMiniAppExchangeRateController::class, 'index']);
    });

    Route::prefix('currency')->name('currency.')->group(function () {
        Route::get('/', [TelegramMiniAppCurrencyController::class, 'index']);
    });
});
Route::post('/print_receipt_usb',[PrinterController::class,'printReceiptUSB']);
Route::post('/print_label',[PrinterController::class,'printLabel']);
