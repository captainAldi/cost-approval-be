<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\FileUpload\InputFile;

class SendChatJob extends Job
{
    use InteractsWithQueue, Queueable, SerializesModels;

    // public $chatID;
    public $chatID, $judul, $pembuka, $deskripsi, $fileInv, $approveLink;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chatID, $judul, $pembuka, $deskripsi, $fileInv, $approveLink)
    {
        $this->chatID         = $chatID;
        $this->judul          = $judul;
        $this->pembuka        = $pembuka;
        $this->deskripsi      = $deskripsi;
        $this->fileInv        = $fileInv;
        $this->approveLink    = $approveLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Allow only 2 chats every 1 second
        Redis::throttle('cost-approval')->allow(4)->every(1)->then(function () {

            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

            $chat_judul        = '<b>'.$this->judul.'</b>';
            $chat_pembuka      = 'Halo '.$this->pembuka.' Ada Tagihan yang Harus di Review nih !';
            $chat_deskripsi    = $this->deskripsi;
            $chat_id           = $this->chatID;

            // Send First Chat
            $response = $telegram->sendMessage([
                'chat_id' => $chat_id, 
                'text' => $chat_judul . "\n\n" . $chat_pembuka . "\n\n" . $chat_deskripsi,
                'parse_mode' => 'HTML',
            ]);

             // Send File Inv Also
            $chat_fileInv        = storage_path('app/data-aplikasi/file-inv/'.$this->fileInv);
            $dataInlineKB = json_encode([//Because its object
                'inline_keyboard'=>[
                    [
                        ['text'=>'Approve', 'url' => $this->approveLink]
                    ],
                ]
            ]);

            $response2 = $telegram->sendDocument([
                'chat_id' => $chat_id, 
                'document' => InputFile::create($chat_fileInv, $this->fileInv),
                'caption'  => 'File Invoice',
                'reply_markup'  => $dataInlineKB
            ]);


        }, function () {
            // Could not obtain lock; this job will be re-queued
            return $this->release(4);
        });
    }
}
