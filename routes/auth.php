<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

        Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:register');
    Route::post('register/otp/send', [\App\Http\Controllers\Auth\OtpAuthController::class, 'sendRegister'])
        ->middleware('throttle:5,1')
        ->name('register.otp.send');

    Route::post('register/otp/verify', [\App\Http\Controllers\Auth\OtpAuthController::class, 'verifyRegister'])
        ->middleware('throttle:10,1')
        ->name('register.otp.verify');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login');
    

    Route::post('login/otp/send', [\App\Http\Controllers\Auth\OtpAuthController::class, 'sendLogin'])
        ->middleware('throttle:5,1')
        ->name('login.otp.send');

    Route::post('login/otp/verify', [\App\Http\Controllers\Auth\OtpAuthController::class, 'verifyLogin'])
        ->middleware('throttle:10,1')
        ->name('login.otp.verify');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('logout', function () {
        return view('auth.logout');
    })->name('logout.confirm');

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
