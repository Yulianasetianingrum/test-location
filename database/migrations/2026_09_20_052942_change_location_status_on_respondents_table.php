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
        Schema::table('respondents', function (Blueprint $table) {
            $table->dropColumn('location_status');
        });

        Schema::table('respondents', function (Blueprint $table) {
            $table->enum('location_status', ['DALAM_WILAYAH', 'DI_LUAR_WILAYAH'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->dropColumn('location_status');
        });

        Schema::table('respondents', function (Blueprint $table) {
            $table->enum('location_status', ['BELUM_DIVERIFIKASI', 'VALIDASI_BERHASIL', 'VALIDASI_GAGAL'])->default('BELUM_DIVERIFIKASI');
        });
    }
};
