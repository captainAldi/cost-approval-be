<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bill;
use App\Models\User;

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
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'judul'         => 'required',
            'deskripsi'     => 'required',
            'file_inv'      => 'required',
            'bu'            => 'required',
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

        return response()->json([
            'message' => 'Tagihan Telah di Ajukan !',
            'data'  => $dataBill
        ], 201);


    }

    public function approvePengajuan(Request $request, $id)
    {
        // Tagihan
        $dataBill = Bill::where('id', $id)->first();

        if (!$dataBill) {
            return response()->json([
                'message' => 'Pengajuan Tidak Ada !',
            ], 403);
        }

        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Simpan Inputan
        $dataBill->status = "Approved";
        $dataBill->approvers_id = $user->id;

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

        // Cek Admin or Not
        $cekAdmin = $user->role == "admin" ? true : false; 

        // Tagihan
        if ($cekAdmin) {
            $dataBill = Bill::where('status', 'Waiting')
                            ->with(['pengajus', 'approvers'])
                            ->get();
        } else {
            $dataBill = Bill::where('pengaju_id', $user->id)
                            ->with(['pengajus', 'approvers'])
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

        // Cek Admin or Not
        $cekAdmin = $user->role == "admin" ? true : false; 

        // Tagihan
        if ($cekAdmin) {
            $dataBill = Bill::where('status', 'Approved')
                            ->with(['pengajus', 'approvers'])
                            ->get();
        } else {
            $dataBill = Bill::where('pengaju_id', $user->id)
                            ->with(['pengajus', 'approvers'])
                            ->where('status', 'Approved')
                            ->get();
        }
        

        return response()->json([
            'messsage' => 'Data berhasil di ambil !',
            'data'  => $dataBill
        ], 200);

    }

}
