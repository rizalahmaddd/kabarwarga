<?php

use App\Http\Controllers\Api\V1;
use App\Http\Controllers\Api\V1\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/home', V1\HomeController::class)->name('home');
    Route::get('/settings', V1\SettingController::class)->name('settings');
    Route::get('/posts', [V1\PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post}', [V1\PostController::class, 'show'])->name('posts.show');
    Route::get('/households', [V1\ReferenceController::class, 'households'])->name('households.index');
    Route::get('/dues-types', [V1\ReferenceController::class, 'duesTypes'])->name('dues-types.index');
    Route::get('/bank-accounts', [V1\ReferenceController::class, 'bankAccounts'])->name('bank-accounts.index');
    Route::get('/dues/ledger', [V1\DuesController::class, 'ledger'])->name('dues.ledger');
    Route::get('/dues/cashbook', [V1\DuesController::class, 'cashbook'])->name('dues.cashbook');
    Route::get('/households/{household}/dues/{duesType}', [V1\DuesController::class, 'status'])->name('dues.status');
    Route::post('/payment-submissions', [V1\PaymentSubmissionController::class, 'store'])->middleware('throttle:10,1')->name('payment-submissions.store');
    Route::get('/payment-submissions/{code}', [V1\PaymentSubmissionController::class, 'show'])->name('payment-submissions.show');

    Route::post('/auth/login', [V1\AuthController::class, 'login'])->middleware('throttle:6,1')->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [V1\AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [V1\AuthController::class, 'logout'])->name('auth.logout');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
            Route::post('/payments', [Admin\PaymentController::class, 'store'])->name('payments.store');
            Route::delete('/payments/{payment}', [Admin\PaymentController::class, 'destroy'])->name('payments.destroy');

            Route::get('/payment-submissions', [Admin\PaymentSubmissionController::class, 'index'])->name('payment-submissions.index');
            Route::get('/payment-submissions/{submission}', [Admin\PaymentSubmissionController::class, 'show'])->name('payment-submissions.show');
            Route::get('/payment-submissions/{submission}/proof', [Admin\PaymentSubmissionController::class, 'proof'])->name('payment-submissions.proof');
            Route::post('/payment-submissions/{submission}/approve', [Admin\PaymentSubmissionController::class, 'approve'])->name('payment-submissions.approve');
            Route::post('/payment-submissions/{submission}/reject', [Admin\PaymentSubmissionController::class, 'reject'])->name('payment-submissions.reject');
            Route::post('/payment-submissions/{submission}/cancel', [Admin\PaymentSubmissionController::class, 'cancel'])->name('payment-submissions.cancel');
            Route::post('/payment-submissions/{submission}/reopen', [Admin\PaymentSubmissionController::class, 'reopen'])->name('payment-submissions.reopen');

            Route::get('/posts', [Admin\PostController::class, 'index'])->name('posts.index');
            Route::post('/posts', [Admin\PostController::class, 'store'])->name('posts.store');
            Route::get('/posts/{post:id}', [Admin\PostController::class, 'show'])->name('posts.show');
            Route::put('/posts/{post:id}', [Admin\PostController::class, 'update'])->name('posts.update');
            Route::delete('/posts/{post:id}', [Admin\PostController::class, 'destroy'])->name('posts.destroy');

            Route::apiResource('households', Admin\HouseholdController::class);
            Route::apiResource('dues-types', Admin\DuesTypeController::class)->parameters(['dues-types' => 'duesType']);
            Route::apiResource('expenses', Admin\ExpenseController::class);
            Route::apiResource('bank-accounts', Admin\BankAccountController::class)->parameters(['bank-accounts' => 'bankAccount']);

            Route::get('/settings', [Admin\SettingController::class, 'show'])->name('settings.show');
            Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
            Route::put('/password', [Admin\SettingController::class, 'password'])->name('password.update');

            Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        });
    });
});
