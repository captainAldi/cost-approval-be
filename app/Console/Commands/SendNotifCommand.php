<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\SendChatJob;
use Ramsey\Uuid\Uuid;

use DB;
use App\Models\BillApprover;
use App\Models\Bill;
use App\Models\User;

class SendNotifCommand extends Command
{
    
    protected $signature = 'send:notif';

    
    protected $description = 'Send Notif to All Approver that not yet Approve Bills';

    
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
            $approveLink    = env('VUE_APP_URL').'/otr/bill/approve/'.$key->remember_token;

            dispatch(new SendChatJob(
                $chatID, 
                $judul, 
                $pembuka, 
                $deskripsi, 
                $fileInv, 
                $approveLink,
                $bu,
                $bi)
            );

        }

    }
}