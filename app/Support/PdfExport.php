<?php

namespace App\Support;

use App\Models\BorrowedItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kelas helper untuk menghasilkan dan mengunduh file PDF.
 *
 * Menyediakan dua jenis ekspor:
 *  1. download()             — Tabel data generik (kategori, stok, peminjaman, pengguna).
 *  2. downloadBorrowReceipt() — Struk peminjaman per transaksi (format kecil, siap cetak).
 *
 * Menggunakan library barryvdh/laravel-dompdf di balik layar.
 */
class PdfExport
{
    /**
     * Menghasilkan dan mengunduh PDF berisi tabel data generik.
     *
     * @param string   $filename Nama file PDF yang akan diunduh.
     * @param string   $title    Judul yang muncul di bagian atas PDF.
     * @param string[] $headers  Array nama kolom untuk baris header tabel.
     * @param array[]  $rows     Array of array berisi data setiap baris tabel.
     * @return StreamedResponse  Response unduhan file PDF.
     */
    public static function download(string $filename, string $title, array $headers, array $rows): StreamedResponse
    {
        // Escape judul untuk mencegah XSS dalam HTML
        $escapedTitle = e($title);

        // Bangun baris header tabel dari array $headers
        $headerHtml = collect($headers)
            ->map(fn (string $header): string => '<th>' . e($header) . '</th>')
            ->implode('');

        // Bangun isi tabel dari array $rows (setiap item di $rows adalah satu baris)
        $bodyHtml = collect($rows)
            ->map(function (array $row): string {
                $columns = collect($row)
                    ->map(fn (mixed $value): string => '<td>' . e((string) ($value ?? '-')) . '</td>')
                    ->implode('');

                return "<tr>{$columns}</tr>";
            })
            ->implode('');

        // Template HTML untuk dokumen PDF
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1    { font-size: 16px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th    { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>{$escapedTitle}</h1>
    <table>
        <thead>
            <tr>{$headerHtml}</tr>
        </thead>
        <tbody>
            {$bodyHtml}
        </tbody>
    </table>
</body>
</html>
HTML;

        // Render HTML ke PDF dengan ukuran kertas A4 landscape
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

        // Kembalikan sebagai response stream (unduhan langsung di browser)
        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Menghasilkan dan mengunduh struk peminjaman dalam format PDF siap cetak.
     *
     * Struk berisi: informasi transaksi, detail barang, catatan,
     * dan area tanda tangan Peminjam & Petugas.
     *
     * @param BorrowedItem $borrowedItem Data transaksi peminjaman yang akan dicetak.
     * @return StreamedResponse          Response unduhan file PDF struk.
     */
    public static function downloadBorrowReceipt(BorrowedItem $borrowedItem): StreamedResponse
    {
        // Pastikan relasi staff dan item sudah di-load (lazy loading jika belum)
        $borrowedItem->loadMissing(['staff', 'item']);

        // Siapkan data yang akan ditampilkan di struk
        $date        = optional($borrowedItem->date)?->format('d M Y H:i') ?? '-';
        $filename    = 'struk-peminjaman-' . $borrowedItem->id . '.pdf';
        $printedAt   = now()->format('d M Y H:i');

        // Escape semua nilai string untuk keamanan (mencegah HTML injection)
        $staffName    = e($borrowedItem->staff?->name ?? '-');
        $borrowerName = e($borrowedItem->name_of_borrower);
        $itemName     = e($borrowedItem->item?->item_name ?? '-');
        $totalItem    = e((string) $borrowedItem->total_item);
        $notes        = e($borrowedItem->notes ?: '-');

        // Template HTML untuk struk peminjaman (ukuran A5, format potret)
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body        { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .receipt    { border: 1px dashed #333; padding: 14px; }
        .title      { text-align: center; font-size: 15px; font-weight: bold; margin: 0; }
        .subtitle   { text-align: center; margin: 2px 0 10px; font-size: 11px; }
        .line       { border-top: 1px dashed #666; margin: 8px 0; }
        .meta, .details { width: 100%; border-collapse: collapse; }
        .meta td    { padding: 2px 0; }
        .meta td:first-child { width: 34%; font-weight: bold; }
        .details th, .details td { padding: 6px 4px; text-align: left; border-bottom: 1px solid #ddd; }
        .details th { border-top: 1px solid #111; border-bottom: 1px solid #111; }
        .details td:last-child, .details th:last-child { text-align: right; }
        .notes      { margin-top: 8px; font-size: 10px; }
        .signatures { margin-top: 26px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 0 8px; }
        .sign-gap   { height: 52px; }
        .sign-line  { border-top: 1px solid #222; padding-top: 4px; font-size: 10px; }
        .footer     { margin-top: 10px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="receipt">
        <p class="title">STRUK PEMINJAMAN BARANG</p>
        <p class="subtitle">Inventaris</p>
        <div class="line"></div>

        <table class="meta">
            <tr><td>No. Transaksi</td><td>: #{$borrowedItem->id}</td></tr>
            <tr><td>Tanggal Pinjam</td><td>: {$date}</td></tr>
            <tr><td>Dicetak Pada</td><td>: {$printedAt}</td></tr>
            <tr><td>Petugas</td><td>: {$staffName}</td></tr>
            <tr><td>Peminjam</td><td>: {$borrowerName}</td></tr>
        </table>

        <div class="line"></div>

        <table class="details">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{$itemName}</td>
                    <td>{$totalItem}</td>
                </tr>
            </tbody>
        </table>

        <div class="notes"><strong>Catatan:</strong> {$notes}</div>

        <table class="signatures">
            <tr>
                <td>
                    Peminjam
                    <div class="sign-gap"></div>
                    <div class="sign-line">(.................................)</div>
                </td>
                <td>
                    Petugas
                    <div class="sign-gap"></div>
                    <div class="sign-line">(.................................)</div>
                </td>
            </tr>
        </table>

        <div class="footer">Simpan struk ini sebagai bukti peminjaman.</div>
    </div>
</body>
</html>
HTML;

        // Render HTML ke PDF dengan ukuran kertas A5 potret
        $pdf = Pdf::loadHTML($html)->setPaper('a5', 'portrait');

        // Kembalikan sebagai response stream (unduhan langsung di browser)
        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
