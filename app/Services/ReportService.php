<?php

namespace App\Services;

use App\Models\AgendaKegiatan;
use App\Models\Keluarga;
use App\Models\KunjunganRumah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate statistics for keluarga data
     */
    public function getKeluargaStatistics(array $filters = []): array
    {
        $userId = Auth::id();
        $query = Keluarga::where('user_id', $userId);
        
        // Apply filters
        $this->applyKeluargaFilters($query, $filters);
        
        $keluargas = $query->get();
        
        return [
            'total_keluarga' => $keluargas->count(),
            'total_anggota' => $keluargas->sum('jumlah_anggota'),
            'total_balita' => $keluargas->sum('jumlah_balita'),
            'total_ibu_hamil' => $keluargas->sum('jumlah_ibu_hamil'),
            'by_status_ekonomi' => $keluargas->groupBy('status_ekonomi')->map->count(),
            'by_rt' => $keluargas->groupBy('rt')->map->count(),
            'by_rw' => $keluargas->groupBy('rw')->map->count(),
        ];
    }
    
    /**
     * Generate statistics for agenda kegiatan
     */
    public function getAgendaStatistics(array $filters = []): array
    {
        $userId = Auth::id();
        $query = AgendaKegiatan::where('user_id', $userId);
        
        // Apply filters
        $this->applyAgendaFilters($query, $filters);
        
        $agendas = $query->get();
        $statistikKehadiran = AgendaKegiatan::getStatistikKehadiran($userId);
        
        return array_merge($statistikKehadiran, [
            'total_kegiatan_laporan' => $agendas->count(),
            'kegiatan_selesai' => $agendas->where('status', 'selesai')->count(),
            'kegiatan_aktif' => $agendas->where('status', 'aktif')->count(),
            'kegiatan_draft' => $agendas->where('status', 'draft')->count(),
            'kegiatan_dibatalkan' => $agendas->where('status', 'dibatalkan')->count(),
            'by_jenis' => $agendas->groupBy('jenis_kegiatan')->map->count(),
            'by_status' => $agendas->groupBy('status')->map->count(),
        ]);
    }
    
    /**
     * Generate statistics for kunjungan rumah
     */
    public function getKunjunganStatistics(array $filters = []): array
    {
        $userId = Auth::id();
        $query = KunjunganRumah::where('user_id', $userId);
        
        // Apply filters
        $this->applyKunjunganFilters($query, $filters);
        
        $kunjungans = $query->get();
        $statistikSanitasi = KunjunganRumah::getStatistikSanitasi($userId);
        
        return array_merge($statistikSanitasi, [
            'total_kunjungan_laporan' => $kunjungans->count(),
            'prioritas_tinggi' => $kunjungans->where('prioritas', 'tinggi')->count(),
            'prioritas_sedang' => $kunjungans->where('prioritas', 'sedang')->count(),
            'prioritas_rendah' => $kunjungans->where('prioritas', 'rendah')->count(),
            'perlu_follow_up' => $kunjungans->where('perlu_kunjungan_ulang', true)->count(),
            'sanitasi_sangat_baik' => $kunjungans->filter(fn($k) => $k->sanitasi_score === 4)->count(),
            'sanitasi_baik' => $kunjungans->filter(fn($k) => $k->sanitasi_score === 3)->count(),
            'sanitasi_cukup' => $kunjungans->filter(fn($k) => $k->sanitasi_score === 2)->count(),
            'sanitasi_kurang' => $kunjungans->filter(fn($k) => $k->sanitasi_score <= 1)->count(),
        ]);
    }
    
    /**
     * Apply filters to keluarga query
     */
    private function applyKeluargaFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['rt'])) {
            $query->where('rt', $filters['rt']);
        }
        
        if (!empty($filters['rw'])) {
            $query->where('rw', $filters['rw']);
        }
        
        if (!empty($filters['status_ekonomi'])) {
            $query->where('status_ekonomi', $filters['status_ekonomi']);
        }
        
        if (!empty($filters['has_balita'])) {
            $query->withBalita();
        }
        
        if (!empty($filters['has_ibu_hamil'])) {
            $query->withIbuHamil();
        }
    }
    
    /**
     * Apply filters to agenda query
     */
    private function applyAgendaFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['jenis_kegiatan'])) {
            $query->where('jenis_kegiatan', $filters['jenis_kegiatan']);
        }
        
        if (!empty($filters['bulan'])) {
            $bulan = explode('-', $filters['bulan']);
            if (count($bulan) === 2) {
                $query->whereMonth('tanggal_waktu', $bulan[1])
                      ->whereYear('tanggal_waktu', $bulan[0]);
            }
        }
    }
    
    /**
     * Apply filters to kunjungan query
     */
    private function applyKunjunganFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['prioritas'])) {
            $query->where('prioritas', $filters['prioritas']);
        }
        
        if (!empty($filters['bulan'])) {
            $bulan = explode('-', $filters['bulan']);
            if (count($bulan) === 2) {
                $query->whereMonth('tanggal_kunjungan', $bulan[1])
                      ->whereYear('tanggal_kunjungan', $bulan[0]);
            }
        }
        
        if (!empty($filters['rt'])) {
            $query->whereHas('keluarga', function($q) use ($filters) {
                $q->where('rt', $filters['rt']);
            });
        }
    }
    
    /**
     * Generate comprehensive dashboard statistics
     */
    public function getDashboardStatistics(): array
    {
        $userId = Auth::id();
        
        // Basic counts
        $totalKeluarga = Keluarga::where('user_id', $userId)->count();
        $totalBalita = Keluarga::getTotalBalitaByUser($userId);
        $totalIbuHamil = Keluarga::getTotalIbuHamilByUser($userId);
        
        // This month statistics
        $kegiatanBulanIni = AgendaKegiatan::where('user_id', $userId)->bulanIni()->count();
        $kunjunganBulanIni = KunjunganRumah::where('user_id', $userId)->bulanIni()->count();
        
        // Activity statistics
        $statistikKehadiran = AgendaKegiatan::getStatistikKehadiran($userId);
        $statistikSanitasi = KunjunganRumah::getStatistikSanitasi($userId);
        
        // Upcoming activities
        $kegiatanMendatang = AgendaKegiatan::where('user_id', $userId)
            ->where('status', 'aktif')
            ->where('tanggal_waktu', '>', now())
            ->count();
        
        // Follow-up required
        $perluFollowUp = KunjunganRumah::where('user_id', $userId)
            ->where('perlu_kunjungan_ulang', true)
            ->whereNotNull('tanggal_kunjungan_ulang')
            ->where('tanggal_kunjungan_ulang', '<=', now()->addDays(7))
            ->count();
        
        return [
            'overview' => [
                'total_keluarga' => $totalKeluarga,
                'total_balita' => $totalBalita,
                'total_ibu_hamil' => $totalIbuHamil,
                'kegiatan_bulan_ini' => $kegiatanBulanIni,
                'kunjungan_bulan_ini' => $kunjunganBulanIni,
                'kegiatan_mendatang' => $kegiatanMendatang,
                'perlu_follow_up' => $perluFollowUp,
            ],
            'kehadiran' => $statistikKehadiran,
            'sanitasi' => $statistikSanitasi,
            'alerts' => [
                'kegiatan_overdue' => AgendaKegiatan::where('user_id', $userId)
                    ->where('status', 'aktif')
                    ->where('tanggal_waktu', '<', now())
                    ->count(),
                'kunjungan_overdue' => KunjunganRumah::where('user_id', $userId)
                    ->where('perlu_kunjungan_ulang', true)
                    ->whereNotNull('tanggal_kunjungan_ulang')
                    ->where('tanggal_kunjungan_ulang', '<', now())
                    ->count(),
                'sanitasi_buruk' => KunjunganRumah::where('user_id', $userId)
                    ->get()
                    ->filter(fn($k) => $k->sanitasi_score <= 1)
                    ->count(),
            ]
        ];
    }
    
    /**
     * Generate trend data for charts
     */
    public function getTrendData(string $type, int $months = 6): array
    {
        $userId = Auth::id();
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthYear = $date->format('Y-m');
            $monthName = $date->locale('id')->translatedFormat('M Y');
            
            switch ($type) {
                case 'kegiatan':
                    $count = AgendaKegiatan::where('user_id', $userId)
                        ->whereMonth('tanggal_waktu', $date->month)
                        ->whereYear('tanggal_waktu', $date->year)
                        ->count();
                    break;
                    
                case 'kunjungan':
                    $count = KunjunganRumah::where('user_id', $userId)
                        ->whereMonth('tanggal_kunjungan', $date->month)
                        ->whereYear('tanggal_kunjungan', $date->year)
                        ->count();
                    break;
                    
                case 'keluarga':
                    $count = Keluarga::where('user_id', $userId)
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                    break;
                    
                default:
                    $count = 0;
            }
            
            $data[] = [
                'period' => $monthYear,
                'label' => $monthName,
                'count' => $count,
            ];
        }
        
        return $data;
    }
    
    /**
     * Generate performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $userId = Auth::id();
        
        // Calculate coverage metrics
        $totalKeluarga = Keluarga::where('user_id', $userId)->count();
        $keluargaDikunjungi = KunjunganRumah::where('user_id', $userId)
            ->distinct('keluarga_id')
            ->count();
        
        $coveragePercentage = $totalKeluarga > 0 ? round(($keluargaDikunjungi / $totalKeluarga) * 100, 1) : 0;
        
        // Calculate average sanitasi score
        $avgSanitasiScore = KunjunganRumah::where('user_id', $userId)
            ->get()
            ->avg('sanitasi_score');
        
        // Calculate kegiatan success rate
        $totalKegiatan = AgendaKegiatan::where('user_id', $userId)->count();
        $kegiatanSelesai = AgendaKegiatan::where('user_id', $userId)
            ->where('status', 'selesai')
            ->count();
        
        $successRate = $totalKegiatan > 0 ? round(($kegiatanSelesai / $totalKegiatan) * 100, 1) : 0;
        
        return [
            'coverage_percentage' => $coveragePercentage,
            'average_sanitasi_score' => round($avgSanitasiScore, 1),
            'kegiatan_success_rate' => $successRate,
            'families_visited' => $keluargaDikunjungi,
            'total_families' => $totalKeluarga,
            'total_activities' => $totalKegiatan,
            'completed_activities' => $kegiatanSelesai,
        ];
    }
    
    /**
     * Generate export filename with timestamp
     */
    public function generateFilename(string $type, array $filters = []): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $filterSuffix = '';
        
        if (!empty($filters)) {
            $filterParts = [];
            foreach ($filters as $key => $value) {
                if ($value && !is_array($value)) {
                    $filterParts[] = substr($key, 0, 3) . '-' . substr($value, 0, 3);
                }
            }
            if (!empty($filterParts)) {
                $filterSuffix = '-' . implode('-', $filterParts);
            }
        }
        
        return "laporan-{$type}{$filterSuffix}-{$timestamp}.pdf";
    }
}