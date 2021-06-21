<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class CostApproveEmail extends Mailable
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      
        return $this->view('emails.cost.approve')
                    ->attach(storage_path('app/data-aplikasi/file-inv/' . $this->fileInv));
    }
}