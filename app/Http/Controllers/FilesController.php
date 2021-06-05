<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    // Show PP
    public function showInvBill($namaFile, $tipeFile)
    {

        $cariInv = storage_path('app/data-aplikasi/file-inv/' . $namaFile . '.' .$tipeFile);
            
        $isiResponse = response()->download($cariInv);

        $fileInv = !empty($cariInv) ? $isiResponse : null;

        return $fileInv;
    }

    public function showBP($namaFile, $tipeFile)
    {

        $cariBP = storage_path('app/data-aplikasi/bukti-pembayaran/' . $namaFile . '.' .$tipeFile);
            
        $isiResponse = response()->download($cariBP);

        $fileBP = !empty($cariBP) ? $isiResponse : null;

        return $fileBP;
    }
}
