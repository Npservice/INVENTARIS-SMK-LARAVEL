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
        Schema::create('inventaris', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama', 40);
            $table->foreignUuid('jenis_id')->constrained('jenis')->restrictOnDelete();
            $table->string('kode_invt', 20)->unique();
            $table->foreignUuid('lokasi_id')->constrained('lokasi')->restrictOnDelete();
            $table->date('masuk');
            $table->string('kondisi', 10);
            $table->foreignUuid('pendanaan_id')->constrained('pendanaan')->restrictOnDelete();
            $table->unsignedInteger('jumlah')->default(1);
            $table->decimal('harga_beli', 12, 2)->nullable();
            $table->string('keterangan', 70)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};
