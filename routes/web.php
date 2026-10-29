<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebreportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\LicenseController;

// ── Aktivasi Lisensi (dikecualikan dari penguncian lisensi) ───
Route::get('/license',            [LicenseController::class, 'show'])->name('license.show');
Route::post('/license/activate',  [LicenseController::class, 'activate'])->name('license.activate');

// ── Public Landing Page ──────────────────────────────────────
Route::get('/',                    [WebreportController::class, 'landing'])->name('webreport.landing');

// ── Public Pages & Registration ──────────────────────────────
Route::get('/daftar-harga',             [WebreportController::class, 'publicPrices'])->name('webreport.public-prices');
Route::get('/daftar-harga/json',        [WebreportController::class, 'publicPricesJson'])->name('webreport.public-prices-json');
Route::get('/harga/{token}',            [WebreportController::class, 'publicPricesToken'])->name('webreport.public-prices-token');
Route::get('/harga/{token}/json',       [WebreportController::class, 'publicPricesTokenJson'])->name('webreport.public-prices-token-json');
Route::get('/register',                 [RegistrationController::class, 'show'])->name('webreport.register');
Route::post('/register',           [RegistrationController::class, 'store'])->name('webreport.register.post');
Route::post('/register/send-otp',  [RegistrationController::class, 'sendOtp'])->name('webreport.register.send-otp');
Route::get('/register/template',   [RegistrationController::class, 'downloadTemplate'])->name('webreport.register.template');

// ── Member / Reseller Portal Auth ────────────────────────────
Route::get('/login',  [WebreportController::class, 'loginView'])->name('webreport.login');
Route::post('/login', [WebreportController::class, 'login'])->name('webreport.login.post');
Route::post('/logout',[WebreportController::class, 'logout'])->name('webreport.logout');

// ── Authenticated Member Pages ───────────────────────────────
Route::get('/dashboard',                  [WebreportController::class, 'dashboard'])->name('webreport.dashboard');
Route::get('/transactions',              [WebreportController::class, 'transactions'])->name('webreport.transactions');
Route::get('/transactions/{trxid}',      [WebreportController::class, 'transactionDetail'])->name('webreport.transactions.detail');
Route::get('/deposit',                   [WebreportController::class, 'deposit'])->name('webreport.deposit');
Route::post('/deposit/request',          [WebreportController::class, 'requestDeposit'])->name('webreport.deposit.request');
Route::post('/deposit/cancel',           [WebreportController::class, 'cancelDeposit'])->name('webreport.deposit.cancel');
Route::get('/prices',                    [WebreportController::class, 'prices'])->name('webreport.prices');
Route::post('/quick-action',             [WebreportController::class, 'quickAction'])->name('webreport.quick-action');
Route::get('/logo',                      [WebreportController::class, 'getLogo'])->name('webreport.logo');
Route::get('/api-docs',                  [WebreportController::class, 'apiDocs'])->name('webreport.api-docs');
Route::get('/settings',                  [WebreportController::class, 'settings'])->name('webreport.settings');
Route::post('/settings/send-otp',        [WebreportController::class, 'sendSettingsOtp'])->name('webreport.settings.send-otp');
Route::post('/settings/update',          [WebreportController::class, 'updateSettings'])->name('webreport.settings.update');

// ── Cetak Struk Transaksi (Public / Direct Link) ───────────────
Route::get('/struk/{trxid}',              [WebreportController::class, 'printReceipt'])->name('webreport.struk');
Route::get('/cetak-struk/{trxid}',        [WebreportController::class, 'printReceipt'])->name('webreport.cetak-struk');

// ── Admin Panel ───────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminController::class, 'loginView'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.post');
    Route::post('/logout',[AdminController::class, 'logout'])->name('logout');

    Route::get('/',                                    [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard',                           [AdminController::class, 'dashboard'])->name('dashboard.main');
    Route::get('/resellers',                           [AdminController::class, 'resellersView'])->name('resellers');
    Route::get('/resellers/export',                    [AdminController::class, 'resellersExport'])->name('resellers.export');
    Route::get('/resellers/profile/{kode}',            [AdminController::class, 'resellerProfileAjax'])->name('resellers.profile');
    Route::get('/reseller-profit',                     [AdminController::class, 'resellerProfitView'])->name('reseller-profit');
    Route::get('/reseller-profit/export',              [AdminController::class, 'resellerProfitExport'])->name('reseller-profit.export');
    Route::get('/reseller-profit/detail/{kode}',       [AdminController::class, 'resellerProfitDetailAjax'])->name('reseller-profit.detail');
    Route::get('/price-links',                         [AdminController::class, 'priceLinksView'])->name('price-links');
    Route::post('/price-links/create',                 [AdminController::class, 'createPriceLink'])->name('price-links.create');
    Route::post('/price-links/delete/{id}',            [AdminController::class, 'deletePriceLink'])->name('price-links.delete');

    Route::post('/branding/update',                    [AdminController::class, 'updateBranding'])->name('branding.update');
    Route::post('/logs/clear',                         [AdminController::class, 'clearSystemLogs'])->name('logs.clear');
    Route::get('/settings',                            [AdminController::class, 'settingsView'])->name('settings');
    Route::post('/settings/mail',                      [AdminController::class, 'updateMailSettings'])->name('settings.mail');
    Route::post('/settings/mail/test',                 [AdminController::class, 'testMailSettings'])->name('settings.mail.test');
    Route::post('/settings/bank',                      [AdminController::class, 'updateBankSettings'])->name('settings.bank');
    Route::post('/settings/otomax',                    [AdminController::class, 'updateOtomaxSettings'])->name('settings.otomax');
    Route::post('/settings/otomax/test',               [AdminController::class, 'testOtomaxConnection'])->name('settings.otomax.test');
    Route::post('/settings/security',                  [AdminController::class, 'updateSecuritySettings'])->name('settings.security');
    Route::post('/settings/apicenter',                 [AdminController::class, 'updateApiCenterSettings'])->name('settings.apicenter');
    Route::post('/settings/account',                   [AdminController::class, 'updateAccountSettings'])->name('settings.account');
    Route::post('/settings/receipt',                   [AdminController::class, 'updateReceiptSettings'])->name('settings.receipt');
    Route::post('/settings/receipt/rule',              [AdminController::class, 'storeReceiptRule'])->name('settings.receipt.rule');
    Route::post('/settings/receipt/rule/delete/{id}',  [AdminController::class, 'deleteReceiptRule'])->name('settings.receipt.rule.delete');
    Route::post('/settings/receipt/rule/reset',        [AdminController::class, 'resetReceiptRules'])->name('settings.receipt.rule.reset');
});
