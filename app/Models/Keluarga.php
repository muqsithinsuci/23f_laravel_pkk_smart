<?php

// app/Models/Keluarga.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keluarga extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_kepala_keluarga',
        'alamat_lengkap',
        'rt',
        'rw',
        'status_ekonomi',
        'telepon',
        'anggota_keluarga',
        'data_gizi_balita',
        'koordinat_lat',
        'koordinat_lng',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'anggota_keluarga' => 'array',
        'data_gizi_balita' => 'array',
        'koordinat_lat' => 'decimal:8',
        'koordinat_lng' => 'decimal:8',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kunjunganRumahs(): HasMany
    {
        return $this->hasMany(KunjunganRumah::class);
    }

    // Traditional Accessors (Compatible with all Laravel versions)
    public function getRtRwAttribute(): string
    {
        return "RT {$this->rt} / RW {$this->rw}";
    }

    public function getJumlahAnggotaAttribute(): int
    {
        return is_array($this->anggota_keluarga) ? count($this->anggota_keluarga) : 0;
    }

    public function getJumlahBalitaAttribute(): int
    {
        if (!is_array($this->anggota_keluarga)) return 0;
        
        return collect($this->anggota_keluarga)
            ->where('status', 'balita')
            ->count();
    }

    public function getJumlahIbuHamilAttribute(): int
    {
        if (!is_array($this->anggota_keluarga)) return 0;
        
        return collect($this->anggota_keluarga)
            ->where('is_ibu_hamil', true)
            ->count();
    }

    public function getKunjunganTerakhirAttribute()
    {
        return $this->kunjunganRumahs()
            ->latest('tanggal_kunjungan')
            ->first();
    }

    public function getBelumDikunjungiAttribute(): bool
    {
        $kunjunganTerakhir = $this->getKunjunganTerakhirAttribute();
        
        if (!$kunjunganTerakhir) return true;
        
        return $kunjunganTerakhir->tanggal_kunjungan->diffInDays(now()) > 30;
    }

    // Helper methods for data manipulation
    public function getAnggotaByStatus(string $status): array
    {
        if (!is_array($this->anggota_keluarga)) return [];
        
        return collect($this->anggota_keluarga)
            ->where('status', $status)
            ->values()
            ->toArray();
    }

    public function getBalitaList(): array
    {
        return $this->getAnggotaByStatus('balita');
    }

    public function getIbuHamilList(): array
    {
        if (!is_array($this->anggota_keluarga)) return [];
        
        return collect($this->anggota_keluarga)
            ->where('is_ibu_hamil', true)
            ->values()
            ->toArray();
    }

    public function getDataGiziTerbaru(): ?array
    {
        if (!is_array($this->data_gizi_balita) || empty($this->data_gizi_balita)) {
            return null;
        }

        return collect($this->data_gizi_balita)
            ->sortByDesc('tanggal_pengukuran')
            ->first();
    }

    public function addAnggotaKeluarga(array $anggotaData): void
    {
        $currentAnggota = $this->anggota_keluarga ?? [];
        $currentAnggota[] = $anggotaData;
        $this->update(['anggota_keluarga' => $currentAnggota]);
    }

    public function updateAnggotaKeluarga(int $index, array $anggotaData): void
    {
        $currentAnggota = $this->anggota_keluarga ?? [];
        
        if (isset($currentAnggota[$index])) {
            $currentAnggota[$index] = array_merge($currentAnggota[$index], $anggotaData);
            $this->update(['anggota_keluarga' => $currentAnggota]);
        }
    }

    public function removeAnggotaKeluarga(int $index): void
    {
        $currentAnggota = $this->anggota_keluarga ?? [];
        
        if (isset($currentAnggota[$index])) {
            unset($currentAnggota[$index]);
            $this->update(['anggota_keluarga' => array_values($currentAnggota)]);
        }
    }

    public function addDataGizi(array $giziData): void
    {
        $currentGizi = $this->data_gizi_balita ?? [];
        $currentGizi[] = $giziData;
        $this->update(['data_gizi_balita' => $currentGizi]);
    }

    // Scopes
    public function scopeByRtRw($query, $rt = null, $rw = null)
    {
        if ($rt) {
            $query->where('rt', $rt);
        }
        
        if ($rw) {
            $query->where('rw', $rw);
        }
        
        return $query;
    }

    public function scopeWithBalita($query)
    {
        return $query->whereJsonLength('anggota_keluarga', '>', 0)
            ->whereRaw("JSON_SEARCH(anggota_keluarga, 'one', 'balita', null, '$[*].status') IS NOT NULL");
    }

    public function scopeWithIbuHamil($query)
    {
        return $query->whereJsonLength('anggota_keluarga', '>', 0)
            ->whereRaw("JSON_SEARCH(anggota_keluarga, 'one', true, null, '$[*].is_ibu_hamil') IS NOT NULL");
    }

    public function scopeByStatusEkonomi($query, string $status)
    {
        return $query->where('status_ekonomi', $status);
    }

    public function scopeBelumDikunjungi($query, int $days = 30)
    {
        return $query->whereDoesntHave('kunjunganRumahs', function ($query) use ($days) {
            $query->where('tanggal_kunjungan', '>=', now()->subDays($days));
        });
    }

    public function scopePerluKunjungan($query)
    {
        return $query->where(function ($query) {
            $query->whereHas('kunjunganRumahs', function ($kunjunganQuery) {
                $kunjunganQuery->where('perlu_kunjungan_ulang', true)
                    ->where('tanggal_kunjungan_ulang', '<=', now());
            })->orWhereDoesntHave('kunjunganRumahs');
        });
    }

    // Static helper methods
    public static function getByUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)->get();
    }

    public static function countByRt(int $userId, string $rt): int
    {
        return static::where('user_id', $userId)
            ->where('rt', $rt)
            ->count();
    }

    public static function countByRw(int $userId, string $rw): int
    {
        return static::where('user_id', $userId)
            ->where('rw', $rw)
            ->count();
    }

    public static function getTotalBalitaByUser(int $userId): int
    {
        return static::where('user_id', $userId)
            ->withBalita()
            ->get()
            ->sum('jumlah_balita');
    }

    public static function getTotalIbuHamilByUser(int $userId): int
    {
        return static::where('user_id', $userId)
            ->withIbuHamil()
            ->get()
            ->sum('jumlah_ibu_hamil');
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate derived data when saving
        static::saving(function ($keluarga) {
            // Ensure anggota_keluarga is always an array
            if (!is_array($keluarga->anggota_keluarga)) {
                $keluarga->anggota_keluarga = [];
            }
            
            // Ensure data_gizi_balita is always an array
            if (!is_array($keluarga->data_gizi_balita)) {
                $keluarga->data_gizi_balita = [];
            }
        });
    }
}