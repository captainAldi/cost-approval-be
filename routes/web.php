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
    return $router->app->version();
});

// OAuth Google

$router->get('oauth/google/login', 'AuthController@redirectToProvider');
$router->get('oauth/google/callback', 'AuthController@handleProviderCallback');


// API
$router->group(['prefix' => 'api/v1/' ], function() use ($router) {

    // -- ALL --


    // Logged In and Admin
     $router->group(['middleware' => ['login', 'admin'] ], function() use ($router) {
        $router->patch("/bill/approve/{id}", "BillController@approvePengajuan");   
    });

    // Logged In
     $router->group(['middleware' => ['login'] ], function() use ($router) {
        $router->post("/bill/add", "BillController@createPengajuan");  
        $router->get("/bill/get/waiting", "BillController@getPengajuan");   
        $router->get("/bill/get/approved", "BillController@getApproved");   
        $router->get("/files/invoice-tagihan/{namaFile}/{tipeFile}", "FilesController@showInvBill");

        
    });

});