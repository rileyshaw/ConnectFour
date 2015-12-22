<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('api/join_room', 'APIController@join_room');
Route::post('api/leave_room', 'APIController@leave_room');
Route::post('api/make_move', 'APIController@make_move');
Route::post('api/get_current_board', 'APIController@get_current_board');
Route::post('api/create_room', 'APIController@create_room');
Route::post('api/delete_room', 'APIController@delete_room');


Route::post('api/authenticate', 'APIController@authenticateUser');
Route::post('api/register', 'APIController@createUser');