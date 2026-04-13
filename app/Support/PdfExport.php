<?php

namespace App\Support;

use App\Models\BorrowedItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfExport
{
    public static function download(string $filename, string $title, array $headers, array $rows): StreamedResponse
    {
        $escapedTitle = e($title);

        $headerHtml = collect($headers)
            ->map(fn (string $header): string => '<th>' . e($header) . '</th>')
            ->implode('');

        $bodyHtml = collect($rows)
            ->map(function (array $row): string {
                $columns = collect($row)
                    ->map(fn (mixed $value): string => '<td>' . e((string) ($value ?? '-')) . '</td>')
                    ->implode('');

                return "<tr>{$columns}</tr>";
            })
            ->implode('');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
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

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    public static function downloadBorrowReceipt(BorrowedItem $borrowedItem): StreamedResponse
    {
        $borrowedItem->loadMissing(['staff', 'item']);

        $date = optional($borrowedItem->date)?->format('d M Y H:i') ?? '-';
        $filename = 'struk-peminjaman-' . $borrowedItem->id . '.pdf';
        $staffName = e($borrowedItem->staff?->name ?? '-');
        $borrowerName = e($borrowedItem->name_of_borrower);
        $itemName = e($borrowedItem->item?->item_name ?? '-');
        $totalItem = e((string) $borrowedItem->total_item);
        $notes = e($borrowedItem->notes ?: '-');

        $printedAt = now()->format('d M Y H:i');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .receipt { border: 1px dashed #333; padding: 14px; }
        .title { text-align: center; font-size: 15px; font-weight: bold; margin: 0; }
        .subtitle { text-align: center; margin: 2px 0 10px; font-size: 11px; }
        .line { border-top: 1px dashed #666; margin: 8px 0; }
        .meta, .details { width: 100%; border-collapse: collapse; }
        .meta td { padding: 2px 0; }
        .meta td:first-child { width: 34%; font-weight: bold; }
        .details th, .details td { padding: 6px 4px; text-align: left; border-bottom: 1px solid #ddd; }
        .details th { border-top: 1px solid #111; border-bottom: 1px solid #111; }
        .details td:last-child, .details th:last-child { text-align: right; }
        .notes { margin-top: 8px; font-size: 10px; }
        .signatures { margin-top: 26px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 0 8px; }
        .sign-gap { height: 52px; }
        .sign-line { border-top: 1px solid #222; padding-top: 4px; font-size: 10px; }
        .footer { margin-top: 10px; text-align: center; font-size: 10px; }
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

        $pdf = Pdf::loadHTML($html)->setPaper('a5', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
