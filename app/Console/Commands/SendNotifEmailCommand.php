<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

USE App\Jobs\SendEmailCostApproveJob;

use DB;
use Carbon\Carbon;
use App\Models\BillApprover;
use App\Models\Bill;
use App\Models\User;


class SendNotifEmailCommand extends Command
{
    
    protected $signature = 'send:notif-email';

    
    protected $description = 'Send Notif E-Mail to All Approver that not yet Approve Bills';

    
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        
        // Get Waiting Approver
        $dataWaitingApprover = BillApprover::where('status', 'Waiting')
                                ->get();
        
        // Send Notifikasi ke Telegram
         foreach ($dataWaitingApprover as $key) {
            
            $dataWaitingBill = Bill::where('id', $key->bill_id)->first();
            $dataUser        = User::where('email', $key->email)->first();

            $chatID         = $dataUser->chat_id_telegram;
            $judul          = $dataWaitingBill->judul;
            $pembuka        = $dataUser->name;
            $deskripsi      = $dataWaitingBill->deskripsi;
            $fileInv        = $dataWaitingBill->file_inv;
            $bu             = $dataWaitingBill->bu;
            $bi             = $dataWaitingBill->business_initiative;
            $tgl_jatuh_tempo = Carbon::parse($dataWaitingBill->tanggal_jatuh_tempo)->format('d-F-Y'); 
            $jumlah_tagihan  = number_format($dataWaitingBill->jumlah_tagihan,2,',','.');
            $transaksi_berulang = $dataWaitingBill->transaksi_berulang;
            $nama_pt         = $dataWaitingBill->nama_pt;
            $approveLink    = env('VUE_APP_URL').'/otr/bill/approve/'.$key->remember_token;

            dispatch(new SendEmailCostApproveJob(
                $dataUser->email, 
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
            );

        }

    }
}