<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RespondentController;

Route::get('/', [RespondentController::class, 'create'])->name('respondent.create');
Route::post('/api/respondents/prepare', [RespondentController::class, 'prepare'])->name('respondent.prepare');
Route::post('/api/respondents/verify', [RespondentController::class, 'store'])->name('respondent.store');
