<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\admin\StatsController;
use App\Http\Controllers\Auth\CustomForgotPasswordController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController as ControllersHomeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

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


Route::get('', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [CustomLoginController::class, 'login'])->name('login.submit');
Route::get('registration', [Controller::class, 'registration'])->name('registration');
Route::post('check_matricule', [Controller::class, 'check_matricule'])->name('check_matricule');
Route::post('createAccount', [Controller::class, 'createAccount'])->name('createAccount');
Route::post('logout', [CustomLoginController::class, 'logout'])->name('logout');

Route::post('reset_password_with_token/password/reset', [CustomForgotPasswordController::class, 'validatePasswordRequest'])->name('reset_password_without_token');
Route::get('reset_password_with_token/{token}/{email}', [CustomForgotPasswordController::class, 'resetForm'])->name('reset');

Route::prefix('admin')->name('admin.')->middleware('isAdmin')->group(function () {
    Route::get('', 'Admin\HomeController@index')->name('home');
    Route::get('home', 'Admin\HomeController@index')->name('home');
    Route::get('setayear', 'Admin\HomeController@setayear')->name('setayear');
    Route::post('setayear/{id}', 'Admin\HomeController@setAcademicYear')->name('createacademicyear');
    Route::get('students', [HomeController::class, 'students'])->name('schools.students');
    Route::get('students/import', [HomeController::class, 'import_students'])->name('schools.students.import');
    Route::post('students/import', [HomeController::class, 'save_students']);
    Route::get('students/download', [HomeController::class, 'download_students'])->name('schools.download_students');
    Route::post('students/download', [HomeController::class, 'download_students_save']);

    Route::get('students/printed', [HomeController::class, 'printed_ids_index'])->name('printed_ids');
    Route::post('students/printed', [HomeController::class, 'download_printed_ids']);

    Route::prefix('stats')->name('stats.')->group(function(){
        Route::get('index', [StatsController::class, 'index'])->name('index');
    });

    Route::get('reset_password', 'Controller@reset_password')->name('reset_password');
    Route::post('reset_password', 'Controller@reset_password_save')->name('reset_password');

    Route::get('reset_student_data{id}', [HomeController::class, 'reset_student_data'])->name('reset_student_data');
});


Route::prefix('student')->name('student.')->middleware('isStudent')->group(function () {
    Route::get('', 'Student\HomeController@index')->name('home');
    Route::get('edit_profile', 'Student\HomeController@edit_profile')->name('edit_profile');
    Route::post('update_profile', 'Student\HomeController@update_profile')->name('update_profile');
    Route::post('update', 'Student\HomeController@update')->name('update');
    Route::get('drop_image', 'Student\HomeController@drop_image')->name('drop_image');
    Route::post('add_image', 'Student\HomeController@update_image_save');
    Route::get('reset_password', 'Controller@reset_password')->name('reset_password');
    Route::post('reset_password', 'Controller@reset_password_save')->name('reset_password');
});

Route::get('search/students/boarders/{name}', 'HomeController@getStudentBoarders')->name('getStudentBoarder');

Route::get('search_students', [ControllersHomeController::class, 'search_students'])->name('search_students');
Route::get('getColor/{label}', [HomeController::class, 'getColor'])->name('getColor');


Route::get('mode/{locale}', function ($batch) {
    session()->put('mode', $batch);

    return redirect()->back();
})->name('mode');
