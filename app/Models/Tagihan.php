<?php

declare(strict_types=1);

final class Tagihan extends Model
{
    public function paginate(string $search, string $status, int $page, int $perPage = 12): array
    {
        $where = [];
        $params = [];
        if ($search !== '') { $where[] = '(t.npa LIKE :search OR p.nama_pelanggan LIKE :search)'; $params['search'] = '%' . $search . '%'; }
        if (in_array($status, ['Lunas', 'Belum Lunas'], true)) { $where[] = 't.status_bayar = :status'; $params['status'] = $status; }
        $condition = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $base = ' FROM tb_tagihan t INNER JOIN tb_pelanggan p ON p.npa = t.npa';
        $total = (int) ($this->fetch('SELECT COUNT(*) AS total' . $base . $condition, $params)['total'] ?? 0);
        $offset = max(0, ($page - 1) * $perPage);
        return ['rows' => $this->fetchAll('SELECT t.*, p.nama_pelanggan' . $base . $condition . ' ORDER BY t.periode DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset, $params), 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function byPelanggan(string $npa): array
    {
        return $this->fetchAll('SELECT * FROM tb_tagihan WHERE npa = :npa ORDER BY periode DESC', ['npa' => $npa]);
    }

    public function find(int $id): ?array
    {
        return $this->fetch('SELECT t.*, p.nama_pelanggan FROM tb_tagihan t JOIN tb_pelanggan p ON p.npa = t.npa WHERE t.id_tagihan = :id', ['id' => $id]);
    }

    public function summary(string $npa): array
    {
        return $this->fetch("SELECT COUNT(*) AS jumlah_tagihan, SUM(status_bayar = 'Belum Lunas') AS belum_lunas,
            COALESCE(SUM(CASE WHEN status_bayar = 'Belum Lunas' THEN total_tagihan ELSE 0 END), 0) AS total_tunggakan
            FROM tb_tagihan WHERE npa = :npa", ['npa' => $npa]) ?? ['jumlah_tagihan' => 0, 'belum_lunas' => 0, 'total_tunggakan' => 0];
    }

    public function create(array $data): bool
    {
        return $this->execute('INSERT INTO tb_tagihan (npa, periode, meter_awal, meter_akhir, total_tagihan, status_bayar, tgl_bayar)
            VALUES (:npa, :periode, :meter_awal, :meter_akhir, :total_tagihan, :status_bayar, :tgl_bayar)', $data);
    }

    public function update(int $id, array $data): bool
    {
        unset($data['npa']);
        $data['id'] = $id;
        return $this->execute('UPDATE tb_tagihan SET periode = :periode, meter_awal = :meter_awal, meter_akhir = :meter_akhir,
            total_tagihan = :total_tagihan, status_bayar = :status_bayar, tgl_bayar = :tgl_bayar WHERE id_tagihan = :id', $data);
    }
}
