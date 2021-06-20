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
    public $chatID; 
    public $judul; 
    public $pembuka; 
    public $deskripsi; 
    public $fileInv; 
    public $approveLink; 
    public $bu; 
    public $bi; 
    public $tgl_jatuh_tempo; 
    public $jumlah_tagihan; 
    public $transaksi_berulang; 
    public $nama_pt;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $chatID, 
        $judul, 
        $pembuka, 
        $deskripsi, 
        $fileInv, 
        $approveLink, 
        $bu, 
        $bi, 
        $tgl_jatuh_tempo, 
        $jumlah_tagihan, 
        $transaksi_berulang, 
        $nama_pt)
    {
        $this->chatID         = $chatID;
        $this->judul          = $judul;
        $this->pembuka        = $pembuka;
        $this->deskripsi      = $deskripsi;
        $this->fileInv        = $fileInv;
        $this->approveLink    = $approveLink;
        $this->bu             = $bu;
        $this->bi             = $bi;
        $this->tgl_jatuh_tempo  = $tgl_jatuh_tempo;
        $this->jumlah_tagihan   = $jumlah_tagihan;
        $this->transaksi_berulang = $transaksi_berulang;
        $this->nama_pt = $nama_pt;
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
            $chat_bu           = '<b>Business Unit: </b>'.$this->bu;
            $chat_bi           = '<b>Business Initiative: </b>'.$this->bi;
            $chat_nama_pt      = '<b>Nama PT: </b>'.$this->nama_pt;
            $chat_tgl_jatuh_tempo = '<b>Tanggal Tempo: </b>'.$this->tgl_jatuh_tempo;
            $chat_transaksi_berulang = '<b>Transaksi Berulang ? : </b>'.$this->transaksi_berulang;
            $chat_jumlah_tagihan = '<b>Jumlah Tagihan: </b>'. 'Rp ' . $this->jumlah_tagihan;
            $chat_deskripsi    = $this->deskripsi;
            $chat_id           = $this->chatID;

            // Send First Chat
            $response = $telegram->sendMessage([
                'chat_id' => $chat_id, 
                'text' => $chat_judul . "\n\n" .
                             $chat_pembuka . "\n\n" .
                             $chat_nama_pt . "\n" . 
                             $chat_bu . "\n" . 
                             $chat_bi . "\n\n" . 
                             $chat_tgl_jatuh_tempo . "\n" .
                             $chat_transaksi_berulang . "\n" . 
                             $chat_jumlah_tagihan . "\n\n" . 
                             $chat_deskripsi,
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
