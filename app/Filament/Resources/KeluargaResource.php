<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeluargaResource\Pages;
use App\Models\Keluarga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class KeluargaResource extends Resource
{
    protected static ?string $model = Keluarga::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Data Keluarga';
    protected static ?string $navigationGroup = 'Dasa Wisma';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'nama_kepala_keluarga';

    // ... existing form method stays the same ...
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Main form content
                Section::make('Data Keluarga')
                    ->description('Kelola data keluarga dengan sistem tabs untuk kemudahan navigasi')
                    ->icon('heroicon-m-home')
                    ->schema([
                        Tabs::make('Data Keluarga')
                            ->tabs([
                                self::getDataKepalaKeluargaTab(),
                                self::getAnggotaKeluargaTab(),
                                self::getDataGiziBalitaTab(),
                            ])
                            ->columnSpanFull()
                            ->persistTabInQueryString(),
                    ])
                    ->columnSpanFull(),
     
            ]);
    }

    // ... all existing private methods stay the same ...

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters(self::getTableFilters(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->actions(self::getTableActions(), position: ActionsPosition::BeforeColumns)
            ->bulkActions(self::getTableBulkActions())
            ->headerActions([
                Tables\Actions\Action::make('download_laporan')
                    ->label('Download Laporan PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('rt')
                            ->label('Filter RT')
                            ->options(fn () => self::getRtOptions())
                            ->placeholder('Semua RT'),
                        
                        Forms\Components\Select::make('rw')
                            ->label('Filter RW')
                            ->options(fn () => self::getRwOptions())
                            ->placeholder('Semua RW'),
                        
                        Forms\Components\Select::make('status_ekonomi')
                            ->label('Filter Status Ekonomi')
                            ->options(self::getStatusEkonomiOptions())
                            ->placeholder('Semua Status'),
                        
                        Forms\Components\Toggle::make('has_balita')
                            ->label('Hanya yang Memiliki Balita'),
                        
                        Forms\Components\Toggle::make('has_ibu_hamil')
                            ->label('Hanya yang Memiliki Ibu Hamil'),
                    ])
                    ->action(function (array $data) {
                        $params = http_build_query(array_filter($data));
                        return redirect("/reports/keluarga?{$params}");
                    })
                    ->modalHeading('Download Laporan Data Keluarga')
                    ->modalSubmitActionLabel('Download PDF')
                    ->modalWidth(MaxWidth::Medium),
            ])
            ->emptyStateHeading('Belum ada data keluarga')
            ->emptyStateDescription('Mulai dengan menambahkan data keluarga pertama.')
            ->emptyStateIcon('heroicon-o-home')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Keluarga')
                    ->icon('heroicon-m-plus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->poll('30s')
            ->deferLoading();
    }

    
    private static function getDataKepalaKeluargaTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Data Kepala Keluarga')
            ->icon('heroicon-m-user')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        
                        Forms\Components\TextInput::make('nama_kepala_keluarga')
                            ->label('Nama Kepala Keluarga')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama kepala keluarga')
                            ->prefixIcon('heroicon-m-user')
                            ->live(onBlur: true),
                        
                        self::getRtRwSection(),
                        
                        Forms\Components\Select::make('status_ekonomi')
                            ->label('Status Ekonomi')
                            ->required()
                            ->options(self::getStatusEkonomiOptions())
                            ->native(false)
                            ->prefixIcon('heroicon-m-banknotes'),
                        
                        Forms\Components\TextInput::make('telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('081234567890')
                            ->prefixIcon('heroicon-m-phone'),
                    ]),
                
                Forms\Components\Textarea::make('alamat_lengkap')
                    ->label('Asal Domisili Asli')
                    ->required()
                    ->rows(3)
                    ->placeholder('Masukkan asal domisili asli keluarga secara lengkap')
                    ->columnSpanFull(),
            ]);
    }

    private static function getRtRwSection(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TextInput::make('rt')
                    ->label('RT')
                    ->required()
                    ->maxLength(3)
                    ->numeric()
                    ->placeholder('001')
                    ->prefixIcon('heroicon-m-map-pin')
                    ->minValue(1)
                    ->maxValue(999),
                
                Forms\Components\TextInput::make('rw')
                    ->label('RW')
                    ->required()
                    ->maxLength(3)
                    ->numeric()
                    ->placeholder('001')
                    ->prefixIcon('heroicon-m-map-pin')
                    ->minValue(1)
                    ->maxValue(999),
            ])
            ->columnSpan(1);
    }

    private static function getAnggotaKeluargaTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Anggota Keluarga')
            ->icon('heroicon-m-users')
            ->badge(fn (Forms\Get $get) => count($get('anggota_keluarga') ?? []))
            ->schema([
                Forms\Components\Placeholder::make('info_anggota')
                    ->label('')
                    ->content(self::getAnggotaInfoContent()),
                
                Repeater::make('anggota_keluarga')
                    ->label('Daftar Anggota Keluarga')
                    ->schema(self::getAnggotaKeluargaSchema())
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => self::getAnggotaItemLabel($state))
                    ->addActionLabel('Tambah Anggota Keluarga')
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->minItems(1)
                    ->defaultItems(1)
                    ->live(),
            ]);
    }

    private static function getDataGiziBalitaTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Data Gizi Balita')
            ->icon('heroicon-m-chart-bar')
            ->badge(fn (Forms\Get $get) => count($get('data_gizi_balita') ?? []))
            ->schema([
                Forms\Components\Placeholder::make('info_gizi')
                    ->label('')
                    ->content(self::getGiziInfoContent()),
                
                Repeater::make('data_gizi_balita')
                    ->label('Data Gizi & Imunisasi Balita')
                    ->schema(self::getDataGiziSchema())
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => self::getGiziItemLabel($state))
                    ->addActionLabel('Tambah Data Gizi & Imunisasi')
                    ->reorderableWithButtons()
                    ->cloneable(),
            ]);
    }

    private static function getStatusEkonomiOptions(): array
    {
        return [
            'mampu' => 'Mampu',
            'kurang_mampu' => 'Kurang Mampu',
            'tidak_mampu' => 'Tidak Mampu',
        ];
    }

    private static function getAnggotaInfoContent(): HtmlString
    {
        return new HtmlString('
            <div class="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-blue-800 dark:text-blue-200 font-medium">Tips:</span>
                </div>
                <p class="text-blue-700 dark:text-blue-300 text-sm mt-2">
                    Status usia akan otomatis ditentukan berdasarkan umur. Untuk ibu hamil, aktifkan toggle kehamilan.
                </p>
            </div>
        ');
    }

    private static function getAnggotaKeluargaSchema(): array
    {
        return [
            Forms\Components\Grid::make(4)
                ->schema([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Lengkap')
                        ->required()
                        ->placeholder('Nama anggota keluarga')
                        ->prefixIcon('heroicon-m-user'),
                    
                    Forms\Components\TextInput::make('umur')
                        ->label('Umur')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(120)
                        ->suffix('tahun')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state !== null) {
                                $set('status', self::calculateAgeStatus((int) $state));
                            }
                        }),
                    
                    Forms\Components\Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->required()
                        ->options([
                            'laki-laki' => 'Laki-laki',
                            'perempuan' => 'Perempuan',
                        ])
                        ->native(false)
                        ->live(),

                    Forms\Components\TextInput::make('status')
                        ->label('Status Usia')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Otomatis berdasarkan umur')
                        ->prefixIcon('heroicon-m-calendar'),
                ]),
            
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('pekerjaan')
                        ->label('Pekerjaan')
                        ->placeholder('Contoh: Petani, Guru, Ibu Rumah Tangga, Pelajar')
                        ->prefixIcon('heroicon-m-briefcase'),
                    
                    Forms\Components\Select::make('status_peran')
                        ->label('Status Peran dalam Keluarga')
                        ->required()
                        ->options(self::getStatusPeranOptions())
                        ->native(false)
                        ->searchable(),
                ]),
            
            self::getKehamilanSection(),
        ];
    }

    private static function getKehamilanSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Data Kehamilan')
            ->schema([
                Forms\Components\Toggle::make('is_ibu_hamil')
                    ->label('Sedang Hamil')
                    ->live(),
                
                Forms\Components\TextInput::make('usia_kehamilan')
                    ->label('Usia Kehamilan')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(9)
                    ->suffix('bulan')
                    ->visible(fn (Forms\Get $get) => $get('is_ibu_hamil'))
                    ->required(fn (Forms\Get $get) => $get('is_ibu_hamil')),
            ])
            ->visible(fn (Forms\Get $get) => $get('jenis_kelamin') === 'perempuan' && $get('status') === 'dewasa')
            ->collapsed()
            ->collapsible();
    }

    private static function getGiziInfoContent(): HtmlString
    {
        return new HtmlString('
            <div class="bg-green-50 dark:bg-green-950 p-4 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-800 dark:text-green-200 font-medium">Status Gizi Balita</span>
                </div>
                <p class="text-green-700 dark:text-green-300 text-sm mt-2">
                    Data gizi dan imunisasi untuk balita (anak usia 0-5 tahun). Status gizi akan otomatis dihitung berdasarkan BB dan TB.
                </p>
            </div>
        ');
    }

    private static function getDataGiziSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('nama_balita')
                        ->label('Nama Balita')
                        ->options(function (Forms\Get $get) {
                            return self::getBalitaOptions($get);
                        })
                        ->searchable()
                        ->placeholder('Pilih balita dari anggota keluarga')
                        ->helperText('Jika tidak ada pilihan, tambahkan anggota dengan umur 0-5 tahun di tab sebelumnya')
                        ->prefixIcon('heroicon-m-user'),
                    
                    Forms\Components\DatePicker::make('tanggal_pengukuran')
                        ->label('Tanggal Pengukuran')
                        ->default(now())
                        ->native(false)
                        ->prefixIcon('heroicon-m-calendar'),
                ]),
            
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('berat_badan')
                        ->label('Berat Badan')
                        ->numeric()
                        ->step(0.1)
                        ->minValue(0)
                        ->maxValue(50)
                        ->suffix('kg')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                            self::calculateStatusGizi($state, $get('tinggi_badan'), $set);
                        }),
                    
                    Forms\Components\TextInput::make('tinggi_badan')
                        ->label('Tinggi Badan')
                        ->numeric()
                        ->step(0.1)
                        ->minValue(0)
                        ->maxValue(150)
                        ->suffix('cm')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                            self::calculateStatusGizi($get('berat_badan'), $state, $set);
                        }),
                    
                    Forms\Components\TextInput::make('status_gizi')
                        ->label('Status Gizi')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Otomatis dihitung')
                        ->helperText('Berdasarkan perhitungan BB/TB'),
                ]),
            
            Forms\Components\CheckboxList::make('status_imunisasi')
                ->label('Status Imunisasi')
                ->options(self::getImunisasiOptions())
                ->columns(2)
                ->helperText('Centang imunisasi yang sudah diterima'),
            
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Textarea::make('catatan_imunisasi')
                        ->label('Catatan Imunisasi')
                        ->rows(2)
                        ->placeholder('Catatan tambahan tentang imunisasi'),
                    
                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan Gizi')
                        ->rows(2)
                        ->placeholder('Catatan tambahan hasil pengukuran'),
                ]),
        ];
    }

    private static function getStatusPeranOptions(): array
    {
        return [
            'ayah' => 'Ayah/Kepala Keluarga',
            'ibu' => 'Ibu/Istri',
            'anak_kandung' => 'Anak Kandung',
            'anak_tiri' => 'Anak Tiri',
            'anak_angkat' => 'Anak Angkat',
            'cucu' => 'Cucu',
            'orangtua_kandung' => 'Orangtua Kandung',
            'orangtua_tiri' => 'Orangtua Tiri',
            'orangtua_angkat' => 'Orangtua Angkat',
            'saudara_kandung' => 'Saudara Kandung',
            'saudara_tiri' => 'Saudara Tiri',
            'keponakan' => 'Keponakan',
            'lainnya' => 'Lainnya',
        ];
    }

    private static function getImunisasiOptions(): array
    {
        return [
            'bcg' => 'BCG',
            'dpt_hb_hib_1' => 'DPT-HB-Hib 1',
            'dpt_hb_hib_2' => 'DPT-HB-Hib 2',
            'dpt_hb_hib_3' => 'DPT-HB-Hib 3',
            'polio_1' => 'Polio 1',
            'polio_2' => 'Polio 2',
            'polio_3' => 'Polio 3',
            'polio_4' => 'Polio 4',
            'ipv' => 'IPV',
            'campak' => 'Campak/MR',
        ];
    }

    private static function calculateAgeStatus(int $age): string
    {
        return match (true) {
            $age <= 5 => 'balita',
            $age <= 17 => 'anak',
            $age <= 59 => 'dewasa',
            default => 'lansia'
        };
    }

    private static function getBalitaOptions(Forms\Get $get): array
    {
        $anggotaKeluarga = $get('../../anggota_keluarga') ?? [];
        $balitaOptions = [];
        
        foreach ($anggotaKeluarga as $anggota) {
            if (isset($anggota['status']) && $anggota['status'] === 'balita' && !empty($anggota['nama'])) {
                $umur = isset($anggota['umur']) ? " ({$anggota['umur']} th)" : '';
                $balitaOptions[$anggota['nama']] = $anggota['nama'] . $umur;
            }
        }
        
        return $balitaOptions;
    }

    private static function getAnggotaItemLabel(array $state): ?string
    {
        return ($state['nama'] ?? 'Anggota Baru') . 
            (isset($state['umur']) ? " ({$state['umur']} th)" : '') .
            (isset($state['status_peran']) ? " - " . ucfirst(str_replace('_', ' ', $state['status_peran'])) : '');
    }

    private static function getGiziItemLabel(array $state): ?string
    {
        return ($state['nama_balita'] ?? 'Balita') . 
            (isset($state['status_gizi']) ? " - {$state['status_gizi']}" : '') .
            (isset($state['tanggal_pengukuran']) && $state['tanggal_pengukuran'] ? ' (' . date('d/m/Y', strtotime($state['tanggal_pengukuran'])) . ')' : '');
    }

    private static function calculateStatusGizi($beratBadan, $tinggiBadan, Forms\Set $set): void
    {
        if (!$beratBadan || !$tinggiBadan || $beratBadan <= 0 || $tinggiBadan <= 0) {
            $set('status_gizi', '');
            return;
        }

        $bmi = $beratBadan / (($tinggiBadan / 100) ** 2);
        
        $status = match (true) {
            $bmi < 14 => 'Sangat Kurus',
            $bmi < 16 => 'Kurus',
            $bmi < 18 => 'Normal',
            $bmi < 20 => 'Gemuk',
            default => 'Obesitas'
        };

        $set('status_gizi', $status);
    }

    private static function getTableColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('nama_kepala_keluarga')
                        ->label('Kepala Keluarga')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->icon('heroicon-m-user')
                        ->grow(false),
                    
                    Tables\Columns\TextColumn::make('rt_rw')
                        ->label('')
                        ->badge()
                        ->color('gray'),
                ]),
                
                Tables\Columns\Layout\Grid::make(2)
                    ->schema([

                        
                        Tables\Columns\TextColumn::make('status_ekonomi')
                            ->label('Status Ekonomi')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'mampu' => 'success',
                                'kurang_mampu' => 'warning',
                                'tidak_mampu' => 'danger',
                            }),
                        
                        Tables\Columns\TextColumn::make('telepon')
                            ->label('Telepon')
                            ->icon('heroicon-m-phone')
                            ->color('gray'),
                    ]),
                
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('jumlah_anggota')
                        ->label('Anggota')
                        ->alignCenter()
                        ->badge()
                        ->color('blue')
                        ->prefix('👥 '),
                    
                    Tables\Columns\TextColumn::make('jumlah_balita')
                        ->label('Balita')
                        ->alignCenter()
                        ->badge()
                        ->color(fn (int $state): string => match (true) {
                            $state === 0 => 'gray',
                            $state <= 2 => 'success',
                            default => 'warning',
                        })
                        ->prefix('👶 '),
                    
                    Tables\Columns\TextColumn::make('jumlah_ibu_hamil')
                        ->label('Ibu Hamil')
                        ->alignCenter()
                        ->badge()
                        ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray')
                        ->prefix('🤱 '),
                    
                    Tables\Columns\TextColumn::make('created_at')
                        ->label('Dibuat')
                        ->date('d/m/Y')
                        ->color('gray'),
                ]),
            ])
            ->space(2),
        ];
    }

    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('rt')
                ->label('RT')
                ->options(fn () => self::getRtOptions()),
            
            Tables\Filters\SelectFilter::make('rw')
                ->label('RW')
                ->options(fn () => self::getRwOptions()),
            
            Tables\Filters\SelectFilter::make('status_ekonomi')
                ->label('Status Ekonomi')
                ->options(self::getStatusEkonomiOptions()),
            
            Tables\Filters\Filter::make('has_balita')
                ->label('Memiliki Balita')
                ->query(fn (Builder $query): Builder => 
                    $query->whereRaw("JSON_SEARCH(anggota_keluarga, 'one', 'balita', null, '$[*].status') IS NOT NULL")
                )
                ->toggle(),
            
            Tables\Filters\Filter::make('has_ibu_hamil')
                ->label('Memiliki Ibu Hamil')
                ->query(fn (Builder $query): Builder => 
                    $query->whereRaw("JSON_SEARCH(anggota_keluarga, 'one', true, null, '$[*].is_ibu_hamil') IS NOT NULL")
                )
                ->toggle(),
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

    private static function getRwOptions(): array
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) return [];
        
        return Keluarga::where('user_id', $currentUserId)
            ->distinct()
            ->pluck('rw', 'rw')
            ->mapWithKeys(fn ($rw) => [$rw => "RW $rw"])
            ->toArray();
    }

    private static function getTableActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                
                Tables\Actions\Action::make('kunjungi')
                    ->label('Kunjungi Rumah')
                    ->icon('heroicon-m-home')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Kunjungi Rumah')
                    ->modalDescription(fn (Keluarga $record) => "Apakah Anda yakin ingin melakukan kunjungan ke rumah {$record->nama_kepala_keluarga}?")
                    ->modalSubmitActionLabel('Ya, Kunjungi')
                    ->action(function (Keluarga $record) {
                        return redirect('/admin/kunjungan-rumahs/create?keluarga_id=' . $record->getKey());
                    }),
                
                
            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->size('sm')
            ->color('gray')
            ->button(),
        ];
    }

    private static function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),
                
                Tables\Actions\BulkAction::make('bulk_print')
                    ->label('Cetak Kartu Keluarga Terpilih')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $count = $records->count();
                        Notification::make()
                            ->title("Mencetak {$count} kartu keluarga")
                            ->body('Kartu keluarga sedang diproses untuk dicetak.')
                            ->success()
                            ->send();
                    }),
            ]),
        ];
    }

    // Rest of the methods remain the same as in the original code...
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeluargas::route('/'),
            'create' => Pages\CreateKeluarga::route('/create'),
            'edit' => Pages\EditKeluarga::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $currentUserId = Auth::id();
        
        return parent::getEloquentQuery()
            ->when($currentUserId, fn (Builder $query) => $query->where('user_id', $currentUserId));
    }

    public static function getNavigationBadge(): ?string
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) return null;

        return static::getModel()::where('user_id', $currentUserId)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nama_kepala_keluarga',
            'alamat_lengkap',
            'rt',
            'rw',
            'telepon',
        ];
    }
}
            