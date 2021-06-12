<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Redis;

use App\Jobs\SendChatJob;
use Telegram\Bot\FileUpload\InputFile;

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

// Cek Redis
$router->get('/redis', function () use ($router) {
    try{
        $redis = Redis::connect('127.0.0.1',6379);
        return response('redis working');
    }catch(\Predis\Connection\ConnectionException $e){
        return response('error connection redis');
    }
});

// tes telegram
$router->get('/telegram', function () use ($router) {
    $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

    $response = $telegram->getUpdates(); 

    return response()->json([
        'data' => $response
    ], 200);
});


// OAuth Google

$router->get('oauth/google/login', 'AuthController@redirectToProvider');
$router->get('oauth/google/callback', 'AuthController@handleProviderCallback');


// API
$router->group(['prefix' => 'api/v1/' ], function() use ($router) {

    // -- ALL --
    $router->get("/otr/bill/approve/{remember_token}", "BillController@approvePengajuanWithToken");   
    $router->get("/approvers/name/get", "BillController@getApproversName");   


    // Logged In and Admin
     $router->group(['middleware' => ['login', 'finance'] ], function() use ($router) {
        $router->patch("/bill/pay/{id}", "BillController@payPengajuan");   
    });

    // Logged In and Approver
     $router->group(['middleware' => ['login', 'approver'] ], function() use ($router) {
        $router->patch("/bill/approve/{id}", "BillController@approvePengajuan");   
    });

    // Logged In
     $router->group(['middleware' => ['login'] ], function() use ($router) {
        

        $router->post("/bill/add", "BillController@createPengajuan");  
        $router->get("/bill/get/waiting", "BillController@getPengajuan");   
        $router->get("/bill/get/approved", "BillController@getApproved");   
        $router->get("/bill/get/paid", "BillController@getPaid");   

        $router->get("/files/invoice-tagihan/{namaFile}/{tipeFile}", "FilesController@showInvBill");
        $router->get("/files/bukti-pembayaran/{namaFile}/{tipeFile}", "FilesController@showBP");

        
    });

});