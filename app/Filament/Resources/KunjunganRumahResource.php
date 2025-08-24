<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KunjunganRumahResource\Pages;
use App\Models\KunjunganRumah;
use App\Models\Keluarga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class KunjunganRumahResource extends Resource
{
    protected static ?string $model = KunjunganRumah::class;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Kunjungan Rumah';
    protected static ?string $navigationGroup = 'Kunjungan Rumah';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Kunjungan Terintegrasi')
                    ->description('Pilih keluarga dan lakukan kunjungan dengan checklist lengkap')
                    ->schema([
                        Tabs::make('Kunjungan Rumah')
                            ->tabs([
                                self::getPilihKeluargaTab(),
                                self::getChecklistSanitasiTab(),
                                self::getCatatanDokumentasiTab(),
                                self::getFollowUpTab(),
                            ])
                            ->columnSpanFull()
                            ->persistTabInQueryString(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

  
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->actions(self::getTableActions())
            ->bulkActions(self::getTableBulkActions())
            ->headerActions([
                Tables\Actions\Action::make('download_laporan')
                    ->label('Download Laporan PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('prioritas')
                            ->label('Filter Prioritas')
                            ->options([
                                'tinggi' => 'Tinggi',
                                'sedang' => 'Sedang',
                                'rendah' => 'Rendah',
                            ])
                            ->placeholder('Semua Prioritas'),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Pilih tanggal mulai periode laporan'),
                                
                                Forms\Components\DateTimePicker::make('tanggal_akhir')
                                    ->label('Tanggal Akhir')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Pilih tanggal akhir periode laporan'),
                            ]),
                        
                        Forms\Components\Select::make('rt')
                            ->label('Filter RT')
                            ->options(fn () => self::getRtOptions())
                            ->placeholder('Semua RT'),
                    ])
                    ->action(function (array $data) {
                        // Convert dates to proper format if provided
                        if (!empty($data['tanggal_mulai'])) {
                            $data['tanggal_mulai'] = \Carbon\Carbon::parse($data['tanggal_mulai'])->format('Y-m-d');
                        }
                        if (!empty($data['tanggal_akhir'])) {
                            $data['tanggal_akhir'] = \Carbon\Carbon::parse($data['tanggal_akhir'])->format('Y-m-d');
                        }
                        
                        $params = http_build_query(array_filter($data));
                        return redirect("/reports/kunjungan?{$params}");
                    })
                    ->modalHeading('Download Laporan Kunjungan Rumah')
                    ->modalDescription('Pilih periode dan filter untuk laporan yang akan didownload')
                    ->modalSubmitActionLabel('Download PDF')
                    ->modalWidth(MaxWidth::Medium),
                
                Tables\Actions\Action::make('download_rekapitulasi')
                    ->label('Download Rekapitulasi')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->action(function () {
                        return redirect('/reports/rekapitulasi');
                    })
                    ->tooltip('Download laporan rekapitulasi lengkap'),
            ])
            ->defaultSort('tanggal_kunjungan', 'desc');
    }

    // All existing private methods remain the same...
    private static function getPilihKeluargaTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Pilih Keluarga')
            ->icon('heroicon-m-home')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Forms\Components\DatePicker::make('tanggal_kunjungan')
                            ->label('Tanggal Kunjungan')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->native(false),
                    ]),

                Forms\Components\Select::make('keluarga_id')
                    ->label('Pilih Keluarga untuk Dikunjungi')
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->options(fn () => self::getKeluargaOptions())
                    ->helperText('🔴 Prioritas Tinggi | 🟡 Ada Balita | 🟢 Normal'),

                Forms\Components\Placeholder::make('info_keluarga')
                    ->label('Informasi Keluarga')
                    ->content(fn (Forms\Get $get) => self::getKeluargaInfo($get))
                    ->visible(fn (Forms\Get $get) => !empty($get('keluarga_id'))),

                Forms\Components\CheckboxList::make('tujuan_kunjungan')
                    ->label('Tujuan Kunjungan')
                    ->required()
                    ->options(self::getTujuanKunjunganOptions())
                    ->columns(2),
            ]);
    }

    private static function getChecklistSanitasiTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Checklist Sanitasi')
            ->icon('heroicon-m-shield-check')
            ->schema([
                Forms\Components\Section::make('Kondisi Sanitasi Rumah')
                    ->description('Lakukan pengecekan kondisi sanitasi rumah')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('air_bersih')
                                    ->label('🚰 Air Bersih')
                                    ->required()
                                    ->options(self::getAirBersihOptions())
                                    ->native(false),

                                Forms\Components\Select::make('jamban')
                                    ->label('🚽 Jamban')
                                    ->required()
                                    ->options(self::getJambanOptions())
                                    ->native(false),

                                Forms\Components\Select::make('sampah')
                                    ->label('🗑️ Pengelolaan Sampah')
                                    ->required()
                                    ->options(self::getSampahOptions())
                                    ->native(false),

                                Forms\Components\Select::make('ventilasi')
                                    ->label('🌬️ Ventilasi')
                                    ->required()
                                    ->options(self::getVentilasiOptions())
                                    ->native(false),
                            ]),

                     
                    ]),
            ]);
    }

 

    private static function getCatatanDokumentasiTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Catatan & Dokumentasi')
            ->icon('heroicon-m-camera')
            ->schema([
                Forms\Components\Textarea::make('catatan_kondisi_keluarga')
                    ->label('Catatan Kondisi Keluarga')
                    ->rows(4)
                    ->placeholder('Catatan umum kondisi keluarga saat kunjungan')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('rekomendasi')
                    ->label('Rekomendasi')
                    ->rows(4)
                    ->placeholder('Rekomendasi tindak lanjut untuk keluarga')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('foto_dokumentasi')
                    ->label('Foto Dokumentasi')
                    ->multiple()
                    ->image()
                    ->maxFiles(5)
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->directory('kunjungan-dokumentasi')
                    ->helperText('Upload maksimal 5 foto dokumentasi kunjungan')
                    ->columnSpanFull(),
            ]);
    }

    private static function getFollowUpTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Follow Up')
            ->icon('heroicon-m-arrow-path')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('perlu_kunjungan_ulang')
                            ->label('Perlu Kunjungan Ulang')
                            ->reactive()
                            ->inline(false),

                        Forms\Components\Select::make('prioritas')
                            ->label('Prioritas Kunjungan Berikutnya')
                            ->options(self::getPrioritasOptions())
                            ->default('sedang')
                            ->native(false),
                    ]),

                Forms\Components\DatePicker::make('tanggal_kunjungan_ulang')
                    ->label('Tanggal Kunjungan Ulang')
                    ->visible(fn (Forms\Get $get) => $get('perlu_kunjungan_ulang'))
                    ->minDate(now())
                    ->native(false)
                    ->helperText('Tentukan kapan kunjungan ulang akan dilakukan'),

                Forms\Components\Textarea::make('catatan_follow_up')
                    ->label('Catatan Follow Up')
                    ->visible(fn (Forms\Get $get) => $get('perlu_kunjungan_ulang'))
                    ->rows(3)
                    ->placeholder('Catatan khusus untuk kunjungan ulang')
                    ->columnSpanFull(),
            ]);
    }

    private static function getKeluargaOptions(): array
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) return [];
        
        return Keluarga::where('user_id', $currentUserId)
            ->get()
            ->mapWithKeys(function ($keluarga) {
                $prioritas = '🟢 '; // Default normal
                
                if ($keluarga->jumlah_balita > 0) {
                    $prioritas = '🟡 ';
                }
                
                $info = "{$keluarga->nama_kepala_keluarga} - RT {$keluarga->rt} / RW {$keluarga->rw}";
                $detail = " ({$keluarga->jumlah_anggota} anggota";
                
                if ($keluarga->jumlah_balita > 0) {
                    $detail .= ", {$keluarga->jumlah_balita} balita";
                }
                
                if ($keluarga->jumlah_ibu_hamil > 0) {
                    $detail .= ", {$keluarga->jumlah_ibu_hamil} ibu hamil";
                }
                
                $detail .= ")";
                
                return [$keluarga->getKey() => $prioritas . $info . $detail];
            })
            ->toArray();
    }

    private static function getKeluargaInfo(Forms\Get $get): string
    {
        $keluargaId = $get('keluarga_id');
        if (!$keluargaId) {
            return 'Pilih keluarga untuk melihat informasi detail';
        }
        
        $keluarga = Keluarga::find($keluargaId);
        if (!$keluarga) return 'Keluarga tidak ditemukan';
        
        $info = "📍 {$keluarga->alamat_lengkap}\n";
        $info .= "👥 {$keluarga->jumlah_anggota} anggota keluarga";
        
        if ($keluarga->jumlah_balita > 0) {
            $info .= " (termasuk {$keluarga->jumlah_balita} balita)";
        }
        
        return $info;
    }

    private static function getTujuanKunjunganOptions(): array
    {
        return [
            'cek_balita' => 'Cek Tumbuh Kembang Balita',
            'sosialisasi' => 'Sosialisasi Kesehatan',
            'pendataan' => 'Pendataan Ulang',
            'pemantauan' => 'Pemantauan Kondisi Keluarga',
            'edukasi' => 'Edukasi Kesehatan',
        ];
    }

    private static function getAirBersihOptions(): array
    {
        return [
            'baik' => 'Baik - Air jernih, tidak berbau',
            'kurang' => 'Kurang - Air keruh atau berbau',
            'buruk' => 'Buruk - Air tidak layak konsumsi',
        ];
    }

    private static function getJambanOptions(): array
    {
        return [
            'sehat' => 'Sehat - Jamban bersih dan tertutup',
            'tidak_sehat' => 'Tidak Sehat - Jamban kotor/terbuka',
        ];
    }

    private static function getSampahOptions(): array
    {
        return [
            'baik' => 'Baik - Sampah dikelola dengan baik',
            'kurang' => 'Kurang - Sampah berserakan',
            'buruk' => 'Buruk - Tidak ada pengelolaan sampah',
        ];
    }

    private static function getVentilasiOptions(): array
    {
        return [
            'baik' => 'Baik - Ventilasi cukup',
            'kurang' => 'Kurang - Ventilasi terbatas',
            'buruk' => 'Buruk - Tidak ada ventilasi',
        ];
    }

    private static function calculateSanitasiScore(Forms\Get $get): string
    {
        $score = 0;
        if ($get('air_bersih') === 'baik') $score++;
        if ($get('jamban') === 'sehat') $score++;
        if ($get('sampah') === 'baik') $score++;
        if ($get('ventilasi') === 'baik') $score++;
        
        $persentase = ($score / 4) * 100;
        $kondisi = match(true) {
            $score === 4 => '🌟 Sangat Baik',
            $score === 3 => '✅ Baik',
            $score === 2 => '⚠️ Cukup',
            $score === 1 => '❌ Kurang',
            default => '🚨 Buruk'
        };
        
        return "Skor: {$score}/4 ({$persentase}%) - {$kondisi}";
    }

    private static function getUpdateAnggotaSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Anggota')
                        ->placeholder('Nama anggota keluarga'),

                    Forms\Components\Select::make('jenis_update')
                        ->label('Jenis Update')
                        ->options([
                            'gizi' => 'Update Data Gizi (Balita)',
                            'kondisi' => 'Update Kondisi Kesehatan',
                            'imunisasi' => 'Update Status Imunisasi',
                        ])
                        ->reactive()
                        ->native(false),

                    Forms\Components\Select::make('kondisi_kesehatan')
                        ->label('Kondisi Kesehatan')
                        ->options([
                            'baik' => 'Baik',
                            'sakit_ringan' => 'Sakit Ringan',
                            'sakit_berat' => 'Sakit Berat',
                            'perlu_rujukan' => 'Perlu Rujukan',
                        ])
                        ->native(false),
                ]),

            Forms\Components\Section::make('Data Gizi Balita')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('berat_badan_baru')
                                ->label('Berat Badan Baru')
                                ->numeric()
                                ->step(0.1)
                                ->minValue(0)
                                ->suffix('kg'),

                            Forms\Components\TextInput::make('tinggi_badan_baru')
                                ->label('Tinggi Badan Baru')
                                ->numeric()
                                ->step(0.1)
                                ->minValue(0)
                                ->suffix('cm'),
                        ]),
                ])
                ->visible(fn (Forms\Get $get) => $get('jenis_update') === 'gizi')
                ->collapsed()
                ->collapsible(),

            Forms\Components\Textarea::make('catatan')
                ->label('Catatan')
                ->rows(2)
                ->placeholder('Catatan kondisi atau hasil pemeriksaan'),
        ];
    }

    private static function getPrioritasOptions(): array
    {
        return [
            'rendah' => 'Rendah - Kondisi baik',
            'sedang' => 'Sedang - Perlu pemantauan',
            'tinggi' => 'Tinggi - Perlu perhatian khusus',
        ];
    }

    private static function getUpdateAnggotaLabel(array $state): ?string
    {
        return ($state['nama'] ?? 'Anggota') . 
            ($state['jenis_update'] ? " - {$state['jenis_update']}" : '');
    }

    private static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('keluarga.nama_kepala_keluarga')
                ->label('Keluarga')
                ->searchable()
                ->sortable()
                ->weight('bold'),

            Tables\Columns\TextColumn::make('keluarga.rt_rw')
                ->label('RT/RW')
                ->badge()
                ->searchable(),

            Tables\Columns\TextColumn::make('tanggal_kunjungan')
                ->label('Tanggal Kunjungan')
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('hari_sejak_kunjungan')
                ->label('Hari Lalu')
                ->suffix(' hari')
                ->alignCenter()
                ->color(fn (int $state): string => match (true) {
                    $state <= 7 => 'success',
                    $state <= 30 => 'warning',
                    default => 'danger',
                }),

            Tables\Columns\TextColumn::make('tujuan_kunjungan_text')
                ->label('Tujuan')
                ->limit(30)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    return $column->getState();
                }),

            Tables\Columns\TextColumn::make('sanitasi_score')
                ->label('Sanitasi')
                ->alignCenter()
                ->formatStateUsing(fn (int $state): string => "{$state}/4")
                ->badge()
                ->color(fn (int $state): string => match (true) {
                    $state === 4 => 'success',
                    $state === 3 => 'info',
                    $state === 2 => 'warning',
                    default => 'danger',
                }),

            Tables\Columns\TextColumn::make('kondisi_sanitasi')
                ->label('Kondisi')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Sangat Baik' => 'success',
                    'Baik' => 'info',
                    'Cukup' => 'warning',
                    'Kurang' => 'danger',
                    'Buruk' => 'danger',
                }),

            Tables\Columns\TextColumn::make('prioritas')
                ->label('Prioritas')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'tinggi' => 'danger',
                    'sedang' => 'warning',
                    'rendah' => 'success',
                }),

            Tables\Columns\IconColumn::make('perlu_kunjungan_ulang')
                ->label('Follow Up')
                ->boolean()
                ->alignCenter(),

            Tables\Columns\TextColumn::make('tanggal_kunjungan_ulang')
                ->label('Kunjungan Ulang')
                ->date()
                ->placeholder('Tidak dijadwalkan')
                ->color(fn ($state) => $state && $state <= now() ? 'danger' : 'success'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('keluarga.rt')
                ->label('RT')
                ->relationship('keluarga', 'rt')
                ->options(fn () => self::getRtOptions()),

            Tables\Filters\SelectFilter::make('prioritas')
                ->options([
                    'tinggi' => 'Tinggi',
                    'sedang' => 'Sedang',
                    'rendah' => 'Rendah',
                ]),

            Tables\Filters\Filter::make('bulan_ini')
                ->label('Bulan Ini')
                ->query(fn (Builder $query): Builder => $query->bulanIni()),

            Tables\Filters\Filter::make('tahun_ini')
                ->label('Tahun Ini')
                ->query(fn (Builder $query): Builder => $query->tahunIni()),
        ];
    }

    private static function getRtOptions(): array
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) return [];
        
        return Keluarga::where('user_id', $currentUserId)
            ->distinct()
            ->pluck('rt', 'rt')
            ->mapWithKeys(fn ($rt) => [$rt => "RT $rt"])
            ->toArray();
    }

    private static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            
            Tables\Actions\Action::make('kunjungi_ulang')
                ->label('Kunjungi Ulang')
                ->icon('heroicon-m-arrow-path')
                ->color('info')
                ->visible(fn (KunjunganRumah $record) => $record->perlu_kunjungan_ulang)
                ->url(fn (KunjunganRumah $record): string => 
                    '/admin/kunjungan-rumahs/create?keluarga_id=' . $record->keluarga_id
                ),
        ];
    }

    private static function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKunjunganRumahs::route('/'),
            'create' => Pages\CreateKunjunganRumah::route('/create'),
            'edit' => Pages\EditKunjunganRumah::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $currentUserId = Auth::id();
        
        return parent::getEloquentQuery()
            ->when($currentUserId, fn (Builder $query) => $query->where('user_id', $currentUserId))
            ->with(['keluarga']);
    }
}