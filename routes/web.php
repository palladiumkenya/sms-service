<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    //return $router->app->version();
   return 'Just Another API - SMS Service Gateway';
});


$router->group(['prefix' => 'api'], function () use ($router) {
  $router->get('/getSMSReport',  ['middleware'=> 'auth','uses' => 'SMSController@SMSReports']);
  $router->get('/blacklist/{id}', ['middleware'=> 'auth', 'uses' => 'SMSController@checkBlacklist']);
  $router->post('/sender', ['middleware'=>'auth','uses' => 'SMSController@sendSMS']);
  $router->post('/receiver', ['uses' => 'SMSController@CallBack']);
});
