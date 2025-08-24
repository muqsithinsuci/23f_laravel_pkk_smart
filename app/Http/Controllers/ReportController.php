<?php

namespace App\Http\Controllers;

use App\Models\AgendaKegiatan;
use App\Models\Keluarga;
use App\Models\KunjunganRumah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function downloadKeluargaReport(Request $request)
    {
        $userId = Auth::id();
        
        // Filter berdasarkan parameter
        $query = Keluarga::where('user_id', $userId);
        
        if ($request->filled('rt')) {
            $query->where('rt', $request->rt);
        }
        
        if ($request->filled('rw')) {
            $query->where('rw', $request->rw);
        }
        
        if ($request->filled('status_ekonomi')) {
            $query->where('status_ekonomi', $request->status_ekonomi);
        }
        
        if ($request->boolean('has_balita')) {
            $query->withBalita();
        }
        
        if ($request->boolean('has_ibu_hamil')) {
            $query->withIbuHamil();
        }
        
        $keluargas = $query->orderBy('rt')->orderBy('rw')->orderBy('nama_kepala_keluarga')->get();
        
        // Statistik
        $totalKeluarga = $keluargas->count();
        $totalAnggota = $keluargas->sum('jumlah_anggota');
        $totalBalita = $keluargas->sum('jumlah_balita');
        $totalIbuHamil = $keluargas->sum('jumlah_ibu_hamil');
        
        $data = [
            'title' => 'Laporan Data Keluarga',
            'keluargas' => $keluargas,
            'user' => Auth::user(),
            'date' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'filters' => $request->only(['rt', 'rw', 'status_ekonomi', 'has_balita', 'has_ibu_hamil']),
            'statistics' => [
                'total_keluarga' => $totalKeluarga,
                'total_anggota' => $totalAnggota,
                'total_balita' => $totalBalita,
                'total_ibu_hamil' => $totalIbuHamil,
            ]
        ];
        
        $pdf = Pdf::loadView('reports.keluarga', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'laporan-keluarga-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function downloadAgendaReport(Request $request)
    {
        $userId = Auth::id();
        
        // Filter berdasarkan parameter
        $query = AgendaKegiatan::where('user_id', $userId);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('jenis_kegiatan')) {
            $query->where('jenis_kegiatan', $request->jenis_kegiatan);
        }
        
        if ($request->filled('bulan')) {
            $bulan = explode('-', $request->bulan);
            if (count($bulan) === 2) {
                $query->whereMonth('tanggal_waktu', $bulan[1])
                      ->whereYear('tanggal_waktu', $bulan[0]);
            }
        }
        
        $agendas = $query->orderBy('tanggal_waktu', 'desc')->get();
        
        // Statistik
        $statistik = AgendaKegiatan::getStatistikKehadiran($userId);
        $totalKegiatan = $agendas->count();
        $kegiatanSelesai = $agendas->where('status', 'selesai')->count();
        $kegiatanAktif = $agendas->where('status', 'aktif')->count();
        
        $data = [
            'title' => 'Laporan Agenda Kegiatan',
            'agendas' => $agendas,
            'user' => Auth::user(),
            'date' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'filters' => $request->only(['status', 'jenis_kegiatan', 'bulan']),
            'statistics' => array_merge($statistik, [
                'total_kegiatan_laporan' => $totalKegiatan,
                'kegiatan_selesai' => $kegiatanSelesai,
                'kegiatan_aktif' => $kegiatanAktif,
            ])
        ];
        
        $pdf = Pdf::loadView('reports.agenda', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'laporan-agenda-kegiatan-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function downloadKunjunganReport(Request $request)
    {
        $userId = Auth::id();
        
        // Filter berdasarkan parameter
        $query = KunjunganRumah::where('user_id', $userId)->with('keluarga');
        
        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }
        
        if ($request->filled('bulan')) {
            $bulan = explode('-', $request->bulan);
            if (count($bulan) === 2) {
                $query->whereMonth('tanggal_kunjungan', $bulan[1])
                      ->whereYear('tanggal_kunjungan', $bulan[0]);
            }
        }
        
        if ($request->filled('rt')) {
            $query->whereHas('keluarga', function($q) use ($request) {
                $q->where('rt', $request->rt);
            });
        }
        
        $kunjungans = $query->orderBy('tanggal_kunjungan', 'desc')->get();
        
        // Statistik
        $statistikSanitasi = KunjunganRumah::getStatistikSanitasi($userId);
        $totalKunjungan = $kunjungans->count();
        $kunjunganPrioritasTinggi = $kunjungans->where('prioritas', 'tinggi')->count();
        $perluFollowUp = $kunjungans->where('perlu_kunjungan_ulang', true)->count();
        
        $data = [
            'title' => 'Laporan Kunjungan Rumah',
            'kunjungans' => $kunjungans,
            'user' => Auth::user(),
            'date' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'filters' => $request->only(['prioritas', 'bulan', 'rt']),
            'statistics' => array_merge($statistikSanitasi, [
                'total_kunjungan_laporan' => $totalKunjungan,
                'prioritas_tinggi' => $kunjunganPrioritasTinggi,
                'perlu_follow_up' => $perluFollowUp,
            ])
        ];
        
        $pdf = Pdf::loadView('reports.kunjungan', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'laporan-kunjungan-rumah-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function downloadRekapitulasiReport(Request $request)
    {
        $userId = Auth::id();
        
        // Data rekapitulasi
        $totalKeluarga = Keluarga::where('user_id', $userId)->count();
        $totalBalita = Keluarga::getTotalBalitaByUser($userId);
        $totalIbuHamil = Keluarga::getTotalIbuHamilByUser($userId);
        
        $kegiatanBulanIni = AgendaKegiatan::where('user_id', $userId)->bulanIni()->count();
        $kunjunganBulanIni = KunjunganRumah::where('user_id', $userId)->bulanIni()->count();
        
        $statistikKehadiran = AgendaKegiatan::getStatistikKehadiran($userId);
        $statistikSanitasi = KunjunganRumah::getStatistikSanitasi($userId);
        
        // Data per RT/RW
        $dataPerRT = Keluarga::where('user_id', $userId)
            ->selectRaw('rt, rw, COUNT(*) as total_keluarga, SUM(JSON_LENGTH(anggota_keluarga)) as total_anggota')
            ->groupBy('rt', 'rw')
            ->orderBy('rt')
            ->orderBy('rw')
            ->get();
        
        // Kegiatan terakhir
        $kegiatanTerakhir = AgendaKegiatan::where('user_id', $userId)
            ->orderBy('tanggal_waktu', 'desc')
            ->limit(5)
            ->get();
        
        // Kunjungan prioritas tinggi
        $kunjunganPrioritas = KunjunganRumah::where('user_id', $userId)
            ->prioritasTinggi()
            ->with('keluarga')
            ->orderBy('tanggal_kunjungan', 'desc')
            ->limit(10)
            ->get();
        
        $data = [
            'title' => 'Laporan Rekapitulasi Dasa Wisma',
            'user' => Auth::user(),
            'date' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'periode' => Carbon::now()->locale('id')->translatedFormat('F Y'),
            'summary' => [
                'total_keluarga' => $totalKeluarga,
                'total_balita' => $totalBalita,
                'total_ibu_hamil' => $totalIbuHamil,
                'kegiatan_bulan_ini' => $kegiatanBulanIni,
                'kunjungan_bulan_ini' => $kunjunganBulanIni,
            ],
            'statistik_kehadiran' => $statistikKehadiran,
            'statistik_sanitasi' => $statistikSanitasi,
            'data_per_rt' => $dataPerRT,
            'kegiatan_terakhir' => $kegiatanTerakhir,
            'kunjungan_prioritas' => $kunjunganPrioritas,
        ];
        
        $pdf = Pdf::loadView('reports.rekapitulasi', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'laporan-rekapitulasi-dasa-wisma-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
}