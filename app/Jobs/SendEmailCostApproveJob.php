<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Mail\CostApproveEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendEmailCostApproveJob extends Job
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    public $emailUser; 
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
        $emailUser, 
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
        $this->emailUser      = $emailUser;
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
        Redis::throttle('cost-approval-email')->allow(4)->every(1)->then(function () {

            Mail::to($this->emailUser
                )->send(new CostApproveEmail(
                    $this->judul,
                    $this->pembuka,
                    $this->deskripsi,
                    $this->fileInv,
                    $this->approveLink,
                    $this->bu,
                    $this->bi,
                    $this->tgl_jatuh_tempo,
                    $this->jumlah_tagihan,
                    $this->transaksi_berulang,
                    $this->nama_pt)
                );

        }, function () {
            // Could not obtain lock; this job will be re-queued
            return $this->release(4);
        });
    }
}
