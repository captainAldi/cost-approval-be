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

    // $fileInv        = storage_path('app/data-aplikasi/file-inv/778719231-DigitalOcean-Invoice-2021-Apr-(2195650-424548830).pdf');
    // $streamFileInv  = response()->download($fileInv, 'file-invoice.pdf', [], 'inline');

    // return $streamFileInv;
    
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

    $judul        = '<b>Ini Judul</b>';
    $pembuka      = 'Halo nama';
    $deskripsi    = 'Ini Deskripsi';
    $fileInv      = '<a href="https://www.google.com/">inline URL Lihat Invoince</a>';
    $linkApproval = '<a href="https://www.google.com/">inline URL Approve</a>';

    $dataInlineKB = json_encode([//Because its object
        'inline_keyboard'=>[
            [
                ['text'=>'Lihat Invoice', 'url'=>'https://www.google.com/'],
                ['text'=>'Approve', 'url'=>'https://www.google.com/']
            ],
        ]
    ]);

    $response = $telegram->sendMessage([
        'chat_id' => env('TELEGRAM_CHAT_ID'), 
        // 'text' => $judul . "\n" . $deskripsi . "\n" . $fileInv ."\n" . $linkApproval,
        'text' => $judul . "\n\n" . $pembuka . "\n\n" . $deskripsi,
        'parse_mode' => 'HTML',
        'reply_markup' => $dataInlineKB

    ]);

    $messageId = $response->getMessageId();

    return response()->json([
        'data' => $messageId
    ], 200);
});

// sendMessages with Job
$router->get('/telegram/jobs', function () use ($router) {

    $chatID         = env('TELEGRAM_CHAT_ID');
    $judul          = 'Tagihan Digital Ocean - Bulan 1 - 2020';
    $pembuka        = 'Rawis';
    $deskripsi      = 'Berikut saya lampirkan PDF Invoice nya';
    $fileInv        = '778719231-DigitalOcean-Invoice-2021-Apr-(2195650-424548830).pdf';
    $approveLink    = env('VUE_APP_URL').'/otr/bill/approve/token';

    dispatch(new SendChatJob(
        $chatID, 
        $judul, 
        $pembuka, 
        $deskripsi, 
        $fileInv, 
        $approveLink)
    );

    return response()->json([
        'message' => 'Data Berhasil di Kirim via Jobs !',
    ], 200);
});

// tes telegram document
$router->get('/telegram/document', function () use ($router) {
    $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

    $judul        = '<b>Ini Judul</b>';
    $pembuka      = 'Halo nama';
    $deskripsi    = 'Ini Deskripsi';

    // Send First Chat
    $response = $telegram->sendMessage([
        'chat_id' => env('TELEGRAM_CHAT_ID'), 
        'text' => $judul . "\n\n" . $pembuka . "\n\n" . $deskripsi,
        'parse_mode' => 'HTML',

    ]);

    // Send File Inv Also
    $fileInv        = storage_path('app/data-aplikasi/file-inv/778719231-DigitalOcean-Invoice-2021-Apr-(2195650-424548830).pdf');
    $dataInlineKB = json_encode([//Because its object
        'inline_keyboard'=>[
            [
                ['text'=>'Approve', 'url'=>'https://www.google.com/']
            ],
        ]
    ]);

    $response2 = $telegram->sendDocument([
        'chat_id' => env('TELEGRAM_CHAT_ID'), 
        // 'text' => $judul . "\n" . $deskripsi . "\n" . $fileInv ."\n" . $linkApproval,
        'document' => InputFile::create($fileInv, '778719231-DigitalOcean-Invoice-2021-Apr-(2195650-424548830).pdf'),
        'caption'  => 'File Invoice',
        'reply_markup'  => $dataInlineKB
    ]);


    $messageId1 = $response->getMessageId();
    $messageId2 = $response->getOk();

    return response()->json([
        'data' => [
            $messageId1, 
            $messageId2
        ]
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