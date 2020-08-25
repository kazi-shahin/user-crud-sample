<?php

use Illuminate\Support\Facades\Route;

//Route::group(['namespace' => 'Modules\user\Controllers', 'middleware' => ['web', 'userAuth' ,'installerComplete']], function () {
Route::group(['namespace' => 'Modules\user\Controllers', 'middleware' => ['web', 'installerComplete', 'customAuthentication']], function () {
    Route::get('user', 'UserController@index');
    Route::get('create_user', 'UserController@createUser')->name('create-application-user');
    Route::post('store_user', 'UserController@storeUser')->name('store-application-user');
    Route::get('edit_user/{id}', 'UserController@editUser')->name('edit-application-user');
    Route::post('user_detail_ajax', 'UserController@usserDetails')->name('get-user-details');
    Route::post('update_user', 'UserController@updateUser')->name('update-application-user');
    Route::post('update_user_status', 'UserController@updateUserStatus')->name('update-application-user-status');
    Route::post('users/store_badge', 'UserController@storeUserBadge')->name('store-user-badge');
    Route::post('users/delete_badge', 'UserController@deleteUserBadge')->name('delete-user-badge');
    Route::post('users/reset_password_invite/{id}', 'UserController@resetPasswordInvitation')->name('reset_password_invite');

});