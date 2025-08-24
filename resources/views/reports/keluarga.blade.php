{{-- resources/views/reports/keluarga.blade.php --}}
@extends('reports.layout')

@section('content')
    @if(isset($statistics))
    <div class="statistics">
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_keluarga'] }}</div>
            <div class="stat-label">Total Keluarga</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_anggota'] }}</div>
            <div class="stat-label">Total Anggota</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_balita'] }}</div>
            <div class="stat-label">Total Balita</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $statistics['total_ibu_hamil'] }}</div>
            <div class="stat-label">Ibu Hamil</div>
        </div>
    </div>
    @endif

    @if(isset($statistics['by_status_ekonomi']) && count($statistics['by_status_ekonomi']) > 0)
    <div class="info-box mb-4">
        <h3 style="margin: 0 0 10px 0; color: #1e40af;">Distribusi Status Ekonomi</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
            @foreach($statistics['by_status_ekonomi'] as $status => $count)
            <div style="text-align: center; padding: 5px;">
                <span class="badge badge-{{ $status === 'mampu' ? 'success' : ($status === 'kurang_mampu' ? 'warning' : 'danger') }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Nama Kepala Keluarga</th>
                <th style="width: 8%">RT/RW</th>
                <th style="width: 25%">Alamat</th>
                <th style="width: 7%">Anggota</th>
                <th style="width: 7%">Balita</th>
                <th style="width: 7%">Ibu Hamil</th>
                <th style="width: 12%">Status Ekonomi</th>
                <th style="width: 9%">Telepon</th>
            </tr>
        </thead>
        <tbody>
            @forelse($keluargas as $index => $keluarga)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $keluarga->nama_kepala_keluarga }}</td>
                <td class="text-center">RT {{ $keluarga->rt }}/RW {{ $keluarga->rw }}</td>
                <td class="text-sm">{{ Str::limit($keluarga->alamat_lengkap, 40) }}</td>
                <td class="text-center">{{ $keluarga->jumlah_anggota }}</td>
                <td class="text-center">
                    @if($keluarga->jumlah_balita > 0)
                        <span class="badge badge-info">{{ $keluarga->jumlah_balita }}</span>
                    @else
                        <span class="text-sm" style="color: #64748b;">-</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($keluarga->jumlah_ibu_hamil > 0)
                        <span class="badge badge-warning">{{ $keluarga->jumlah_ibu_hamil }}</span>
                    @else
                        <span class="text-sm" style="color: #64748b;">-</span>
                    @endif
                </td>
                <td>
                    @php
                        $badgeClass = match($keluarga->status_ekonomi) {
                            'mampu' => 'badge-success',
                            'kurang_mampu' => 'badge-warning',
                            'tidak_mampu' => 'badge-danger',
                            default => 'badge-gray'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $keluarga->status_ekonomi)) }}</span>
                </td>
                <td class="text-sm">{{ $keluarga->telepon ?: '-' }}</td>
            </tr>
            
            {{-- Detail Anggota Keluarga jika ada --}}
            @if(is_array($keluarga->anggota_keluarga) && count($keluarga->anggota_keluarga) > 0)
            <tr style="background: #f8fafc;">
                <td></td>
                <td colspan="8" style="padding: 8px;">
                    <div style="font-size: 10px; color: #475569;">
                        <strong>Anggota Keluarga:</strong>
                        @foreach($keluarga->anggota_keluarga as $anggota)
                            <span style="margin-right: 15px;">
                                {{ $anggota['nama'] ?? 'N/A' }} ({{ $anggota['umur'] ?? 0 }}th, {{ $anggota['status'] ?? 'N/A' }})
                                @if(isset($anggota['is_ibu_hamil']) && $anggota['is_ibu_hamil'])
                                    <span class="badge badge-warning" style="font-size: 8px;">Hamil</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </td>
            </tr>
            @endif
            
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                    Tidak ada data keluarga ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary per RT/RW jika ada data --}}
    @if($keluargas->count() > 0)
    <div class="page-break"></div>
    
    <h3 style="color: #1e40af; margin-bottom: 15px;">📊 Ringkasan Data per RT/RW</h3>
    
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 15%">RT</th>
                <th style="width: 15%">RW</th>
                <th style="width: 20%">Jumlah Keluarga</th>
                <th style="width: 20%">Total Anggota</th>
                <th style="width: 15%">Total Balita</th>
                <th style="width: 15%">Ibu Hamil</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedData = $keluargas->groupBy(function($item) {
                    return $item->rt . '-' . $item->rw;
                });
            @endphp
            
            @foreach($groupedData as $rtRw => $group)
                @php
                    [$rt, $rw] = explode('-', $rtRw);
                    $totalKeluarga = $group->count();
                    $totalAnggota = $group->sum('jumlah_anggota');
                    $totalBalita = $group->sum('jumlah_balita');
                    $totalIbuHamil = $group->sum('jumlah_ibu_hamil');
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $rt }}</td>
                    <td class="text-center font-bold">{{ $rw }}</td>
                    <td class="text-center">{{ $totalKeluarga }}</td>
                    <td class="text-center">{{ $totalAnggota }}</td>
                    <td class="text-center">
                        @if($totalBalita > 0)
                            <span class="badge badge-info">{{ $totalBalita }}</span>
                        @else
                            <span style="color: #64748b;">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($totalIbuHamil > 0)
                            <span class="badge badge-warning">{{ $totalIbuHamil }}</span>
                        @else
                            <span style="color: #64748b;">0</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            
            {{-- Total Row --}}
            <tr style="background: #e5e7eb; font-weight: bold;">
                <td colspan="2" class="text-center">TOTAL</td>
                <td class="text-center">{{ $keluargas->count() }}</td>
                <td class="text-center">{{ $keluargas->sum('jumlah_anggota') }}</td>
                <td class="text-center">{{ $keluargas->sum('jumlah_balita') }}</td>
                <td class="text-center">{{ $keluargas->sum('jumlah_ibu_hamil') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Keluarga dengan Balita --}}
    @php
        $keluargaBalita = $keluargas->filter(function($k) { return $k->jumlah_balita > 0; });
    @endphp
    
    @if($keluargaBalita->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">👶 Keluarga dengan Balita</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 35%">Nama Kepala Keluarga</th>
                <th style="width: 15%">RT/RW</th>
                <th style="width: 15%">Jumlah Balita</th>
                <th style="width: 30%">Detail Balita</th>
            </tr>
        </thead>
        <tbody>
            @foreach($keluargaBalita as $index => $keluarga)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $keluarga->nama_kepala_keluarga }}</td>
                <td class="text-center">RT {{ $keluarga->rt }}/RW {{ $keluarga->rw }}</td>
                <td class="text-center">
                    <span class="badge badge-info">{{ $keluarga->jumlah_balita }}</span>
                </td>
                <td class="text-sm">
                    @if(is_array($keluarga->anggota_keluarga))
                        @foreach($keluarga->anggota_keluarga as $anggota)
                            @if(isset($anggota['status']) && $anggota['status'] === 'balita')
                                <span style="margin-right: 10px;">
                                    {{ $anggota['nama'] ?? 'N/A' }} ({{ $anggota['umur'] ?? 0 }}th)
                                </span>
                            @endif
                        @endforeach
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Keluarga dengan Ibu Hamil --}}
    @php
        $keluargaIbuHamil = $keluargas->filter(function($k) { return $k->jumlah_ibu_hamil > 0; });
    @endphp
    
    @if($keluargaIbuHamil->count() > 0)
    <h3 style="color: #1e40af; margin-bottom: 15px;">🤱 Keluarga dengan Ibu Hamil</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 35%">Nama Kepala Keluarga</th>
                <th style="width: 15%">RT/RW</th>
                <th style="width: 15%">Jumlah Ibu Hamil</th>
                <th style="width: 30%">Detail Ibu Hamil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($keluargaIbuHamil as $index => $keluarga)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $keluarga->nama_kepala_keluarga }}</td>
                <td class="text-center">RT {{ $keluarga->rt }}/RW {{ $keluarga->rw }}</td>
                <td class="text-center">
                    <span class="badge badge-warning">{{ $keluarga->jumlah_ibu_hamil }}</span>
                </td>
                <td class="text-sm">
                    @if(is_array($keluarga->anggota_keluarga))
                        @foreach($keluarga->anggota_keluarga as $anggota)
                            @if(isset($anggota['is_ibu_hamil']) && $anggota['is_ibu_hamil'])
                                <span style="margin-right: 10px;">
                                    {{ $anggota['nama'] ?? 'N/A' }}
                                    @if(isset($anggota['usia_kehamilan']))
                                        ({{ $anggota['usia_kehamilan'] }} bulan)
                                    @endif
                                </span>
                            @endif
                        @endforeach
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endif
@endsection