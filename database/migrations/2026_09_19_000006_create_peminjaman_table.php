<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->uuid('id_pjm')->primary();
            $table->foreignUuid('id_user')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('id_inventaris')->constrained('inventaris')->restrictOnDelete();
            $table->string('nama_pjm', 20);
            $table->string('status_pjm', 15);
            $table->time('waktu_pinjam')->nullable();
            $table->time('waktu_kembali')->nullable();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali');
            $table->string('status_pinjam', 20);
            $table->string('keterangan_pjm', 70)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
