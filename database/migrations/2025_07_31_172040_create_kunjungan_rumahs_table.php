<?php

// database/migrations/2024_01_04_000001_create_kunjungan_rumahs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_rumahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('keluarga_id')->constrained()->onDelete('cascade');
            
            // Data Kunjungan
            $table->date('tanggal_kunjungan');
            $table->json('tujuan_kunjungan'); // ['cek_balita', 'sosialisasi', 'pendataan']
            
            // Checklist Sanitasi
            $table->enum('air_bersih', ['baik', 'kurang', 'buruk']);
            $table->enum('jamban', ['sehat', 'tidak_sehat']);
            $table->enum('sampah', ['baik', 'kurang', 'buruk']);
            $table->enum('ventilasi', ['baik', 'kurang', 'buruk']);
            
            // Update Data Anggota (JSON Array)
            $table->json('update_anggota')->nullable();
            /* Format JSON:
            [
                {
                    "nama": "string",
                    "jenis_update": "gizi|kondisi|imunisasi",
                    "berat_badan_baru": decimal,
                    "tinggi_badan_baru": decimal,
                    "kondisi_kesehatan": "baik|sakit_ringan|sakit_berat|perlu_rujukan",
                    "catatan": "string"
                }
            ]
            */
            
            // Catatan dan Dokumentasi
            $table->text('catatan_kondisi_keluarga')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->json('foto_dokumentasi')->nullable(); // array of file paths
            
            // Follow up
            $table->boolean('perlu_kunjungan_ulang')->default(false);
            $table->date('tanggal_kunjungan_ulang')->nullable();
            $table->text('catatan_follow_up')->nullable();
            
            // Prioritas untuk kunjungan berikutnya
            $table->enum('prioritas', ['tinggi', 'sedang', 'rendah'])->default('sedang');
            
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'tanggal_kunjungan']);
            $table->index(['keluarga_id', 'tanggal_kunjungan']);
            $table->index(['prioritas', 'tanggal_kunjungan_ulang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_rumahs');
    }
};