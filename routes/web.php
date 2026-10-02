<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DuesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/kabar', [PostController::class, 'index'])->name('posts.index');
Route::get('/kabar/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/iuran', [DuesController::class, 'index'])->name('dues.index');
Route::get('/kas', [DuesController::class, 'cashbook'])->name('dues.cashbook');
Route::get('/bayar', [PayController::class, 'create'])->name('pay.create');
Route::post('/bayar', [PayController::class, 'store'])->middleware('throttle:10,1')->name('pay.store');
Route::get('/bayar/cek/{code}', [PayController::class, 'show'])->name('pay.show');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthController::class, 'store'])->middleware('throttle:6,1');
});
Route::post('/keluar', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\PaymentController::class, 'create'])->name('home');
    Route::get('/pembayaran', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/pembayaran', [Admin\PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/pembayaran/{payment}', [Admin\PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/buku-iuran', [Admin\PaymentController::class, 'ledger'])->name('payments.ledger');

    Route::get('/konfirmasi', [Admin\PaymentSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/konfirmasi/{submission}/bukti', [Admin\PaymentSubmissionController::class, 'proof'])->name('submissions.proof');
    Route::post('/konfirmasi/{submission}/terima', [Admin\PaymentSubmissionController::class, 'approve'])->name('submissions.approve');
    Route::post('/konfirmasi/{submission}/tolak', [Admin\PaymentSubmissionController::class, 'reject'])->name('submissions.reject');
    Route::post('/konfirmasi/{submission}/batal', [Admin\PaymentSubmissionController::class, 'cancel'])->name('submissions.cancel');
    Route::post('/konfirmasi/{submission}/buka-lagi', [Admin\PaymentSubmissionController::class, 'reopen'])->name('submissions.reopen');
    Route::resource('rekening', Admin\BankAccountController::class)->except('show')->parameters(['rekening' => 'bankAccount'])->names('bank-accounts');

    Route::resource('kabar', Admin\PostController::class)->except('show')->parameters(['kabar' => 'post'])->names('posts');
    Route::resource('rumah', Admin\HouseholdController::class)->except('show')->parameters(['rumah' => 'household'])->names('households');
    Route::get('/rumah/{household}/penghuni', [Admin\HouseholdMemberController::class, 'index'])->name('households.members.index');
    Route::post('/rumah/{household}/penghuni', [Admin\HouseholdMemberController::class, 'store'])->name('households.members.store');
    Route::put('/rumah/{household}/penghuni/{member}', [Admin\HouseholdMemberController::class, 'update'])->name('households.members.update');
    Route::delete('/rumah/{household}/penghuni/{member}', [Admin\HouseholdMemberController::class, 'destroy'])->name('households.members.destroy');
    Route::post('/rumah/{household}/penghuni/sync', [Admin\HouseholdMemberController::class, 'sync'])->name('households.members.sync');
    Route::resource('jenis-iuran', Admin\DuesTypeController::class)->except('show')->parameters(['jenis-iuran' => 'duesType'])->names('dues-types');
    Route::resource('pengeluaran', Admin\ExpenseController::class)->except('show')->parameters(['pengeluaran' => 'expense'])->names('expenses');

    Route::get('/pengaturan', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');
    Route::put('/pengaturan/sandi', [Admin\SettingController::class, 'password'])->name('settings.password');

    Route::get('/pengurus', [Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/pengurus', [Admin\UserController::class, 'store'])->name('users.store');
    Route::delete('/pengurus/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
});
