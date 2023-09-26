<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ResultsAndTranscriptsController;
use App\Http\Controllers\admin\StockController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\CustomForgotPasswordController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\documentation\BaseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Student\HomeController as StudentHomeController;
use App\Http\Controllers\Teacher\HomeController as TeacherHomeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Transactions;
use App\Http\Resources\SubjectResource;
use App\Http\Services\MailService;
use App\Models\Resit;
use App\Models\StudentSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use \App\Models\Subjects;

Route::get('/clear', function () {
    echo Session::get('applocale');
    $clearcache = Artisan::call('cache:clear');
    echo "Cache cleared<br>";

    $clearview = Artisan::call('view:clear');
    echo "View cleared<br>";

    $clearconfig = Artisan::call('config:cache');
    echo "Config cleared<br>";

});

Route::get('set_local/{lang}', [Controller::class, 'set_local'])->name('lang.switch');


Route::post('login', [CustomLoginController::class, 'login'])->name('login.submit');
Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::get('registration', [Controller::class, 'registration'])->name('registration');
Route::post('check_matricule', [Controller::class, 'check_matricule'])->name('check_matricule');
Route::post('createAccount', [Controller::class, 'createAccount'])->name('createAccount');
Route::post('logout', [CustomLoginController::class, 'logout'])->name('logout');

Route::post('reset_password_with_token/password/reset', [CustomForgotPasswordController::class, 'validatePasswordRequest'])->name('reset_password_without_token');
Route::get('reset_password_with_token/{token}/{email}', [CustomForgotPasswordController::class, 'resetForm'])->name('reset');
Route::post('reset_password_with_token', [CustomForgotPasswordController::class, 'resetPassword'])->name('reset_password_with_token');
Route::post('recover_username', [CustomForgotPasswordController::class, 'recover_username'])->name('recover_username');

Route::prefix('admin')->name('admin.')->middleware('isAdmin')->group(function () {
    Route::get('', 'Admin\HomeController@index')->name('home');
    Route::get('home', 'Admin\HomeController@index')->name('home');
    Route::get('setayear', 'Admin\HomeController@setayear')->name('setayear');
    Route::post('setayear/{id}', 'Admin\HomeController@setAcademicYear')->name('createacademicyear');
    Route::get('units/{parent_id}/student', 'Admin\ProgramController@students')->name('students.index');
});


Route::prefix('student')->name('student.')->middleware('isStudent')->group(function () {
    Route::get('', 'Student\HomeController@index')->name('home');
    Route::get('edit_profile', 'Student\HomeController@edit_profile')->name('edit_profile');
    Route::post('update_profile', 'Student\HomeController@update_profile')->name('update_profile');
    Route::get('reset_password', 'Controller@reset_password')->name('reset_password');
    Route::post('reset_password', 'Controller@reset_password_save')->name('reset_password');
});

Route::get('search/students/boarders/{name}', 'HomeController@getStudentBoarders')->name('getStudentBoarder');


Route::get('getColor/{label}', [HomeController::class, 'getColor'])->name('getColor');


Route::get('mode/{locale}', function ($batch) {
    session()->put('mode', $batch);

    return redirect()->back();
})->name('mode');


