<?php

// database/migrations/2024_01_02_000001_create_keluargas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tab 1: Data Kepala Keluarga
            $table->string('nama_kepala_keluarga');
            $table->text('alamat_lengkap');
            $table->string('rt', 3);
            $table->string('rw', 3);
            $table->enum('status_ekonomi', ['mampu', 'kurang_mampu', 'tidak_mampu']);
            $table->string('telepon')->nullable();
            
            // Tab 2: Anggota Keluarga (JSON Array)
            $table->json('anggota_keluarga'); 
            /* Format JSON:
            [
                {
                    "nama": "string",
                    "umur": int,
                    "jenis_kelamin": "laki-laki|perempuan",
                    "status": "balita|anak|dewasa|lansia",
                    "pendidikan": "string",
                    "pekerjaan": "string",
                    "status_imunisasi": "lengkap|belum_lengkap|tidak_ada", // khusus balita
                    "is_ibu_hamil": boolean,
                    "usia_kehamilan": int // dalam bulan
                }
            ]
            */
            
            // Tab 3: Data Gizi Balita (JSON Array)
            $table->json('data_gizi_balita')->nullable();
            /* Format JSON:
            [
                {
                    "nama_balita": "string",
                    "tinggi_badan": decimal,
                    "berat_badan": decimal,
                    "tanggal_pengukuran": "date",
                    "status_gizi": "baik|kurang|buruk|lebih",
                    "catatan": "string"
                }
            ]
            */
            
            // Koordinat untuk mapping (optional)
            $table->decimal('koordinat_lat', 10, 8)->nullable();
            $table->decimal('koordinat_lng', 11, 8)->nullable();
            
            $table->timestamps();
            
            // Index untuk pencarian
            $table->index(['user_id', 'rt', 'rw']);
            $table->index('nama_kepala_keluarga');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keluargas');
    }
};