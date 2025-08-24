{{-- resources/views/reports/agenda.blade.php --}}
@extends('reports.layout')

@section('content')
    @if(isset($statistics))
    <div class="statistics">
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_kegiatan_laporan'] ?? 0 }}</div>
            <div class="stat-label">Total Kegiatan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['kegiatan_selesai'] ?? 0 }}</div>
            <div class="stat-label">Selesai</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_target'] ?? 0 }}</div>
            <div class="stat-label">Total Target</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['persentase_kehadiran'] ?? 0 }}%</div>
            <div class="stat-label">Kehadiran</div>
        </div>
    </div>
    @endif

    @if(isset($statistics['by_status']) && count($statistics['by_status']) > 0)
    <div class="info-box mb-4">
        <h3 style="margin: 0 0 10px 0; color: #1e40af;">📊 Distribusi Status Kegiatan</h3>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
            @foreach($statistics['by_status'] as $status => $count)
            <div style="text-align: center; padding: 5px;">
                @php
                    $badgeClass = match($status) {
                        'selesai' => 'badge-success',
                        'aktif' => 'badge-warning',
                        'draft' => 'badge-gray',
                        'dibatalkan' => 'badge-danger',
                        default => 'badge-gray'
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">
                    {{ ucfirst($status) }}: {{ $count }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: 22%">Nama Kegiatan</th>
                <th style="width: 12%">Jenis</th>
                <th style="width: 12%">Tanggal & Waktu</th>
                <th style="width: 10%">Status</th>
                <th style="width: 8%">Target</th>
                <th style="width: 8%">Hadir</th>
                <th style="width: 12%">Tempat</th>
                <th style="width: 12%">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agendas as $index => $agenda)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $agenda->nama_kegiatan }}</td>
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
                    {{ $jenisLabels[$agenda->jenis_kegiatan] ?? $agenda->jenis_kegiatan }}
                </td>
                <td class="text-sm">{{ $agenda->tanggal_waktu->format('d/m/Y H:i') }}</td>
                <td>
                    @php
                        $badgeClass = match($agenda->status) {
                            'selesai' => 'badge-success',
                            'aktif' => 'badge-warning',
                            'draft' => 'badge-gray',
                            'dibatalkan' => 'badge-danger',
                            default => 'badge-gray'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($agenda->status) }}</span>
                </td>
                <td class="text-center">{{ $agenda->total_target ?? '-' }}</td>
                <td class="text-center">
                    @if($agenda->total_target && $agenda->total_hadir !== null && $agenda->total_target > 0)
                        <span class="font-bold">{{ $agenda->total_hadir }}</span>
                        <br>
                        <span class="text-sm" style="color: #64748b;">
                            ({{ round(($agenda->total_hadir / $agenda->total_target) * 100) }}%)
                        </span>
                    @else
                        <span style="color: #64748b;">-</span>
                    @endif
                </td>
                <td class="text-sm">{{ Str::limit($agenda->tempat, 20) }}</td>
                <td class="text-sm">{{ $agenda->penanggung_jawab }}</td>
            </tr>
            
            {{-- Detail Kegiatan jika ada deskripsi --}}
            @if($agenda->deskripsi || $agenda->hasil_kegiatan)
            <tr style="background: #f8fafc;">
                <td></td>
                <td colspan="8" style="padding: 8px;">
                    <div style="font-size: 10px; color: #475569;">
                        @if($agenda->deskripsi)
                            <strong>Deskripsi:</strong> {{ Str::limit($agenda->deskripsi, 150) }}<br>
                        @endif
                        @if($agenda->hasil_kegiatan)
                            <strong>Hasil:</strong> {{ Str::limit($agenda->hasil_kegiatan, 150) }}<br>
                        @endif
                        @if($agenda->tingkat_kepuasan)
                            <strong>Kepuasan:</strong> 
                            @php
                                $kepuasanLabels = [
                                    'sangat_puas' => '😄 Sangat Puas',
                                    'puas' => '🙂 Puas',
                                    'cukup' => '😐 Cukup',
                                    'kurang_puas' => '😕 Kurang Puas',
                                    'tidak_puas' => '😞 Tidak Puas',
                                ];
                            @endphp
                            {{ $kepuasanLabels[$agenda->tingkat_kepuasan] ?? $agenda->tingkat_kepuasan }}
                        @endif
                    </div>
                </td>
            </tr>
            @endif
            
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                    Tidak ada data agenda kegiatan ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary per Jenis Kegiatan --}}
    @if($agendas->count() > 0)
    <div class="page-break"></div>
    
    <h3 style="color: #1e40af; margin-bottom: 15px;">📈 Ringkasan per Jenis Kegiatan</h3>
    
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 30%">Jenis Kegiatan</th>
                <th style="width: 15%">Total</th>
                <th style="width: 15%">Selesai</th>
                <th style="width: 15%">Aktif</th>
                <th style="width: 15%">Target Peserta</th>
                <th style="width: 10%">Rata-rata Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedByJenis = $agendas->groupBy('jenis_kegiatan');
                $jenisLabels = [
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
            @endphp
            
            @foreach($groupedByJenis as $jenis => $group)
                @php
                    $total = $group->count();
                    $selesai = $group->where('status', 'selesai')->count();
                    $aktif = $group->where('status', 'aktif')->count();
                    $totalTarget = $group->sum('total_target') ?? 0;
                    $totalHadir = $group->sum('total_hadir') ?? 0;
                    // Fix division by zero error
                    $avgKehadiran = $totalTarget > 0 ? round(($totalHadir / $totalTarget) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="font-bold">{{ $jenisLabels[$jenis] ?? ucfirst($jenis) }}</td>
                    <td class="text-center">{{ $total }}</td>
                    <td class="text-center">
                        @if($selesai > 0)
                            <span class="badge badge-success">{{ $selesai }}</span>
                        @else
                            <span style="color: #64748b;">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($aktif > 0)
                            <span class="badge badge-warning">{{ $aktif }}</span>
                        @else
                            <span style="color: #64748b;">0</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $totalTarget }}</td>
                    <td class="text-center">
                        @if($avgKehadiran > 0)
                            <span class="font-bold">{{ $avgKehadiran }}%</span>
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Kegiatan Mendatang --}}
    @php
        $kegiatanMendatang = $agendas->filter(function($agenda) {
            return $agenda->tanggal_waktu->isFuture() && in_array($agenda->status, ['aktif', 'draft']);
        })->sortBy('tanggal_waktu');
    @endphp
    
    @if($kegiatanMendatang->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">🗓️ Kegiatan Mendatang</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 35%">Nama Kegiatan</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 15%">Waktu</th>
                <th style="width: 15%">Status</th>
                <th style="width: 15%">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kegiatanMendatang->take(10) as $index => $agenda)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $agenda->nama_kegiatan }}</td>
                <td class="text-center">{{ $agenda->tanggal_waktu->format('d/m/Y') }}</td>
                <td class="text-center">{{ $agenda->tanggal_waktu->format('H:i') }}</td>
                <td class="text-center">
                    <span class="badge {{ $agenda->status === 'aktif' ? 'badge-warning' : 'badge-gray' }}">
                        {{ ucfirst($agenda->status) }}
                    </span>
                </td>
                <td class="text-sm">{{ $agenda->penanggung_jawab }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Kegiatan dengan Tingkat Kepuasan Tinggi --}}
    @php
        $kegiatanBerkualitas = $agendas->filter(function($agenda) {
            return in_array($agenda->tingkat_kepuasan, ['sangat_puas', 'puas']) && $agenda->status === 'selesai';
        })->sortByDesc(function($agenda) {
            return $agenda->tingkat_kepuasan === 'sangat_puas' ? 2 : 1;
        });
    @endphp
    
    @if($kegiatanBerkualitas->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">⭐ Kegiatan dengan Kepuasan Tinggi</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 30%">Nama Kegiatan</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 15%">Tingkat Kepuasan</th>
                <th style="width: 10%">Kehadiran</th>
                <th style="width: 25%">Hasil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kegiatanBerkualitas->take(10) as $index => $agenda)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $agenda->nama_kegiatan }}</td>
                <td class="text-center">{{ $agenda->tanggal_waktu->format('d/m/Y') }}</td>
                <td class="text-center">
                    @php
                        $kepuasanBadge = $agenda->tingkat_kepuasan === 'sangat_puas' ? 'badge-success' : 'badge-info';
                    @endphp
                    <span class="badge {{ $kepuasanBadge }}">
                        {{ $agenda->tingkat_kepuasan === 'sangat_puas' ? '😄 Sangat Puas' : '🙂 Puas' }}
                    </span>
                </td>
                <td class="text-center">
                    @if($agenda->total_target && $agenda->total_hadir !== null && $agenda->total_target > 0)
                        {{ round(($agenda->total_hadir / $agenda->total_target) * 100) }}%
                    @else
                        -
                    @endif
                </td>
                <td class="text-sm">{{ Str::limit($agenda->hasil_kegiatan, 60) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Tindak Lanjut yang Diperlukan --}}
    @php
        $perluTindakLanjut = $agendas->filter(function($agenda) {
            return $agenda->status === 'selesai' && !empty($agenda->rencana_tindak_lanjut);
        });
    @endphp
    
    @if($perluTindakLanjut->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">📋 Rencana Tindak Lanjut</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 30%">Nama Kegiatan</th>
                <th style="width: 15%">Tanggal Selesai</th>
                <th style="width: 50%">Rencana Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($perluTindakLanjut->take(15) as $index => $agenda)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $agenda->nama_kegiatan }}</td>
                <td class="text-center">{{ $agenda->tanggal_waktu->format('d/m/Y') }}</td>
                <td class="text-sm">{{ $agenda->rencana_tindak_lanjut }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Performance Metrics --}}
    @if(isset($statistics))
    <div class="page-break"></div>
    <h3 style="color: #1e40af; margin-bottom: 15px;">📊 Metrik Kinerja Kegiatan</h3>
    
    <div class="info-box">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
            <div>
                <div class="info-row">
                    <span class="info-label">Total Kegiatan:</span>
                    <span>{{ $statistics['total_kegiatan'] ?? 0 }} kegiatan</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tingkat Penyelesaian:</span>
                    <span>
                        @php
                            $totalKegiatan = $statistics['total_kegiatan'] ?? 0;
                            $kegiatanSelesai = $statistics['kegiatan_selesai'] ?? 0;
                            // Fix division by zero error
                            $persentaseSelesai = $totalKegiatan > 0 ? round(($kegiatanSelesai / $totalKegiatan) * 100, 1) : 0;
                        @endphp
                        {{ $persentaseSelesai }}% ({{ $kegiatanSelesai }}/{{ $totalKegiatan }})
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rata-rata Peserta per Kegiatan:</span>
                    <span>
                        @php
                            $totalKegiatan = $statistics['total_kegiatan'] ?? 0;
                            $totalTarget = $statistics['total_target'] ?? 0;
                            // Fix division by zero error
                            $avgPeserta = $totalKegiatan > 0 ? round($totalTarget / $totalKegiatan, 1) : 0;
                        @endphp
                        {{ $avgPeserta }} orang
                    </span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">Total Target Peserta:</span>
                    <span>{{ $statistics['total_target'] ?? 0 }} orang</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Kehadiran:</span>
                    <span>{{ $statistics['total_hadir'] ?? 0 }} orang</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Persentase Kehadiran:</span>
                    <span class="font-bold">{{ $statistics['persentase_kehadiran'] ?? 0 }}%</span>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
@endsection