<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/attachments/{id}/inline', [\App\Http\Controllers\AttachmentWebController::class, 'inline'])->name('web.attachment.inline')->middleware('signed');
Route::get('/attachments/{id}/preview', [\App\Http\Controllers\AttachmentWebController::class, 'thumbnail'])->name('web.attachment.preview')->middleware('signed');
Route::get('/attachments/{id}/download', [\App\Http\Controllers\AttachmentWebController::class, 'download'])->name('web.attachment.download')->middleware('signed');

Route::get('/paysheets/file/{filename}/preview', [\App\Http\Controllers\PaysheetWebController::class, 'file'])->name('web.paysheet.file.preview')->middleware('signed');
Route::get('/paysheets/thumb/{thumbname}/preview', [\App\Http\Controllers\PaysheetWebController::class, 'thumbnail'])->name('web.paysheet.thumb.preview')->middleware('signed');

Route::post('/reset-password-go', [\App\Http\Controllers\AuthController::class, 'resetPassword'])->name('password.reset');

Route::get('/{vue_capture?}', function () {
    return view('app');
})->where('vue_capture', '[\/\w\.-]*');
