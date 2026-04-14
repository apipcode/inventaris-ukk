<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\PdfExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Kelas Table untuk resource User.
 *
 * Mendefinisikan tampilan tabel daftar pengguna: kolom, dan aksi.
 * Dipisahkan dari Resource utama agar kode lebih ringkas dan mudah dimodifikasi.
 *
 * Catatan: Data yang muncul di tabel ini sudah difilter oleh UserResource::getEloquentQuery()
 * berdasarkan role pengguna yang sedang login.
 */
class UsersTable
{
    /**
     * Mengkonfigurasi dan mengembalikan skema tabel.
     *
     * @param Table $table Objek tabel Filament yang akan dikonfigurasi.
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            // =========================================================
            // KOLOM TABEL
            // =========================================================
            ->columns([
                // Nama lengkap pengguna
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                // Alamat email pengguna
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                // Role pengguna ditampilkan sebagai badge berwarna
                // Admin = warna biru (primary), Staff = warna abu-abu (gray)
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'primary',
                        'staff' => 'gray',
                        default => 'gray',
                    }),

                // Tanggal akun dibuat (disembunyikan secara default)
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // =========================================================
            // AKSI PER BARIS (Record Actions)
            // =========================================================
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            // =========================================================
            // AKSI HEADER (Header Actions) — Tombol di atas tabel
            // =========================================================
            ->headerActions([
                // Tombol "Export PDF": mengunduh seluruh data pengguna ke PDF
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $rows = User::query()
                            ->orderBy('name')
                            ->get()
                            ->map(fn (User $user): array => [
                                $user->name,
                                $user->email,
                                $user->role,
                                optional($user->created_at)?->format('d M Y'),
                            ])
                            ->all();

                        return PdfExport::download(
                            filename: 'users.pdf',
                            title:    'Data Pengguna',
                            headers:  ['Nama', 'Email', 'Role', 'Dibuat'],
                            rows:     $rows,
                        );
                    }),
            ])

            // =========================================================
            // BULK ACTIONS — Aksi untuk banyak baris sekaligus
            // =========================================================
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
