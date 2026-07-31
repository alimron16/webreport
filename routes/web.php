<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebreportController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AdminController;

// ── Member / Reseller Portal ─────────────────────────────────
Route::get('/login',  [WebreportController::class, 'loginView'])->name('webreport.login');
Route::post('/login', [WebreportController::class, 'login'])->name('webreport.login.post');
Route::post('/logout',[WebreportController::class, 'logout'])->name('webreport.logout');

// Registration
Route::get('/register',           [RegistrationController::class, 'show'])->name('webreport.register');
Route::post('/register',          [RegistrationController::class, 'store'])->name('webreport.register.post');
Route::post('/register/send-otp',  [RegistrationController::class, 'sendOtp'])->name('webreport.register.send-otp');
Route::get('/register/template',  [RegistrationController::class, 'downloadTemplate'])->name('webreport.register.template');

// Authenticated member pages
Route::get('/',                          [WebreportController::class, 'dashboard'])->name('webreport.dashboard');
Route::get('/transactions',              [WebreportController::class, 'transactions'])->name('webreport.transactions');
Route::get('/transactions/{trxid}',      [WebreportController::class, 'transactionDetail'])->name('webreport.transactions.detail');
Route::get('/deposit',                   [WebreportController::class, 'deposit'])->name('webreport.deposit');
Route::post('/deposit/request',          [WebreportController::class, 'requestDeposit'])->name('webreport.deposit.request');
Route::post('/deposit/cancel',           [WebreportController::class, 'cancelDeposit'])->name('webreport.deposit.cancel');
Route::post('/pending/upload',           [WebreportController::class, 'uploadDocuments'])->name('webreport.pending.upload');
Route::get('/prices',                    [WebreportController::class, 'prices'])->name('webreport.prices');
Route::post('/quick-action',             [WebreportController::class, 'quickAction'])->name('webreport.quick-action');
Route::get('/logo',                      [WebreportController::class, 'getLogo'])->name('webreport.logo');
Route::get('/api-docs',                  [WebreportController::class, 'apiDocs'])->name('webreport.api-docs');
Route::get('/settings',                  [WebreportController::class, 'settings'])->name('webreport.settings');
Route::post('/settings/send-otp',        [WebreportController::class, 'sendSettingsOtp'])->name('webreport.settings.send-otp');
Route::post('/settings/update',          [WebreportController::class, 'updateSettings'])->name('webreport.settings.update');

// ── Admin Panel ───────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminController::class, 'loginView'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.post');
    Route::post('/logout',[AdminController::class, 'logout'])->name('logout');

    Route::get('/',                                    [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/registrations/{id}',                  [AdminController::class, 'detail'])->name('registrations.detail');
    Route::get('/registrations/{id}/berkas',           [AdminController::class, 'downloadBerkas'])->name('registrations.berkas');
    Route::post('/registrations/{id}/approve',         [AdminController::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{id}/resend-otomax',    [AdminController::class, 'resendToOtomax'])->name('registrations.resend-otomax');
    Route::post('/registrations/{id}/reject',          [AdminController::class, 'reject'])->name('registrations.reject');
    Route::post('/pks/upload',                         [AdminController::class, 'uploadPks'])->name('pks.upload');
    Route::post('/branding/update',                    [AdminController::class, 'updateBranding'])->name('branding.update');
    Route::post('/logs/clear',                         [AdminController::class, 'clearSystemLogs'])->name('logs.clear');
    Route::get('/settings',                            [AdminController::class, 'settingsView'])->name('settings');
    Route::post('/settings/mail',                      [AdminController::class, 'updateMailSettings'])->name('settings.mail');
    Route::post('/settings/mail/test',                 [AdminController::class, 'testMailSettings'])->name('settings.mail.test');
    Route::post('/settings/bank',                      [AdminController::class, 'updateBankSettings'])->name('settings.bank');
    Route::post('/settings/otomax',                    [AdminController::class, 'updateOtomaxSettings'])->name('settings.otomax');
    Route::post('/settings/security',                  [AdminController::class, 'updateSecuritySettings'])->name('settings.security');
});
