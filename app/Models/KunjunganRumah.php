<?php

// app/Models/KunjunganRumah.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KunjunganRumah extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'keluarga_id',
        'tanggal_kunjungan',
        'tujuan_kunjungan',
        'air_bersih',
        'jamban',
        'sampah',
        'ventilasi',
        'update_anggota',
        'catatan_kondisi_keluarga',
        'rekomendasi',
        'foto_dokumentasi',
        'perlu_kunjungan_ulang',
        'tanggal_kunjungan_ulang',
        'catatan_follow_up',
        'prioritas',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'tanggal_kunjungan_ulang' => 'date',
        'tujuan_kunjungan' => 'array',
        'update_anggota' => 'array',
        'foto_dokumentasi' => 'array',
        'perlu_kunjungan_ulang' => 'boolean',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    // Traditional Accessors (Compatible with all Laravel versions)
    public function getTanggalKunjunganFormatAttribute(): string
    {
        return $this->tanggal_kunjungan?->locale('id')->translatedFormat('d F Y') ?? '';
    }

    public function getHariSejakKunjunganAttribute(): int
    {
        return $this->tanggal_kunjungan?->diffInDays(now()) ?? 0;
    }

    public function getTujuanKunjunganTextAttribute(): string
    {
        $tujuan = $this->tujuan_kunjungan ?? [];
        
        $mapping = [
            'cek_balita' => 'Cek Tumbuh Kembang Balita',
            'sosialisasi' => 'Sosialisasi Kesehatan',
            'pendataan' => 'Pendataan Ulang',
            'pemantauan' => 'Pemantauan Kondisi Keluarga',
            'edukasi' => 'Edukasi Kesehatan',
        ];

        return collect($tujuan)
            ->map(fn($item) => $mapping[$item] ?? $item)
            ->join(', ');
    }

    public function getPrioritasBadgeAttribute(): string
    {
        return match($this->prioritas) {
            'tinggi' => 'danger',
            'sedang' => 'warning',
            'rendah' => 'success',
            default => 'secondary'
        };
    }

    public function getSanitasiScoreAttribute(): int
    {
        $score = 0;
        
        if ($this->air_bersih === 'baik') $score++;
        if ($this->jamban === 'sehat') $score++;
        if ($this->sampah === 'baik') $score++;
        if ($this->ventilasi === 'baik') $score++;
        
        return $score;
    }

    public function getSanitasiPersentaseAttribute(): float
    {
        return ($this->getSanitasiScoreAttribute() / 4) * 100;
    }

    public function getKondisiSanitasiAttribute(): string
    {
        $score = $this->getSanitasiScoreAttribute();
        
        return match(true) {
            $score === 4 => 'Sangat Baik',
            $score === 3 => 'Baik',
            $score === 2 => 'Cukup',
            $score === 1 => 'Kurang',
            default => 'Buruk'
        };
    }

    // Mutators
    public function setTanggalKunjunganAttribute($value)
    {
        $this->attributes['tanggal_kunjungan'] = Carbon::parse($value);
    }

    public function setTanggalKunjunganUlangAttribute($value)
    {
        $this->attributes['tanggal_kunjungan_ulang'] = $value ? Carbon::parse($value) : null;
    }

    // Business Logic Methods
    public function updateDataGiziBalita(array $dataGizi): void
    {
        $keluarga = $this->keluarga;
        $dataGiziBalita = $keluarga->data_gizi_balita ?? [];
        
        foreach ($dataGizi as $data) {
            $dataGiziBalita[] = array_merge($data, [
                'tanggal_pengukuran' => $this->tanggal_kunjungan->format('Y-m-d')
            ]);
        }
        
        $keluarga->update(['data_gizi_balita' => $dataGiziBalita]);
    }

    public function getAnggotaUpdates(): array
    {
        return $this->update_anggota ?? [];
    }

    public function addAnggotaUpdate(array $updateData): void
    {
        $currentUpdates = $this->update_anggota ?? [];
        $currentUpdates[] = $updateData;
        $this->update(['update_anggota' => $currentUpdates]);
    }

    public function getSanitasiDetails(): array
    {
        return [
            'air_bersih' => [
                'status' => $this->air_bersih,
                'label' => $this->getSanitasiLabel('air_bersih', $this->air_bersih),
                'score' => $this->air_bersih === 'baik' ? 1 : 0,
            ],
            'jamban' => [
                'status' => $this->jamban,
                'label' => $this->getSanitasiLabel('jamban', $this->jamban),
                'score' => $this->jamban === 'sehat' ? 1 : 0,
            ],
            'sampah' => [
                'status' => $this->sampah,
                'label' => $this->getSanitasiLabel('sampah', $this->sampah),
                'score' => $this->sampah === 'baik' ? 1 : 0,
            ],
            'ventilasi' => [
                'status' => $this->ventilasi,
                'label' => $this->getSanitasiLabel('ventilasi', $this->ventilasi),
                'score' => $this->ventilasi === 'baik' ? 1 : 0,
            ],
        ];
    }

    private function getSanitasiLabel(string $kategori, ?string $status): string
    {
        $labels = [
            'air_bersih' => [
                'baik' => '🚰 Air jernih, tidak berbau',
                'kurang' => '⚠️ Air keruh atau berbau',
                'buruk' => '❌ Air tidak layak konsumsi',
            ],
            'jamban' => [
                'sehat' => '🚽 Jamban bersih dan tertutup',
                'tidak_sehat' => '❌ Jamban kotor/terbuka',
            ],
            'sampah' => [
                'baik' => '♻️ Sampah dikelola dengan baik',
                'kurang' => '⚠️ Sampah berserakan',
                'buruk' => '❌ Tidak ada pengelolaan sampah',
            ],
            'ventilasi' => [
                'baik' => '🌬️ Ventilasi cukup',
                'kurang' => '⚠️ Ventilasi terbatas',
                'buruk' => '❌ Tidak ada ventilasi',
            ],
        ];

        return $labels[$kategori][$status] ?? $status ?? 'Tidak diketahui';
    }

    public function isNeedFollowUp(): bool
    {
        return $this->perlu_kunjungan_ulang && 
               $this->tanggal_kunjungan_ulang && 
               $this->tanggal_kunjungan_ulang <= now();
    }

    public function isOverdue(): bool
    {
        return $this->perlu_kunjungan_ulang && 
               $this->tanggal_kunjungan_ulang && 
               $this->tanggal_kunjungan_ulang < now();
    }

    public function getDaysUntilFollowUp(): ?int
    {
        if (!$this->tanggal_kunjungan_ulang) return null;
        
        return $this->tanggal_kunjungan_ulang->diffInDays(now(), false);
    }

    public function getSanitasiRecommendations(): array
    {
        $recommendations = [];
        
        if ($this->air_bersih !== 'baik') {
            $recommendations[] = 'Perbaiki kualitas air bersih - pastikan air jernih dan tidak berbau';
        }
        
        if ($this->jamban !== 'sehat') {
            $recommendations[] = 'Perbaiki kondisi jamban - pastikan bersih dan tertutup rapat';
        }
        
        if ($this->sampah !== 'baik') {
            $recommendations[] = 'Kelola sampah dengan baik - pisahkan organik dan anorganik';
        }
        
        if ($this->ventilasi !== 'baik') {
            $recommendations[] = 'Tambah ventilasi rumah untuk sirkulasi udara yang lebih baik';
        }
        
        return $recommendations;
    }

    public function getPriorityLevel(): string
    {
        return match($this->prioritas) {
            'tinggi' => 'Prioritas Tinggi - Perlu perhatian segera',
            'sedang' => 'Prioritas Sedang - Perlu pemantauan rutin',
            'rendah' => 'Prioritas Rendah - Kondisi baik',
            default => 'Prioritas tidak ditentukan'
        };
    }

    // Scopes
    public function scopePrioritasTinggi($query)
    {
        return $query->where('prioritas', 'tinggi');
    }

    public function scopePerluFollowUp($query)
    {
        return $query->where('perlu_kunjungan_ulang', true)
                    ->whereNotNull('tanggal_kunjungan_ulang')
                    ->where('tanggal_kunjungan_ulang', '<=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('perlu_kunjungan_ulang', true)
                    ->whereNotNull('tanggal_kunjungan_ulang')
                    ->where('tanggal_kunjungan_ulang', '<', now());
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_kunjungan', now()->month)
                    ->whereYear('tanggal_kunjungan', now()->year);
    }

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal_kunjungan', now()->year);
    }

    public function scopeMingguIni($query)
    {
        return $query->whereBetween('tanggal_kunjungan', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeSanitasiKurang($query)
    {
        return $query->where(function($q) {
            $q->where('air_bersih', '!=', 'baik')
              ->orWhere('jamban', '!=', 'sehat')
              ->orWhere('sampah', '!=', 'baik')
              ->orWhere('ventilasi', '!=', 'baik');
        });
    }

    public function scopeSanitasiBaik($query)
    {
        return $query->where('air_bersih', 'baik')
                    ->where('jamban', 'sehat')
                    ->where('sampah', 'baik')
                    ->where('ventilasi', 'baik');
    }

    public function scopeByTujuan($query, string $tujuan)
    {
        return $query->whereJsonContains('tujuan_kunjungan', $tujuan);
    }

    public function scopeByKeluarga($query, int $keluargaId)
    {
        return $query->where('keluarga_id', $keluargaId);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('tanggal_kunjungan', 'desc');
    }

    // Static helper methods
    public static function getByUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)->get();
    }

    public static function countByPrioritas(int $userId, string $prioritas): int
    {
        return static::where('user_id', $userId)
                    ->where('prioritas', $prioritas)
                    ->count();
    }

    public static function getStatistikSanitasi(int $userId): array
    {
        $kunjungan = static::where('user_id', $userId)->get();
        
        $totalKunjungan = $kunjungan->count();
        $sanitasiBaik = $kunjungan->filter(fn($k) => $k->sanitasi_score === 4)->count();
        $sanitasiKurang = $kunjungan->filter(fn($k) => $k->sanitasi_score < 3)->count();
        
        return [
            'total_kunjungan' => $totalKunjungan,
            'sanitasi_baik' => $sanitasiBaik,
            'sanitasi_kurang' => $sanitasiKurang,
            'persentase_baik' => $totalKunjungan > 0 ? round(($sanitasiBaik / $totalKunjungan) * 100, 1) : 0,
        ];
    }

    public static function getKunjunganTerdekat(int $userId, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
                    ->where('perlu_kunjungan_ulang', true)
                    ->whereNotNull('tanggal_kunjungan_ulang')
                    ->whereBetween('tanggal_kunjungan_ulang', [now(), now()->addDays($days)])
                    ->orderBy('tanggal_kunjungan_ulang')
                    ->get();
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($kunjungan) {
            // Ensure arrays are properly formatted
            if (!is_array($kunjungan->tujuan_kunjungan)) {
                $kunjungan->tujuan_kunjungan = [];
            }
            
            if (!is_array($kunjungan->update_anggota)) {
                $kunjungan->update_anggota = [];
            }
            
            if (!is_array($kunjungan->foto_dokumentasi)) {
                $kunjungan->foto_dokumentasi = [];
            }
        });

        static::created(function ($kunjungan) {
            // Update keluarga's last visit information
            $kunjungan->keluarga?->touch();
        });

        static::updated(function ($kunjungan) {
            // Logic after kunjungan is updated
            if ($kunjungan->wasChanged('perlu_kunjungan_ulang') && $kunjungan->perlu_kunjungan_ulang) {
                // Notification logic for follow-up visits
            }
        });
    }
}