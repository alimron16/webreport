<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // Data calon reseller — disesuaikan dengan kolom tabel reseller OtomaX
            $table->string('nama');                     // nama reseller / outlet
            $table->string('nama_pemilik')->nullable(); // nama pemilik asli
            $table->string('no_hp');
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('pin', 20);

            // Kolom Tambahan OtomaX
            $table->string('nomor_ktp', 50)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('alamat_ip', 100)->nullable();
            $table->string('password_ip', 100)->nullable();
            $table->string('url_report', 255)->nullable(); // URL Callback

            // Berkas Upload
            $table->string('berkas_path')->nullable();
            $table->string('berkas_nama')->nullable();
            $table->string('foto_ktp_path')->nullable();
            $table->string('foto_npwp_path')->nullable();

            // Proses validasi admin
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('kode_reseller')->nullable();
            $table->string('kode_prefix', 10)->default('RS');
            $table->text('catatan_admin')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reviewed_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
