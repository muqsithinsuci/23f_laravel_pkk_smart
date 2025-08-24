{{-- resources/views/reports/kunjungan.blade.php --}}
@extends('reports.layout')

@section('content')
    @if(isset($statistics))
    <div class="statistics">
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_kunjungan_laporan'] ?? 0 }}</div>
            <div class="stat-label">Total Kunjungan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['sanitasi_baik'] ?? 0 }}</div>
            <div class="stat-label">Sanitasi Baik</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['prioritas_tinggi'] ?? 0 }}</div>
            <div class="stat-label">Prioritas Tinggi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['persentase_baik'] ?? 0 }}%</div>
            <div class="stat-label">% Sanitasi Baik</div>
        </div>
    </div>
    @endif

    @if(isset($statistics) && $statistics['total_kunjungan'] > 0)
    <div class="info-box mb-4">
        <h3 style="margin: 0 0 10px 0; color: #1e40af;">🏠 Analisis Kondisi Sanitasi</h3>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
            <div style="text-align: center; padding: 5px;">
                <span class="badge badge-success">
                    Sangat Baik: {{ $statistics['sanitasi_sangat_baik'] ?? 0 }}
                </span>
            </div>
            <div style="text-align: center; padding: 5px;">
                <span class="badge badge-info">
                    Baik: {{ $statistics['sanitasi_baik'] ?? 0 }}
                </span>
            </div>
            <div style="text-align: center; padding: 5px;">
                <span class="badge badge-warning">
                    Cukup: {{ $statistics['sanitasi_cukup'] ?? 0 }}
                </span>
            </div>
            <div style="text-align: center; padding: 5px;">
                <span class="badge badge-danger">
                    Kurang: {{ $statistics['sanitasi_kurang'] ?? 0 }}
                </span>
            </div>
        </div>
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: 18%">Keluarga</th>
                <th style="width: 8%">RT/RW</th>
                <th style="width: 10%">Tanggal</th>
                <th style="width: 8%">Sanitasi</th>
                <th style="width: 8%">Prioritas</th>
                <th style="width: 6%">Follow Up</th>
                <th style="width: 18%">Tujuan Kunjungan</th>
                <th style="width: 20%">Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kunjungans as $index => $kunjungan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $kunjungan->keluarga->nama_kepala_keluarga ?? 'N/A' }}</td>
                <td class="text-center">
                    @if($kunjungan->keluarga)
                        RT {{ $kunjungan->keluarga->rt }}/RW {{ $kunjungan->keluarga->rw }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-sm">{{ $kunjungan->tanggal_kunjungan->format('d/m/Y') }}</td>
                <td class="text-center">
                    @php
                        $score = 0;
                        if ($kunjungan->air_bersih === 'baik') $score++;
                        if ($kunjungan->jamban === 'sehat') $score++;
                        if ($kunjungan->sampah === 'baik') $score++;
                        if ($kunjungan->ventilasi === 'baik') $score++;
                        
                        $badgeClass = match(true) {
                            $score === 4 => 'badge-success',
                            $score === 3 => 'badge-info',
                            $score === 2 => 'badge-warning',
                            default => 'badge-danger'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $score }}/4</span>
                </td>
                <td>
                    @php
                        $prioritasBadge = match($kunjungan->prioritas) {
                            'tinggi' => 'badge-danger',
                            'sedang' => 'badge-warning',
                            'rendah' => 'badge-success',
                            default => 'badge-gray'
                        };
                    @endphp
                    <span class="badge {{ $prioritasBadge }}">{{ ucfirst($kunjungan->prioritas) }}</span>
                </td>
                <td class="text-center">
                    @if($kunjungan->perlu_kunjungan_ulang)
                        @if($kunjungan->tanggal_kunjungan_ulang && $kunjungan->tanggal_kunjungan_ulang < now())
                            <span class="badge badge-danger">Terlambat</span>
                        @else
                            <span class="badge badge-warning">Ya</span>
                        @endif
                    @else
                        <span class="badge badge-gray">Tidak</span>
                    @endif
                </td>
                <td class="text-sm">
                    @if(is_array($kunjungan->tujuan_kunjungan))
                        @php
                            $tujuanMapping = [
                                'cek_balita' => 'Cek Balita',
                                'sosialisasi' => 'Sosialisasi',
                                'pendataan' => 'Pendataan',
                                'pemantauan' => 'Pemantauan',
                                'edukasi' => 'Edukasi',
                            ];
                        @endphp
                        {{ collect($kunjungan->tujuan_kunjungan)->map(fn($t) => $tujuanMapping[$t] ?? $t)->join(', ') }}
                    @endif
                </td>
                <td class="text-sm">{{ Str::limit($kunjungan->rekomendasi, 60) }}</td>
            </tr>
            
            {{-- Detail Sanitasi --}}
            <tr style="background: #f8fafc;">
                <td></td>
                <td colspan="8" style="padding: 8px;">
                    <div style="font-size: 10px; color: #475569;">
                        <strong>Detail Sanitasi:</strong>
                        🚰 Air: {{ ucfirst($kunjungan->air_bersih) }} |
                        🚽 Jamban: {{ ucfirst($kunjungan->jamban) }} |
                        🗑️ Sampah: {{ ucfirst($kunjungan->sampah) }} |
                        🌬️ Ventilasi: {{ ucfirst($kunjungan->ventilasi) }}
                        
                        @if($kunjungan->catatan_kondisi_keluarga)
                            <br><strong>Catatan:</strong> {{ Str::limit($kunjungan->catatan_kondisi_keluarga, 120) }}
                        @endif
                        
                        @if($kunjungan->perlu_kunjungan_ulang && $kunjungan->tanggal_kunjungan_ulang)
                            <br><strong>Kunjungan Ulang:</strong> {{ $kunjungan->tanggal_kunjungan_ulang->format('d/m/Y') }}
                        @endif
                    </div>
                </td>
            </tr>
            
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                    Tidak ada data kunjungan rumah ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary per RT/RW --}}
    @if($kunjungans->count() > 0)
    <div class="page-break"></div>
    
    <h3 style="color: #1e40af; margin-bottom: 15px;">📊 Ringkasan Kunjungan per RT/RW</h3>
    
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 15%">RT/RW</th>
                <th style="width: 15%">Total Kunjungan</th>
                <th style="width: 20%">Rata-rata Skor Sanitasi</th>
                <th style="width: 15%">Prioritas Tinggi</th>
                <th style="width: 15%">Perlu Follow Up</th>
                <th style="width: 20%">Terakhir Dikunjungi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedByRtRw = $kunjungans->groupBy(function($item) {
                    return $item->keluarga ? "RT {$item->keluarga->rt}/RW {$item->keluarga->rw}" : 'N/A';
                });
            @endphp
            
            @foreach($groupedByRtRw as $rtRw => $group)
                @php
                    $totalKunjungan = $group->count();
                    $avgSanitasi = $group->map(function($k) {
                        $score = 0;
                        if ($k->air_bersih === 'baik') $score++;
                        if ($k->jamban === 'sehat') $score++;
                        if ($k->sampah === 'baik') $score++;
                        if ($k->ventilasi === 'baik') $score++;
                        return $score;
                    })->avg();
                    $prioritasTinggi = $group->where('prioritas', 'tinggi')->count();
                    $perluFollowUp = $group->where('perlu_kunjungan_ulang', true)->count();
                    $terakhirKunjungan = $group->max('tanggal_kunjungan');
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $rtRw }}</td>
                    <td class="text-center">{{ $totalKunjungan }}</td>
                    <td class="text-center">
                        @php
                            $avgScore = round($avgSanitasi, 1);
                            $scoreColor = $avgScore >= 3 ? 'success' : ($avgScore >= 2 ? 'warning' : 'danger');
                        @endphp
                        <span class="badge badge-{{ $scoreColor }}">{{ $avgScore }}/4</span>
                    </td>
                    <td class="text-center">
                        @if($prioritasTinggi > 0)
                            <span class="badge badge-danger">{{ $prioritasTinggi }}</span>
                        @else
                            <span style="color: #64748b;">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($perluFollowUp > 0)
                            <span class="badge badge-warning">{{ $perluFollowUp }}</span>
                        @else
                            <span style="color: #64748b;">0</span>
                        @endif
                    </td>
                    <td class="text-center text-sm">
                        {{ $terakhirKunjungan ? \Carbon\Carbon::parse($terakhirKunjungan)->format('d/m/Y') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Kunjungan Prioritas Tinggi --}}
    @php
        $prioritasTinggi = $kunjungans->where('prioritas', 'tinggi')->sortByDesc('tanggal_kunjungan');
    @endphp
    
    @if($prioritasTinggi->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">🚨 Kunjungan Prioritas Tinggi</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 25%">Keluarga</th>
                <th style="width: 12%">RT/RW</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 10%">Skor Sanitasi</th>
                <th style="width: 36%">Masalah & Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prioritasTinggi->take(15) as $index => $kunjungan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $kunjungan->keluarga->nama_kepala_keluarga ?? 'N/A' }}</td>
                <td class="text-center">
                    @if($kunjungan->keluarga)
                        RT {{ $kunjungan->keluarga->rt }}/RW {{ $kunjungan->keluarga->rw }}
                    @else
                        N/A
                    @endif
                </td>
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
                <td class="text-sm">
                    @if($kunjungan->catatan_kondisi_keluarga)
                        <strong>Masalah:</strong> {{ Str::limit($kunjungan->catatan_kondisi_keluarga, 80) }}<br>
                    @endif
                    @if($kunjungan->rekomendasi)
                        <strong>Rekomendasi:</strong> {{ Str::limit($kunjungan->rekomendasi, 80) }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Follow Up yang Diperlukan --}}
    @php
        $perluFollowUp = $kunjungans->where('perlu_kunjungan_ulang', true)->sortBy('tanggal_kunjungan_ulang');
    @endphp
    
    @if($perluFollowUp->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">📅 Jadwal Follow Up</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 25%">Keluarga</th>
                <th style="width: 12%">RT/RW</th>
                <th style="width: 12%">Kunjungan Terakhir</th>
                <th style="width: 12%">Jadwal Follow Up</th>
                <th style="width: 8%">Status</th>
                <th style="width: 26%">Catatan Follow Up</th>
            </tr>
        </thead>
        <tbody>
            @foreach($perluFollowUp->take(20) as $index => $kunjungan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $kunjungan->keluarga->nama_kepala_keluarga ?? 'N/A' }}</td>
                <td class="text-center">
                    @if($kunjungan->keluarga)
                        RT {{ $kunjungan->keluarga->rt }}/RW {{ $kunjungan->keluarga->rw }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-sm">{{ $kunjungan->tanggal_kunjungan->format('d/m/Y') }}</td>
                <td class="text-sm">
                    @if($kunjungan->tanggal_kunjungan_ulang)
                        {{ $kunjungan->tanggal_kunjungan_ulang->format('d/m/Y') }}
                    @else
                        <span style="color: #64748b;">Belum dijadwalkan</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($kunjungan->tanggal_kunjungan_ulang)
                        @if($kunjungan->tanggal_kunjungan_ulang < now())
                            <span class="badge badge-danger">Terlambat</span>
                        @elseif($kunjungan->tanggal_kunjungan_ulang <= now()->addDays(7))
                            <span class="badge badge-warning">Segera</span>
                        @else
                            <span class="badge badge-info">Terjadwal</span>
                        @endif
                    @else
                        <span class="badge badge-gray">Pending</span>
                    @endif
                </td>
                <td class="text-sm">{{ Str::limit($kunjungan->catatan_follow_up, 60) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Analisis Kondisi Sanitasi Detail --}}
    <div class="page-break"></div>
    <h3 style="color: #1e40af; margin-bottom: 15px;">🔍 Analisis Detail Kondisi Sanitasi</h3>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
        {{-- Air Bersih --}}
        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🚰 Kondisi Air Bersih</h4>
            @php
                $airStats = $kunjungans->groupBy('air_bersih')->map->count();
            @endphp
            @foreach(['baik', 'kurang', 'buruk'] as $kondisi)
                <div class="info-row">
                    <span class="info-label">{{ ucfirst($kondisi) }}:</span>
                    <span>{{ $airStats[$kondisi] ?? 0 }} rumah</span>
                </div>
            @endforeach
        </div>

        {{-- Jamban --}}
        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🚽 Kondisi Jamban</h4>
            @php
                $jambanStats = $kunjungans->groupBy('jamban')->map->count();
            @endphp
            @foreach(['sehat', 'tidak_sehat'] as $kondisi)
                <div class="info-row">
                    <span class="info-label">{{ $kondisi === 'sehat' ? 'Sehat' : 'Tidak Sehat' }}:</span>
                    <span>{{ $jambanStats[$kondisi] ?? 0 }} rumah</span>
                </div>
            @endforeach
        </div>

        {{-- Sampah --}}
        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🗑️ Pengelolaan Sampah</h4>
            @php
                $sampahStats = $kunjungans->groupBy('sampah')->map->count();
            @endphp
            @foreach(['baik', 'kurang', 'buruk'] as $kondisi)
                <div class="info-row">
                    <span class="info-label">{{ ucfirst($kondisi) }}:</span>
                    <span>{{ $sampahStats[$kondisi] ?? 0 }} rumah</span>
                </div>
            @endforeach
        </div>

        {{-- Ventilasi --}}
        <div class="info-box">
            <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🌬️ Ventilasi</h4>
            @php
                $ventilasiStats = $kunjungans->groupBy('ventilasi')->map->count();
            @endphp
            @foreach(['baik', 'kurang', 'buruk'] as $kondisi)
                <div class="info-row">
                    <span class="info-label">{{ ucfirst($kondisi) }}:</span>
                    <span>{{ $ventilasiStats[$kondisi] ?? 0 }} rumah</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Rekomendasi Umum --}}
    <div class="info-box">
        <h4 style="margin: 0 0 10px 0; color: #dc2626;">💡 Rekomendasi Perbaikan</h4>
        
        @php
            $masalahUmum = [];
            
            // Analisis masalah berdasarkan kondisi sanitasi
            $airBuruk = $kunjungans->where('air_bersih', '!=', 'baik')->count();
            $jambanBuruk = $kunjungans->where('jamban', '!=', 'sehat')->count();
            $sampahBuruk = $kunjungans->where('sampah', '!=', 'baik')->count();
            $ventilasiBuruk = $kunjungans->where('ventilasi', '!=', 'baik')->count();
            
            if ($airBuruk > 0) {
                $masalahUmum[] = "Perbaikan kualitas air bersih di {$airBuruk} rumah";
            }
            if ($jambanBuruk > 0) {
                $masalahUmum[] = "Perbaikan kondisi jamban di {$jambanBuruk} rumah";
            }
            if ($sampahBuruk > 0) {
                $masalahUmum[] = "Perbaikan pengelolaan sampah di {$sampahBuruk} rumah";
            }
            if ($ventilasiBuruk > 0) {
                $masalahUmum[] = "Perbaikan ventilasi di {$ventilasiBuruk} rumah";
            }
        @endphp
        
        @if(count($masalahUmum) > 0)
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($masalahUmum as $masalah)
                    <li style="margin-bottom: 5px;">{{ $masalah }}</li>
                @endforeach
            </ul>
        @else
            <p style="margin: 0; color: #16a34a;">✅ Kondisi sanitasi secara umum sudah baik!</p>
        @endif
    </div>
    @endif
@endsection