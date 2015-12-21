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
Route::post('createroom', 'APIController@createroom'); //WHY THE ACTUAL FUCK IS THIS NOT WORKING HELP
//gives token mismatch error and i have no idea what this is google didnt help wtf kill me







