<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang_transaksi', function (Blueprint $table) {
            $table->id();
            //
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('barang_id');
            //
            $table->string('kode_transaksi')->unique();
            $table->date('tanggal');
            $table->double('qty', null, 3)->default(0);
            $table->double('harga', null, 3)->default(0);
            $table->boolean('is_masuk')->default(1)->comment('0 : Barang keluar, 1 : Barang masuk');
            $table->text('keterangan')->nullable();
            //
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')
                ->onDelete('SET NULL');
            $table->foreign('barang_id')->references('id')->on('barang')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang_transaksi');
    }
};
