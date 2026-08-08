<?php

declare(strict_types=1);

function formatRupiah(float|int|string|null $amount): string { return 'Rp ' . number_format((float) $amount, 0, ',', '.'); }
function formatTanggal(?string $date): string { if (!$date) return '—'; return (new DateTime($date))->format('d ') . ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)(new DateTime($date))->format('n') - 1] . (new DateTime($date))->format(' Y'); }
function formatTanggalWaktu(?string $date): string { return $date ? formatTanggal($date) . (new DateTime($date))->format(', H:i') . ' WIB' : '—'; }
function kategoriStatus(array $pelanggan): array {
    $status = $pelanggan['status'] ?? 'Aktif';
    if ($status === 'Aktif') return ['key' => 'aktif', 'label' => 'AKTIF', 'days' => 0];
    if (!$pelanggan['tgl_nonaktif']) return ['key' => 'nonaktif_baru', 'label' => 'NONAKTIF < 60 HARI', 'days' => 0];
    $days = max(0, (int) (new DateTime($pelanggan['tgl_nonaktif']))->diff(new DateTime('today'))->format('%r%a'));
    $limit = (int) config('nonaktif_limit_hari');
    // Kebijakan boundary: hari ke-60 masuk kategori > 60 hari untuk proses daftar ulang.
    return $days < $limit
        ? ['key' => 'nonaktif_baru', 'label' => 'NONAKTIF < ' . $limit . ' HARI', 'days' => $days]
        : ['key' => 'nonaktif_lama', 'label' => 'NONAKTIF > ' . $limit . ' HARI', 'days' => $days];
}
