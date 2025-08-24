<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendaKegiatanResource\Pages;
use App\Models\AgendaKegiatan;
use App\Models\Keluarga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AgendaKegiatanResource extends Resource
{
    protected static ?string $model = AgendaKegiatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Agenda Kegiatan';
    protected static ?string $navigationGroup = 'Kegiatan & Agenda';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'nama_kegiatan';

    public static function getNavigationBadge(): ?string
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return null;
        }

        $count = static::getModel()::where('user_id', $currentUserId)
            ->where('status', 'aktif')
            ->whereDate('tanggal_waktu', '<=', now()->addDays(3))
            ->count();

        return $count ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Agenda Kegiatan')
                    ->description('Buat agenda kegiatan dengan mudah')
                    ->schema([
                        Tabs::make('Agenda Kegiatan')
                            ->tabs([
                                self::getInformasiKegiatanTab(),
                                self::getTargetPesertaTab(),
                                self::getHasilEvaluasiTab(),
                            ])
                            ->columnSpanFull()
                            ->persistTabInQueryString(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function getInformasiKegiatanTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Informasi Kegiatan')
            ->icon('heroicon-m-calendar')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status Kegiatan')
                            ->required()
                            ->options([
                                'draft' => '📝 Draft',
                                'aktif' => '🟢 Aktif',
                                'selesai' => '✅ Selesai',
                                'dibatalkan' => '❌ Dibatalkan',
                            ])
                            ->default('draft')
                            ->reactive()
                            ->native(false),

                    ]),

                Forms\Components\TextInput::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Posyandu Balita Bulan September, Penyuluhan PHBS')
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('tanggal_waktu')
                            ->label('Tanggal & Waktu')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minDate(now()->subDays(1))
                            ->helperText('Tanggal pelaksanaan kegiatan'),

                        Forms\Components\TextInput::make('tempat')
                            ->label('Tempat Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Balai Desa, Posyandu, Rumah Warga, dll'),
                    ]),

                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi Kegiatan')
                    ->rows(3)
                    ->placeholder('Jelaskan detail kegiatan, agenda, dan hal-hal yang perlu dipersiapkan')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama penanggung jawab kegiatan'),
            ]);
    }

    private static function getTargetPesertaTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Target Peserta')
            ->icon('heroicon-m-users')
            ->schema([
                Section::make('Penetapan Target Peserta')
                    ->description('Tentukan siapa saja yang akan diundang dalam kegiatan ini')
                    ->schema([
                        Forms\Components\Select::make('target_peserta.jenis')
                            ->label('Jenis Target Peserta')
                            ->required()
                            ->options([
                                'semua' => '👥 Semua Keluarga',
                                'manual' => '✋ Pilih Manual',
                            ])
                            ->reactive()
                            ->native(false)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state !== 'manual') {
                                    $set('target_peserta.keluarga_manual', null);
                                }
                            }),

                        Forms\Components\Select::make('target_peserta.keluarga_manual')
                            ->label('Pilih Keluarga')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn () => self::getKeluargaOptions())
                            ->visible(fn (Forms\Get $get) => $get('target_peserta.jenis') === 'manual')
                            ->helperText('Pilih keluarga yang akan diundang ke kegiatan ini'),

                        Forms\Components\Placeholder::make('target_info')
                            ->label('Estimasi Jumlah Peserta')
                            ->content(fn (Forms\Get $get) => self::getEstimasiPeserta($get))
                            ->visible(fn (Forms\Get $get) => !empty($get('target_peserta.jenis'))),
                    ]),
            ]);
    }

    private static function getHasilEvaluasiTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Hasil & Evaluasi')
            ->icon('heroicon-m-document-text')
            ->schema([
                Forms\Components\Textarea::make('catatan_kegiatan')
                    ->label('Catatan Selama Kegiatan')
                    ->rows(4)
                    ->placeholder('Catatan penting selama kegiatan berlangsung, hambatan yang ditemui, dll.')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('hasil_kegiatan')
                    ->label('Hasil dan Pencapaian Kegiatan')
                    ->rows(4)
                    ->placeholder('Hasil yang dicapai, kesimpulan, dan evaluasi kegiatan')
                    ->columnSpanFull(),
            ]);
    }


    private static function getKeluargaOptions(): array
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return [];
        }
        
        return Keluarga::where('user_id', $currentUserId)
            ->orderBy('nama_kepala_keluarga')
            ->get()
            ->mapWithKeys(function ($keluarga) {
                $nama = $keluarga->nama_kepala_keluarga;
                $rtRw = "RT {$keluarga->rt}/RW {$keluarga->rw}";
                $info = '';
                
                if ($keluarga->jumlah_balita > 0) {
                    $info .= " ({$keluarga->jumlah_balita} balita)";
                }
                
                if ($keluarga->jumlah_ibu_hamil > 0) {
                    $info .= " ({$keluarga->jumlah_ibu_hamil} bumil)";
                }
                
                return [$keluarga->id => "$nama - $rtRw$info"];
            })
            ->toArray();
    }

    private static function getEstimasiPeserta(Forms\Get $get): string
    {
        $jenis = $get('target_peserta.jenis');
        $userId = Auth::id();
        
        if (!$userId || !$jenis) {
            return 'Pilih jenis target peserta untuk melihat estimasi';
        }
        
        $count = match($jenis) {
            'semua' => Keluarga::where('user_id', $userId)->count(),
            'manual' => count($get('target_peserta.keluarga_manual') ?? []),
            default => 0
        };
        
        $label = match($jenis) {
            'semua' => '👥 Semua keluarga',
            'manual' => '✋ Keluarga terpilih manual',
            default => 'Tidak diketahui'
        };
        
        return "🎯 $label: $count keluarga akan diundang";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->wrap()
                    ->description(fn (AgendaKegiatan $record): string => 
                        $record->tempat . ' • ' . $record->penanggung_jawab
                    ),

                Tables\Columns\TextColumn::make('tanggal_waktu')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->beforeStateUpdated(fn ($record, $state) => self::validateStatusUpdate($record, $state))
                    ->afterStateUpdated(fn ($record, $state) => self::notifyStatusUpdate($state)),

                Tables\Columns\TextColumn::make('target_peserta.jenis')
                    ->label('Target')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'semua' => '👥 Semua',
                        'manual' => '✋ Manual',
                        default => 'Unknown'
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(self::getTableFilters())
            ->actions(self::getTableActions())
            ->bulkActions(self::getTableBulkActions())
            ->headerActions([
                Tables\Actions\Action::make('download_laporan')
                    ->label('Download Laporan PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Filter Status')
                            ->options([
                                'draft' => 'Draft',
                                'aktif' => 'Aktif',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->placeholder('Semua Status'),
                        
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
                        return redirect("/reports/agenda?{$params}");
                    })
                    ->modalHeading('Download Laporan Agenda Kegiatan')
                    ->modalDescription('Pilih periode dan filter untuk laporan yang akan didownload')
                    ->modalSubmitActionLabel('Download PDF')
                    ->modalWidth(MaxWidth::Medium),
            ])
            ->defaultSort('tanggal_waktu', 'desc')
            ->poll('60s')
            ->deferLoading()
            ->striped();
    }

    private static function validateStatusUpdate($record, $state): bool
    {
        if ($record->status === 'selesai' && $state !== 'selesai') {
            Notification::make()
                ->title('Tidak dapat mengubah status')
                ->body('Kegiatan yang sudah selesai tidak dapat diubah statusnya')
                ->danger()
                ->send();
            return false;
        }
        return true;
    }

    private static function notifyStatusUpdate(string $state): void
    {
        Notification::make()
            ->title('Status berhasil diubah')
            ->body("Status kegiatan diubah menjadi: $state")
            ->success()
            ->send();
    }

    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'aktif' => 'Aktif',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                ]),


            Tables\Filters\SelectFilter::make('target_peserta.jenis')
                ->label('Target Peserta')
                ->options([
                    'semua' => 'Semua Keluarga',
                    'manual' => 'Manual',
                ]),

            Tables\Filters\Filter::make('minggu_ini')
                ->label('Minggu Ini')
                ->query(fn (Builder $query): Builder => 
                    $query->whereBetween('tanggal_waktu', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])
                )
                ->toggle(),

            Tables\Filters\Filter::make('bulan_ini')
                ->label('Bulan Ini')
                ->query(fn (Builder $query): Builder => 
                    $query->whereMonth('tanggal_waktu', now()->month)
                          ->whereYear('tanggal_waktu', now()->year)
                )
                ->toggle(),
        ];
    }

    private static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->slideOver(),

            Tables\Actions\EditAction::make()
                ->slideOver(),

            Tables\Actions\Action::make('duplicate')
                ->label('Duplikasi')
                ->icon('heroicon-m-document-duplicate')
                ->color('info')
                ->action(fn (AgendaKegiatan $record) => self::duplicateRecord($record)),
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

    private static function duplicateRecord(AgendaKegiatan $record)
    {
        $newRecord = $record->replicate([
            'hasil_kegiatan',
            'catatan_kegiatan',
        ]);
        
        $newRecord->nama_kegiatan = $record->nama_kegiatan . ' (Copy)';
        $newRecord->status = 'draft';
        $newRecord->tanggal_waktu = $record->tanggal_waktu->addWeek();
        $newRecord->save();
        
        Notification::make()
            ->title('Kegiatan berhasil diduplikasi')
            ->success()
            ->send();
        
        return redirect(static::getUrl('edit', ['record' => $newRecord]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgendaKegiatans::route('/'),
            'create' => Pages\CreateAgendaKegiatan::route('/create'),
            'edit' => Pages\EditAgendaKegiatan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $currentUserId = Auth::id();
        
        return parent::getEloquentQuery()
            ->when($currentUserId, fn (Builder $query) => 
                $query->where('user_id', $currentUserId)
            );
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nama_kegiatan',
            'tempat',
            'penanggung_jawab',
            'deskripsi',
        ];
    }

    // Permission methods
    public static function canCreate(): bool
    {
        return Auth::check();
    }

    public static function canEdit($record): bool
    {
        return Auth::id() === $record->user_id;
    }

    public static function canDelete($record): bool
    {
        return Auth::id() === $record->user_id && $record->status === 'draft';
    }

    public static function canView($record): bool
    {
        return Auth::id() === $record->user_id;
    }
}