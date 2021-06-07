<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bill;
use App\Models\BillApprover;
use App\Models\User;

use DB;

class BillController extends Controller
{
    
    public function createPengajuan(Request $request)
    {

         // Pesan Jika Error
        $messages = [
            'judul.required'   => 'Masukkan Judul Tagihan !',
            'deskripsi.required'   => 'Masukkan Deskripsi Tagihan !',
            'bu.required'   => 'Pilih Business Unit !',
            'file_inv.required'   => 'Upload File Invoice !',
            'approver_email.required'   => 'Masukkan Approver !',
            'approver_email.*.email'   => 'Masukkan Email dengan Benar !',
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'judul'             => 'required',
            'deskripsi'         => 'required',
            'file_inv'          => 'required',
            'bu'                => 'required',
            'approver_email.*'  => 'required|email'
        ], $messages);

         // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Get Data Inputan
        $judul = $request->input('judul');
        $deskripsi = $request->input('deskripsi');
        $file_inv = $request->input('file_inv');
        $bu = $request->input('bu');
        $status = 'Waiting';
        $pengaju_id = $user->id;

        // Simpan 
        $dataBill = new Bill();
        $dataBill->judul = $judul;
        $dataBill->deskripsi = $deskripsi;
        $dataBill->bu = $bu;
        $dataBill->status = $status;
        $dataBill->pengaju_id = $pengaju_id;

        // Untuk File
            // Nama Asli File
            $fileNameOriginal = str_replace(' ', '-', $request->fileName);

            //Naming
            $fileInvName  = rand().'-'.$fileNameOriginal;

            //Ekstensi
            $getFileExt     = explode('.', $fileNameOriginal);
            $file_ext       = end($getFileExt);

            $tipeValid      = [
                'pdf', 'doc', 'docx', 'xls', 'xlsx'
            ];

            foreach ($tipeValid as $tipe) {
                if (!in_array($file_ext, $tipeValid)) {
                    return response()->json([
                        'message' => 'Tipe File Tidak di Dukung !'
                    ], 415);
                }
            }

            // $fileInvName = $request->file_inv->getClientOriginalName();

            $request->file('file_inv')->storeAs('data-aplikasi/file-inv', $fileInvName);

            $dataBill->file_inv = $fileInvName;

        
        $dataBill->save();

        // -- Untuk Bill Approver --

        // Get Data Inputan
        $approver_email = $request->input('approver_email');

        // Simpan 
        foreach ($approver_email as $key => $value) {

            $billApprover = new BillApprover();

            $billApprover->bill_id  = $dataBill->id;
            $billApprover->email = $value;
            $billApprover->status = 'Waiting';

            $billApprover->save();

        }
        

        return response()->json([
            'message' => 'Tagihan Telah di Ajukan !',
            'data'  => $dataBill
        ], 201);


    }

    public function approvePengajuan(Request $request, $id)
    {
        // Tagihan
        $dataBill = Bill::where('id', $id)->first();

        // Tagihan Approver
        $dataBillApprover = BillApprover::where('bill_id', $id)->first();

        if (!$dataBill || !$dataBillApprover) {
            return response()->json([
                'message' => 'Pengajuan Tidak Ada !',
            ], 403);
        }

        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // -- Data Bill Approver --
        $specificBillApprover = BillApprover::where('bill_id', $id)->where('email', $user->email)->first();
        $specificBillApprover->status = 'Approved';

        $specificBillApprover->save();

        // -- Data Bill --

        // Cek isWaiting All Approver
        $collectionBillApprover = BillApprover::where('bill_id', $id)->get();
        $isWaitingBillApprover = $collectionBillApprover->contains('status', 'Waiting');

        if (!$isWaitingBillApprover) {
            // Simpan Bill
            $dataBill->status = "Approved";
            $dataBill->save();
        }

        return response()->json([
            'message' => 'Tagihan di Approve !',
            'data'  => $dataBill
        ], 200);

    }

    public function payPengajuan(Request $request, $id) {
        // Tagihan
        $dataBill = Bill::where('id', $id)->where('status', 'Approved')->first();

        if (!$dataBill) {
            return response()->json([
                'message' => 'Pengajuan Tidak Ada !',
            ], 403);
        }

        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // -- Data Tagihan --

        // Untuk File
            // Nama Asli File
            $fileNameOriginal = str_replace(' ', '-', $request->fileName);

            //Naming
            $fileInvName  = rand().'-'.$fileNameOriginal;

            //Ekstensi
            $getFileExt     = explode('.', $fileNameOriginal);
            $file_ext       = end($getFileExt);

            $tipeValid      = [
                'pdf', 'doc', 'docx', 'xls', 'xlsx'
            ];

            foreach ($tipeValid as $tipe) {
                if (!in_array($file_ext, $tipeValid)) {
                    return response()->json([
                        'message' => 'Tipe File Tidak di Dukung !'
                    ], 415);
                }
            }

            // $fileInvName = $request->file_inv->getClientOriginalName();

            $request->file('file_bukti_pembayaran')->storeAs('data-aplikasi/bukti-pembayaran', $fileInvName);

            $dataBill->file_bukti_pembayaran = $fileInvName;

        $dataBill->status = 'Paid';
        $dataBill->finance_id = $user->id;

        $dataBill->save();

        return response()->json([
            'message' => 'Tagihan di Approve !',
            'data'  => $dataBill
        ], 200);
    }

    public function getPengajuan(Request $request) 
    {
        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();


        // Tagihan
        if ($user->role == "admin" || $user->role == "finance") {
            $dataBill = Bill::where('status', 'Waiting')
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->get();
        } 
        else if ($user->role == "approver") {
            $dataBill = Bill::where('status', 'Waiting')
                            ->whereHas('approvers', function ($q) use ($user) {
                                $q->where('email', $user->email)->where('status', 'Waiting');
                            })
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->get();
        }                
        else {
            $dataBill = Bill::where('pengaju_id', $user->id)
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->where('status', 'Waiting')
                            ->get();
        }


        return response()->json([
            'messsage' => 'Data berhasil di ambil !',
            'data'  => $dataBill
        ], 200);

    }

    public function getApproved(Request $request) 
    {
        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Tagihan
        if ($user->role == "admin" || $user->role == "finance") {
            $dataBill = Bill::where('status', 'Approved')
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->get();
        } 
        else if ($user->role == "approver") {
            $dataBill = Bill::whereHas('approvers', function ($q) use ($user) {
                                $q->where('email', $user->email)->where('status', 'Approved');
                            })
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->get();
        }
        else {
            $dataBill = Bill::where('pengaju_id', $user->id)
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->where('status', 'Approved')
                            ->get();
        }
        
        return response()->json([
            'messsage' => 'Data berhasil di ambil !',
            'data'  => $dataBill
        ], 200);

    }

    public function getPaid(Request $request) 
    {
        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Tagihan
        if ($user->role == "admin" || $user->role == "finance") {
            $dataBill = Bill::where('status', 'Paid')
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->get();
        } 
        else if ($user->role == "approver") {
            $dataBill = Bill::where('status', 'Paid')
                            ->whereHas('approvers', function ($q) use ($user) {
                                $q->where('email', $user->email)->where('status', 'Approved');
                            })
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->get();
        }
        else {
            $dataBill = Bill::where('pengaju_id', $user->id)
                            ->with(['pengajus', 'approvers', 'finances'])
                            ->where('status', 'Paid')
                            ->get();
        }
        
        return response()->json([
            'messsage' => 'Data berhasil di ambil !',
            'data'  => $dataBill
        ], 200);

    }

}
