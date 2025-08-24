{{-- resources/views/reports/rekapitulasi.blade.php --}}
@extends('reports.layout')

@section('content')
    <!-- Executive Summary -->
    <div class="statistics">
        <div class="stat-card">
            <div class="stat-number">{{ $summary['total_keluarga'] }}</div>
            <div class="stat-label">Total Keluarga</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $summary['total_balita'] }}</div>
            <div class="stat-label">Total Balita</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $summary['kegiatan_bulan_ini'] }}</div>
            <div class="stat-label">Kegiatan Bulan Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $summary['kunjungan_bulan_ini'] }}</div>
            <div class="stat-label">Kunjungan Bulan Ini</div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="info-box mb-4">
        <h3 style="margin: 0 0 15px 0; color: #1e40af;">📊 Indikator Kinerja Utama (KPI)</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
            <div style="text-align: center; padding: 10px; background: #f0f9ff; border-radius: 6px;">
                <div style="font-size: 16px; font-weight: bold; color: #1e40af;">
                    {{ isset($statistik_kehadiran['persentase_kehadiran']) ? $statistik_kehadiran['persentase_kehadiran'] : 0 }}%
                </div>
                <div style="font-size: 11px; color: #64748b;">Tingkat Kehadiran Kegiatan</div>
            </div>
            <div style="text-align: center; padding: 10px; background: #f0fdf4; border-radius: 6px;">
                <div style="font-size: 16px; font-weight: bold; color: #16a34a;">
                    {{ isset($statistik_sanitasi['persentase_baik']) ? $statistik_sanitasi['persentase_baik'] : 0 }}%
                </div>
                <div style="font-size: 11px; color: #64748b;">Sanitasi Rumah Baik</div>
            </div>
            <div style="text-align: center; padding: 10px; background: #fefce8; border-radius: 6px;">
                <div style="font-size: 16px; font-weight: bold; color: #ca8a04;">
                    @php
                        $totalKeluarga = $summary['total_keluarga'] ?? 0;
                        $kunjunganBulanIni = $summary['kunjungan_bulan_ini'] ?? 0;
                        $coverage = $totalKeluarga > 0 ? round(($kunjunganBulanIni / $totalKeluarga) * 100, 1) : 0;
                    @endphp
                    {{ $coverage }}%
                </div>
                <div style="font-size: 11px; color: #64748b;">Cakupan Kunjungan</div>
            </div>
        </div>
    </div>

    <!-- Data Per RT/RW -->
    <h3 style="color: #1e40af; margin-bottom: 10px;">🏘️ Rekapitulasi Per RT/RW</h3>
    <table style="margin-bottom: 25px;">
        <thead>
            <tr>
                <th style="width: 10%">RT</th>
                <th style="width: 10%">RW</th>
                <th style="width: 15%">Jumlah Keluarga</th>
                <th style="width: 15%">Total Anggota</th>
                <th style="width: 12%">Balita</th>
                <th style="width: 12%">Ibu Hamil</th>
                <th style="width: 13%">Rata-rata/KK</th>
                <th style="width: 13%">Status Cakupan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data_per_rt as $data)
            <tr>
                <td class="text-center font-bold">{{ $data->rt }}</td>
                <td class="text-center font-bold">{{ $data->rw }}</td>
                <td class="text-center">{{ $data->total_keluarga }}</td>
                <td class="text-center">{{ $data->total_anggota ?? 0 }}</td>
                <td class="text-center">
                    @php
                        // Hitung balita untuk RT/RW ini
                        $balitaCount = \App\Models\Keluarga::where('user_id', auth()->id())
                            ->where('rt', $data->rt)
                            ->where('rw', $data->rw)
                            ->get()
                            ->sum('jumlah_balita');
                    @endphp
                    @if($balitaCount > 0)
                        <span class="badge badge-info">{{ $balitaCount }}</span>
                    @else
                        <span style="color: #64748b;">0</span>
                    @endif
                </td>
                <td class="text-center">
                    @php
                        // Hitung ibu hamil untuk RT/RW ini
                        $ibuHamilCount = \App\Models\Keluarga::where('user_id', auth()->id())
                            ->where('rt', $data->rt)
                            ->where('rw', $data->rw)
                            ->get()
                            ->sum('jumlah_ibu_hamil');
                    @endphp
                    @if($ibuHamilCount > 0)
                        <span class="badge badge-warning">{{ $ibuHamilCount }}</span>
                    @else
                        <span style="color: #64748b;">0</span>
                    @endif
                </td>
                <td class="text-center">
                    @php
                        $totalKeluargaData = $data->total_keluarga ?? 0;
                        $totalAnggotaData = $data->total_anggota ?? 0;
                        $rataRata = $totalKeluargaData > 0 ? round($totalAnggotaData / $totalKeluargaData, 1) : 0;
                    @endphp
                    {{ $rataRata }} orang
                </td>
                <td class="text-center">
                    @php
                        $totalKeluargaStatus = $data->total_keluarga ?? 0;
                        $statusBadge = $totalKeluargaStatus >= 10 ? 'badge-success' : ($totalKeluargaStatus >= 5 ? 'badge-warning' : 'badge-info');
                        $statusText = $totalKeluargaStatus >= 10 ? 'Tinggi' : ($totalKeluargaStatus >= 5 ? 'Sedang' : 'Rendah');
                    @endphp
                    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
            
            {{-- Total Summary Row --}}
            @if($data_per_rt->count() > 0)
            <tr style="background: #e5e7eb; font-weight: bold; border-top: 2px solid #374151;">
                <td colspan="2" class="text-center">TOTAL</td>
                <td class="text-center">{{ $data_per_rt->sum('total_keluarga') }}</td>
                <td class="text-center">{{ $data_per_rt->sum('total_anggota') }}</td>
                <td class="text-center">{{ $summary['total_balita'] }}</td>
                <td class="text-center">{{ $summary['total_ibu_hamil'] ?? 0 }}</td>
                <td class="text-center">
                    @php
                        $totalKeluargaSum = $data_per_rt->sum('total_keluarga');
                        $totalAnggotaSum = $data_per_rt->sum('total_anggota');
                        $avgTotal = $totalKeluargaSum > 0 ? round($totalAnggotaSum / $totalKeluargaSum, 1) : 0;
                    @endphp
                    {{ $avgTotal }} orang
                </td>
                <td class="text-center">
                    <span class="badge badge-info">{{ $data_per_rt->count() }} RT/RW</span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Statistik Kegiatan & Kehadiran -->
    @if(isset($statistik_kehadiran))
    <h3 style="color: #1e40af; margin-bottom: 10px;">📈 Statistik Kegiatan & Kehadiran</h3>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px;">
        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🎯 Pencapaian Kegiatan</h4>
            <div class="info-row">
                <span class="info-label">Total Kegiatan:</span>
                <span>{{ $statistik_kehadiran['total_kegiatan'] }} kegiatan</span>
            </div>
            <div class="info-row">
                <span class="info-label">Target Peserta:</span>
                <span>{{ $statistik_kehadiran['total_target'] }} orang</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Kehadiran:</span>
                <span>{{ $statistik_kehadiran['total_hadir'] }} orang</span>
            </div>
            <div class="info-row">
                <span class="info-label">Persentase Kehadiran:</span>
                <span class="font-bold" style="color: {{ ($statistik_kehadiran['persentase_kehadiran'] ?? 0) >= 80 ? '#16a34a' : (($statistik_kehadiran['persentase_kehadiran'] ?? 0) >= 60 ? '#ca8a04' : '#dc2626') }}">
                    {{ $statistik_kehadiran['persentase_kehadiran'] ?? 0 }}%
                </span>
            </div>
        </div>

        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">📊 Efektivitas Program</h4>
            <div class="info-row">
                <span class="info-label">Rata-rata per Kegiatan:</span>
                <span>
                    @php
                        $totalKegiatan = $statistik_kehadiran['total_kegiatan'] ?? 0;
                        $totalTarget = $statistik_kehadiran['total_target'] ?? 0;
                        $avgPerKegiatan = $totalKegiatan > 0 ? round($totalTarget / $totalKegiatan, 1) : 0;
                    @endphp
                    {{ $avgPerKegiatan }} target
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tingkat Partisipasi:</span>
                <span>
                    @php
                        $totalKeluargaPartisipasi = $summary['total_keluarga'] ?? 0;
                        $totalHadir = $statistik_kehadiran['total_hadir'] ?? 0;
                        $totalKegiatanPartisipasi = $statistik_kehadiran['total_kegiatan'] ?? 0;
                        $partisipasi = ($totalKeluargaPartisipasi > 0 && $totalKegiatanPartisipasi > 0) ? 
                            round(($totalHadir / ($totalKeluargaPartisipasi * $totalKegiatanPartisipasi)) * 100, 1) : 0;
                    @endphp
                    {{ $partisipasi }}%
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Cakupan Keluarga:</span>
                <span>
                    @php
                        $totalKeluargaCakupan = $summary['total_keluarga'] ?? 0;
                        $totalTargetCakupan = $statistik_kehadiran['total_target'] ?? 0;
                        $cakupanKeluarga = $totalKeluargaCakupan > 0 ? 
                            round(($totalTargetCakupan / $totalKeluargaCakupan) * 100, 1) : 0;
                    @endphp
                    {{ $cakupanKeluarga }}%
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Program:</span>
                <span>
                    @php
                        $persentaseKehadiran = $statistik_kehadiran['persentase_kehadiran'] ?? 0;
                        $statusProgram = $persentaseKehadiran >= 80 ? 'Sangat Baik' : 
                                       ($persentaseKehadiran >= 60 ? 'Baik' : 'Perlu Perbaikan');
                        $statusColor = $persentaseKehadiran >= 80 ? '#16a34a' : 
                                     ($persentaseKehadiran >= 60 ? '#ca8a04' : '#dc2626');
                    @endphp
                    <span style="color: {{ $statusColor }}; font-weight: bold;">{{ $statusProgram }}</span>
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistik Sanitasi -->
    @if(isset($statistik_sanitasi))
    <h3 style="color: #1e40af; margin-bottom: 10px;">🏠 Statistik Kondisi Sanitasi</h3>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px;">
        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">📋 Data Kunjungan</h4>
            <div class="info-row">
                <span class="info-label">Total Kunjungan:</span>
                <span>{{ $statistik_sanitasi['total_kunjungan'] }} kunjungan</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sanitasi Baik:</span>
                <span>{{ $statistik_sanitasi['sanitasi_baik'] }} rumah</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sanitasi Kurang:</span>
                <span>{{ $statistik_sanitasi['sanitasi_kurang'] }} rumah</span>
            </div>
            <div class="info-row">
                <span class="info-label">Persentase Sanitasi Baik:</span>
                <span class="font-bold" style="color: {{ ($statistik_sanitasi['persentase_baik'] ?? 0) >= 80 ? '#16a34a' : (($statistik_sanitasi['persentase_baik'] ?? 0) >= 60 ? '#ca8a04' : '#dc2626') }}">
                    {{ $statistik_sanitasi['persentase_baik'] ?? 0 }}%
                </span>
            </div>
        </div>

        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🎯 Target & Rekomendasi</h4>
            <div class="info-row">
                <span class="info-label">Target Sanitasi Baik:</span>
                <span>85%</span>
            </div>
            <div class="info-row">
                <span class="info-label">Gap Target:</span>
                <span>
                    @php
                        $persentaseBaik = $statistik_sanitasi['persentase_baik'] ?? 0;
                        $gap = 85 - $persentaseBaik;
                    @endphp
                    {{ $gap > 0 ? $gap : 0 }}%
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Rumah Perlu Perbaikan:</span>
                <span>{{ $statistik_sanitasi['sanitasi_kurang'] }} rumah</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Sanitasi:</span>
                <span>
                    @php
                        $persentaseBaikStatus = $statistik_sanitasi['persentase_baik'] ?? 0;
                        $statusSanitasi = $persentaseBaikStatus >= 85 ? 'Target Tercapai' : 
                                        ($persentaseBaikStatus >= 70 ? 'Mendekati Target' : 'Perlu Perbaikan');
                        $statusColor = $persentaseBaikStatus >= 85 ? '#16a34a' : 
                                     ($persentaseBaikStatus >= 70 ? '#ca8a04' : '#dc2626');
                    @endphp
                    <span style="color: {{ $statusColor }}; font-weight: bold;">{{ $statusSanitasi }}</span>
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 5 Kegiatan Terakhir -->
    <h3 style="color: #1e40af; margin-bottom: 10px;">📅 5 Kegiatan Terakhir</h3>
    <table style="margin-bottom: 25px;">
        <thead>
            <tr>
                <th style="width: 35%">Nama Kegiatan</th>
                <th style="width: 12%">Jenis</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 10%">Status</th>
                <th style="width: 12%">Kehadiran</th>
                <th style="width: 19%">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kegiatan_terakhir as $kegiatan)
            <tr>
                <td class="font-bold">{{ $kegiatan->nama_kegiatan }}</td>
                <td class="text-sm">
                    @php
                        $jenisLabels = [
                            'posyandu' => '🏥 Posyandu',
                            'penyuluhan' => '📢 Penyuluhan',
                            'imunisasi' => '💉 Imunisasi',
                            'pemeriksaan' => '🩺 Pemeriksaan',
                            'sosialisasi' => '📣 Sosialisasi',
                            'gotong_royong' => '🤝 Gotong Royong',
                            'rapat' => '👥 Rapat',
                            'pelatihan' => '🎓 Pelatihan',
                            'lainnya' => '📋 Lainnya',
                        ];
                    @endphp
                    {{ $jenisLabels[$kegiatan->jenis_kegiatan] ?? $kegiatan->jenis_kegiatan }}
                </td>
                <td class="text-sm">{{ $kegiatan->tanggal_waktu->format('d/m/Y') }}</td>
                <td>
                    @php
                        $badgeClass = match($kegiatan->status) {
                            'selesai' => 'badge-success',
                            'aktif' => 'badge-warning',
                            'draft' => 'badge-gray',
                            'dibatalkan' => 'badge-danger',
                            default => 'badge-gray'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($kegiatan->status) }}</span>
                </td>
                <td class="text-center">
                    @php
                        $totalTargetKegiatan = $kegiatan->total_target ?? 0;
                        $totalHadirKegiatan = $kegiatan->total_hadir ?? 0;
                    @endphp
                    @if($totalTargetKegiatan > 0)
                        <strong>{{ round(($totalHadirKegiatan / $totalTargetKegiatan) * 100) }}%</strong>
                        <br>
                        <span class="text-sm" style="color: #64748b;">
                            ({{ $totalHadirKegiatan }}/{{ $totalTargetKegiatan }})
                        </span>
                    @else
                        <span style="color: #64748b;">-</span>
                    @endif
                </td>
                <td class="text-sm">{{ $kegiatan->penanggung_jawab }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada kegiatan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Kunjungan Prioritas Tinggi -->
    <h3 style="color: #1e40af; margin-bottom: 10px;">🚨 Kunjungan Prioritas Tinggi</h3>
    <table style="margin-bottom: 25px;">
        <thead>
            <tr>
                <th style="width: 25%">Keluarga</th>
                <th style="width: 12%">RT/RW</th>
                <th style="width: 12%">Tanggal Kunjungan</th>
                <th style="width: 10%">Skor Sanitasi</th>
                <th style="width: 12%">Follow Up</th>
                <th style="width: 29%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kunjungan_prioritas as $kunjungan)
            <tr>
                <td class="font-bold">{{ $kunjungan->keluarga->nama_kepala_keluarga ?? 'N/A' }}</td>
                <td class="text-center">{{ $kunjungan->keluarga->rt_rw ?? 'N/A' }}</td>
                <td class="text-sm">{{ $kunjungan->tanggal_kunjungan->format('d/m/Y') }}</td>
                <td class="text-center">
                    @php
                        $score = 0;
                        if ($kunjungan->air_bersih === 'baik') $score++;
                        if ($kunjungan->jamban === 'sehat') $score++;
                        if ($kunjungan->sampah === 'baik') $score++;
                        if ($kunjungan->ventilasi === 'baik') $score++;
                    @endphp
                    <span class="badge badge-danger">{{ $score }}/4</span>
                </td>
                <td class="text-center">
                    @if($kunjungan->perlu_kunjungan_ulang)
                        @if($kunjungan->tanggal_kunjungan_ulang && $kunjungan->tanggal_kunjungan_ulang < now())
                            <span class="badge badge-danger">Terlambat</span>
                        @else
                            <span class="badge badge-warning">Dijadwalkan</span>
                        @endif
                    @else
                        <span class="badge badge-gray">Tidak</span>
                    @endif
                </td>
                <td class="text-sm">{{ Str::limit($kunjungan->catatan_kondisi_keluarga, 80) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada kunjungan prioritas tinggi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Analisis dan Rekomendasi -->
    <div class="page-break"></div>
    <h3 style="color: #1e40af; margin-bottom: 15px;">💡 Analisis & Rekomendasi</h3>
    
    <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
        <!-- Pencapaian Positif -->
        <div class="info-box" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <h4 style="margin: 0 0 10px 0; color: #16a34a;">✅ Pencapaian Positif</h4>
            <ul style="margin: 0; padding-left: 20px;">
                @php
                    $persentaseKehadiranAnalisis = $statistik_kehadiran['persentase_kehadiran'] ?? 0;
                    $persentaseBaikAnalisis = $statistik_sanitasi['persentase_baik'] ?? 0;
                @endphp
                @if($persentaseKehadiranAnalisis >= 70)
                    <li>Tingkat kehadiran kegiatan mencapai {{ $persentaseKehadiranAnalisis }}% (di atas standar 70%)</li>
                @endif
                @if($persentaseBaikAnalisis >= 60)
                    <li>{{ $persentaseBaikAnalisis }}% rumah memiliki kondisi sanitasi yang baik</li>
                @endif
                @if($summary['total_balita'] > 0)
                    <li>Berhasil mencakup {{ $summary['total_balita'] }} balita dalam program kesehatan</li>
                @endif
                @if($data_per_rt->count() > 0)
                    <li>Cakupan wilayah mencapai {{ $data_per_rt->count() }} RT/RW</li>
                @endif
            </ul>
        </div>

        <!-- Area Perbaikan -->
        <div class="info-box" style="background: #fef3c7; border: 1px solid #fbbf24;">
            <h4 style="margin: 0 0 10px 0; color: #d97706;">⚠️ Area yang Perlu Perbaikan</h4>
            <ul style="margin: 0; padding-left: 20px;">
                @if($persentaseKehadiranAnalisis < 70)
                    <li>Tingkat kehadiran kegiatan {{ $persentaseKehadiranAnalisis }}% masih di bawah target 70%</li>
                @endif
                @if($persentaseBaikAnalisis < 80)
                    <li>{{ $statistik_sanitasi['sanitasi_kurang'] ?? 0 }} rumah masih memiliki kondisi sanitasi yang perlu diperbaiki</li>
                @endif
                @if($kunjungan_prioritas->count() > 0)
                    <li>{{ $kunjungan_prioritas->count() }} keluarga memerlukan perhatian khusus dengan prioritas tinggi</li>
                @endif
                @php
                    $perluFollowUp = $kunjungan_prioritas->where('perlu_kunjungan_ulang', true)->count();
                @endphp
                @if($perluFollowUp > 0)
                    <li>{{ $perluFollowUp }} keluarga memerlukan kunjungan follow-up</li>
                @endif
            </ul>
        </div>

        <!-- Rekomendasi Strategis -->
        <div class="info-box" style="background: #dbeafe; border: 1px solid #60a5fa;">
            <h4 style="margin: 0 0 10px 0; color: #2563eb;">🎯 Rekomendasi Strategis</h4>
            <ol style="margin: 0; padding-left: 20px;">
                @if($persentaseKehadiranAnalisis < 70)
                    <li><strong>Tingkatkan Partisipasi:</strong> Adakan sosialisasi lebih intensif dan perbaiki jadwal kegiatan</li>
                @endif
                @php
                    $sanitasiKurang = $statistik_sanitasi['sanitasi_kurang'] ?? 0;
                @endphp
                @if($sanitasiKurang > 0)
                    <li><strong>Program Perbaikan Sanitasi:</strong> Fokus pada {{ $sanitasiKurang }} rumah dengan kondisi sanitasi kurang</li>
                @endif
                @if($kunjungan_prioritas->count() > 0)
                    <li><strong>Intervensi Khusus:</strong> Berikan pendampingan intensif untuk keluarga prioritas tinggi</li>
                @endif
                <li><strong>Peningkatan Kapasitas:</strong> Lakukan pelatihan untuk petugas Dasa Wisma</li>
                <li><strong>Monitoring Berkala:</strong> Implementasikan sistem monitoring dan evaluasi rutin</li>
            </ol>
        </div>

        <!-- Target Jangka Pendek -->
        <div class="info-box" style="background: #f3e8ff; border: 1px solid #c084fc;">
            <h4 style="margin: 0 0 10px 0; color: #7c3aed;">📈 Target 3 Bulan Ke Depan</h4>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Meningkatkan tingkat kehadiran kegiatan menjadi {{ max(80, $persentaseKehadiranAnalisis + 10) }}%</li>
                <li>Mencapai {{ max(85, $persentaseBaikAnalisis + 15) }}% rumah dengan sanitasi baik</li>
                <li>Menyelesaikan follow-up untuk semua keluarga prioritas tinggi</li>
                <li>Melakukan kunjungan rutin ke minimal 80% keluarga per bulan</li>
                <li>Mengadakan minimal {{ ($statistik_kehadiran['total_kegiatan'] ?? 0) + 2 }} kegiatan per bulan</li>
            </ul>
        </div>
    </div>

    <!-- Footer Summary -->
    <div style="margin-top: 30px; padding: 15px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
        <h4 style="margin: 0 0 10px 0; color: #374151; text-align: center;">📊 Ringkasan Eksekutif</h4>
        <p style="margin: 0; text-align: center; font-size: 11px; color: #64748b; line-height: 1.5;">
            Program Dasa Wisma melayani <strong>{{ $summary['total_keluarga'] }} keluarga</strong> dengan 
            <strong>{{ $summary['total_balita'] }} balita</strong> dan 
            <strong>{{ $summary['total_ibu_hamil'] ?? 0 }} ibu hamil</strong>. 
            Tingkat kehadiran kegiatan <strong>{{ $statistik_kehadiran['persentase_kehadiran'] ?? 0 }}%</strong> dan 
            <strong>{{ $statistik_sanitasi['persentase_baik'] ?? 0 }}%</strong> rumah memiliki sanitasi baik. 
            Dengan {{ $kunjungan_prioritas->count() }} keluarga prioritas tinggi yang memerlukan perhatian khusus.
        </p>
    </div>
@endsection