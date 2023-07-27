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

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
//Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\AuthController::class, 'verifyEmail']);
//Route::get('/email/askverify/{id}/{hash}', [\App\Http\Controllers\AuthController::class, 'askNewVerifyEmail']); //->middleware('throttle:3,10');
Route::post('/password/reset', [\App\Http\Controllers\AuthController::class, 'resetPassword']);
Route::post('/password/askreset', [\App\Http\Controllers\AuthController::class, 'askResetPassword']); //->middleware('throttle:reset');

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', [\App\Http\Controllers\AuthController::class, 'user']);
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::put('/changepassword/{id?}', [\App\Http\Controllers\AuthController::class, 'changePassword']);

    Route::prefix('utility')->group(function () {
        Route::get('/get-staff-types', [\App\Http\Controllers\UtilityController::class, 'getStaffTypes']);
        Route::get('/get-staff-roles', [\App\Http\Controllers\UtilityController::class, 'getStaffRoles']);
        Route::get('/get-attendance-types', [\App\Http\Controllers\UtilityController::class, 'getAttendanceTypes']);
    });

    Route::prefix('staff')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\StaffController::class, 'index']);
        Route::get('{id}/get-staff', [\App\Http\Controllers\StaffController::class, 'getStaffEmployees']);
        Route::get('{id}/get-managers', [\App\Http\Controllers\StaffController::class, 'getManagers']);
        Route::put('{id}/set-staff', [\App\Http\Controllers\Admin\StaffController::class, 'setStaffEmployees']);
        Route::put('{id}/set-managers', [\App\Http\Controllers\Admin\StaffController::class, 'setManagers']);
        Route::get('{id}', [\App\Http\Controllers\StaffController::class, 'show']);
        Route::post('{id}/attachment', [\App\Http\Controllers\StaffController::class, 'uploadAttachment']);
        Route::post('/', [\App\Http\Controllers\Admin\StaffController::class, 'store']);
        Route::put('{id}/edit', [\App\Http\Controllers\StaffController::class, 'update']);
        Route::put('{id}', [\App\Http\Controllers\Admin\StaffController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy']);
        Route::put('{id}/activate-login', [\App\Http\Controllers\Admin\StaffController::class, 'activateLogin']);
        Route::put('{id}/disable-login', [\App\Http\Controllers\Admin\StaffController::class, 'disableLogin']);
        Route::put('{id}/disable', [\App\Http\Controllers\Admin\StaffController::class, 'disable']);
    });

    Route::prefix('communications')->group(function () {
        Route::get('/all', [\App\Http\Controllers\Admin\CommunicationController::class, 'index']);
        Route::get('/', [\App\Http\Controllers\CommunicationController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\CommunicationController::class, 'show']);
        Route::put('{id}/set-read-status', [\App\Http\Controllers\CommunicationController::class, 'setReadStatus']);
        Route::post('{id}/attachment', [\App\Http\Controllers\Admin\CommunicationController::class, 'uploadAttachment']);
        Route::post('/', [\App\Http\Controllers\Admin\CommunicationController::class, 'store']);
        Route::put('{id}/set-view-roles', [\App\Http\Controllers\Admin\CommunicationController::class, 'setViewRoles']);
        Route::put('{id}', [\App\Http\Controllers\Admin\CommunicationController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Admin\CommunicationController::class, 'destroy']);
    });

    Route::prefix('attendances')->group(function () {
        Route::get('/calendar', [\App\Http\Controllers\AttendanceController::class, 'calendar']);
        Route::get('/', [\App\Http\Controllers\AttendanceController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\AttendanceController::class, 'show']);
        Route::post('{id}/attachment', [\App\Http\Controllers\AttendanceController::class, 'uploadAttachment']);
        Route::post('/', [\App\Http\Controllers\AttendanceController::class, 'store']);
        Route::put('{id}', [\App\Http\Controllers\AttendanceController::class, 'update']);
        Route::put('{id}/accepted', [\App\Http\Controllers\Admin\AttendanceController::class, 'accepted']);
        Route::put('{id}/reset', [\App\Http\Controllers\Admin\AttendanceController::class, 'reset']);
        Route::delete('{id}', [\App\Http\Controllers\AttendanceController::class, 'destroy']);
        Route::post('/export-download', [\App\Http\Controllers\Admin\AttendanceController::class, 'export']);
    });

    Route::prefix('attachment')->group(function () {
        Route::get('/', [\App\Http\Controllers\AttachmentController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\AttachmentController::class, 'show']);
        Route::put('{id}', [\App\Http\Controllers\AttachmentController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\AttachmentController::class, 'destroy']);
        Route::get('{id}/text-ocr', [\App\Http\Controllers\AttachmentController::class, 'getTextWithOCR']);
    });

    Route::prefix('paysheet')->group(function () {
        Route::get('/', [\App\Http\Controllers\PaysheetController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\PaysheetController::class, 'show']);
        Route::put('{id}/set-downloaded', [\App\Http\Controllers\PaysheetController::class, 'setDownloaded']);
        Route::post('/upload', [\App\Http\Controllers\Admin\PaysheetController::class, 'upload']);
        Route::post('/store', [\App\Http\Controllers\Admin\PaysheetController::class, 'store']);
        Route::delete('{id}', [\App\Http\Controllers\Admin\PaysheetController::class, 'destroy']);
    });
});
