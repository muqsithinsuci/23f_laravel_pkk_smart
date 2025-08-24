<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AgendaKegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_kegiatan',
        'nama_kegiatan',
        'tanggal_waktu',
        'tempat',
        'deskripsi',
        'penanggung_jawab',
        'target_peserta',
        'kehadiran',
        'status',
        'catatan_kegiatan',
        'hasil_kegiatan',
        'rencana_tindak_lanjut',
        'tingkat_kepuasan',
        'total_target',
        'total_hadir',
        'total_tidak_hadir',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
        'target_peserta' => 'array',
        'kehadiran' => 'array',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Traditional Accessors (Compatible with all Laravel versions)
    public function getTanggalFormatAttribute(): string
    {
        return $this->tanggal_waktu?->locale('id')->translatedFormat('l, d F Y - H:i') ?? '';
    }

    public function getPersentaseKehadiranAttribute(): float
    {
        if (($this->total_target ?? 0) == 0) return 0;
        
        return round((($this->total_hadir ?? 0) / $this->total_target) * 100, 1);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'aktif' => 'warning',
            'selesai' => 'success',
            'dibatalkan' => 'danger',
            default => 'secondary'
        };
    }

    public function getIsSelesaiAttribute(): bool
    {
        return $this->status === 'selesai';
    }

    public function getIsDibatalkanAttribute(): bool
    {
        return $this->status === 'dibatalkan';
    }

    // Mutators
    public function setTanggalWaktuAttribute($value)
    {
        $this->attributes['tanggal_waktu'] = Carbon::parse($value);
    }

    // Business Logic Methods
    public function updateKehadiran(array $kehadiranData): void
    {
        $this->kehadiran = $kehadiranData;
        $this->total_target = count($kehadiranData);
        $this->total_hadir = collect($kehadiranData)->where('hadir', true)->count();
        $this->total_tidak_hadir = $this->total_target - $this->total_hadir;
        $this->save();
    }

    public function getTargetKeluarga(): array
    {
        $targetPeserta = $this->target_peserta;
        
        if (!$targetPeserta || !isset($targetPeserta['jenis'])) {
            return [];
        }

        $query = Keluarga::where('user_id', $this->user_id);

        return match($targetPeserta['jenis']) {
            'semua' => $query->get()->toArray(),
            'manual' => $query->whereIn('id', $targetPeserta['keluarga_manual'] ?? [])->get()->toArray(),
            default => []
        };
    }

    public function calculateEstimasiPeserta(): int
    {
        $targetPeserta = $this->target_peserta;
        
        if (!$targetPeserta || !isset($targetPeserta['jenis'])) {
            return 0;
        }

        $query = Keluarga::where('user_id', $this->user_id);

        return match($targetPeserta['jenis']) {
            'semua' => $query->count(),
            'manual' => count($targetPeserta['keluarga_manual'] ?? []),
            default => 0
        };
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'aktif']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'aktif']);
    }

    public function markAsSelesai(): void
    {
        $this->update(['status' => 'selesai']);
    }

    public function markAsDibatalkan(): void
    {
        $this->update(['status' => 'dibatalkan']);
    }

    public function getDaysUntilEvent(): int
    {
        return $this->tanggal_waktu->diffInDays(now(), false);
    }

    public function isUpcoming(): bool
    {
        return $this->tanggal_waktu->isFuture();
    }

    public function isPast(): bool
    {
        return $this->tanggal_waktu->isPast();
    }

    public function isToday(): bool
    {
        return $this->tanggal_waktu->isToday();
    }

    public function getJenisKegiatanLabel(): string
    {
        $labels = [
            'posyandu' => '🏥 Posyandu',
            'penyuluhan' => '📢 Penyuluhan Kesehatan',
            'imunisasi' => '💉 Imunisasi',
            'pemeriksaan' => '🩺 Pemeriksaan Kesehatan',
            'sosialisasi' => '📣 Sosialisasi Program',
            'gotong_royong' => '🤝 Gotong Royong',
            'rapat' => '👥 Rapat/Koordinasi',
            'pelatihan' => '🎓 Pelatihan',
            'lainnya' => '📋 Lainnya',
        ];

        return $labels[$this->jenis_kegiatan] ?? $this->jenis_kegiatan ?? 'Tidak diketahui';
    }

    public function getDurasiKegiatan(): ?string
    {
        if (!$this->tanggal_waktu) return null;
        
        // Asumsi durasi default berdasarkan jenis kegiatan
        $defaultDuration = match($this->jenis_kegiatan) {
            'posyandu' => 4, // 4 jam
            'penyuluhan' => 2, // 2 jam
            'imunisasi' => 3, // 3 jam
            'pemeriksaan' => 4, // 4 jam
            'rapat' => 2, // 2 jam
            'pelatihan' => 6, // 6 jam
            default => 3 // 3 jam default
        };

        $endTime = $this->tanggal_waktu->copy()->addHours($defaultDuration);
        
        return $this->tanggal_waktu->format('H:i') . ' - ' . $endTime->format('H:i');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeDibatalkan($query)
    {
        return $query->where('status', 'dibatalkan');
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_waktu', now()->month)
                    ->whereYear('tanggal_waktu', now()->year);
    }

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal_waktu', now()->year);
    }

    public function scopeMingguIni($query)
    {
        return $query->whereBetween('tanggal_waktu', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('tanggal_waktu', '<', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_waktu', today());
    }

    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis_kegiatan', $jenis);
    }

    public function scopeNeedFollowUp($query)
    {
        return $query->where('status', 'selesai')
                    ->whereNull('hasil_kegiatan');
    }

    // Static helper methods
    public static function getByUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)->get();
    }

    public static function countByStatus(int $userId, string $status): int
    {
        return static::where('user_id', $userId)
                    ->where('status', $status)
                    ->count();
    }

    public static function getUpcomingEvents(int $userId, int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
                    ->whereBetween('tanggal_waktu', [now(), now()->addDays($days)])
                    ->orderBy('tanggal_waktu')
                    ->get();
    }

    public static function getStatistikKehadiran(int $userId): array
    {
        $events = static::where('user_id', $userId)
                       ->where('status', 'selesai')
                       ->whereNotNull('total_target')
                       ->get();

        $totalTarget = $events->sum('total_target');
        $totalHadir = $events->sum('total_hadir');
        
        return [
            'total_kegiatan' => $events->count(),
            'total_target' => $totalTarget,
            'total_hadir' => $totalHadir,
            'persentase_kehadiran' => $totalTarget > 0 ? round(($totalHadir / $totalTarget) * 100, 1) : 0,
        ];
    }

    public static function getJenisKegiatanOptions(): array
    {
        return [
            'posyandu' => '🏥 Posyandu',
            'penyuluhan' => '📢 Penyuluhan Kesehatan',
            'imunisasi' => '💉 Imunisasi',
            'pemeriksaan' => '🩺 Pemeriksaan Kesehatan',
            'sosialisasi' => '📣 Sosialisasi Program',
            'gotong_royong' => '🤝 Gotong Royong',
            'rapat' => '👥 Rapat/Koordinasi',
            'pelatihan' => '🎓 Pelatihan',
            'lainnya' => '📋 Lainnya',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => '📝 Draft',
            'aktif' => '🟢 Aktif',
            'selesai' => '✅ Selesai',
            'dibatalkan' => '❌ Dibatalkan',
        ];
    }

    public static function getTingkatKepuasanOptions(): array
    {
        return [
            'sangat_puas' => '😄 Sangat Puas',
            'puas' => '🙂 Puas',
            'cukup' => '😐 Cukup',
            'kurang_puas' => '😕 Kurang Puas',
            'tidak_puas' => '😞 Tidak Puas',
        ];
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($agenda) {
            // Ensure target_peserta is always an array
            if (!is_array($agenda->target_peserta)) {
                $agenda->target_peserta = [];
            }
            
            // Ensure kehadiran is always an array
            if (!is_array($agenda->kehadiran)) {
                $agenda->kehadiran = [];
            }

            // Auto-calculate total target when saving
            if (!empty($agenda->target_peserta)) {
                $agenda->total_target = $agenda->calculateEstimasiPeserta();
            }
        });

        static::created(function ($agenda) {
            // Logic after agenda is created
            // You can add notifications, logging, etc.
        });

        static::updated(function ($agenda) {
            // Logic after agenda is updated
            if ($agenda->wasChanged('status') && $agenda->status === 'selesai') {
                // Auto-complete logic when status changed to selesai
            }
        });
    }
}