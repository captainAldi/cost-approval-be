<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->longText('deskripsi');
            $table->string('file_inv');
            $table->string('status');
            $table->string('bu');
            $table->string('business_initiative');
            $table->string('nama_pt');
            $table->integer('pengaju_id')->unsigned()->nullable();
            $table->string('file_bukti_pembayaran')->nullable();
            $table->integer('finance_id')->unsigned()->nullable();
            $table->date('tanggal_transaksi')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->double('jumlah_tagihan');
            $table->string('transaksi_berulang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
}
