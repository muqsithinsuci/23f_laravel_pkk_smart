<?php

// database/migrations/2024_01_03_000001_create_agenda_kegiatans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Data Kegiatan
            $table->string('nama_kegiatan');
            $table->datetime('tanggal_waktu');
            $table->string('tempat');
            $table->text('deskripsi')->nullable();
            $table->string('penanggung_jawab');
            
            // Target Peserta
            $table->json('target_peserta'); 
            /* Format JSON:
            {
                "jenis": "semua|balita|ibu_hamil|manual",
                "keluarga_terpilih": [1,2,3] // array of keluarga_ids jika manual
            }
            */
            
            // Data Kehadiran (JSON Array)
            $table->json('kehadiran')->nullable();
            /* Format JSON:
            [
                {
                    "keluarga_id": int,
                    "nama_kepala_keluarga": "string",
                    "rt_rw": "string",
                    "hadir": boolean,
                    "keterangan": "string",
                    "waktu_absen": "datetime"
                }
            ]
            */
            
            // Status & Hasil
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('draft');
            $table->text('catatan_kegiatan')->nullable();
            $table->text('hasil_kegiatan')->nullable();
            
            // Summary Kehadiran (untuk cepat akses)
            $table->integer('total_target')->default(0);
            $table->integer('total_hadir')->default(0);
            $table->integer('total_tidak_hadir')->default(0);
            
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'tanggal_waktu']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_kegiatans');
    }
};