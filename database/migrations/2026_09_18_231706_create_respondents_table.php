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
        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nomor_hp');
            $table->string('provinsi');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->string('desa');
            $table->string('dusun')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->text('detail_alamat')->nullable();
            
            $table->decimal('latitude_referensi', 10, 8)->nullable();
            $table->decimal('longitude_referensi', 11, 8)->nullable();
            $table->string('display_name_referensi')->nullable();
            
            $table->decimal('latitude_rumah', 10, 8)->nullable();
            $table->decimal('longitude_rumah', 11, 8)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('jarak_dari_referensi', 8, 2)->nullable();
            $table->enum('location_status', ['BELUM_DIVERIFIKASI', 'VALIDASI_BERHASIL', 'VALIDASI_GAGAL'])->default('BELUM_DIVERIFIKASI');
            $table->timestamp('location_captured_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respondents');
    }
};
