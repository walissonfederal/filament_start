<?php

use App\Http\Controllers\PrivacyPolicyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Política de privacidade ---
Route::get('/privacy-policy', [
    PrivacyPolicyController::class,
    'privacyPolicy'
])->name('privacyPolicy');
