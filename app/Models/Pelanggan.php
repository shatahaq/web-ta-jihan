<?php

declare(strict_types=1);

final class Pelanggan extends Model
{
    public function paginate(string $search, string $status, int $page, int $perPage = 10): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(npa LIKE :search OR nama_pelanggan LIKE :search OR alamat LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if (in_array($status, ['Aktif', 'Nonaktif', 'Putus'], true)) {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        $condition = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $total = (int) ($this->fetch('SELECT COUNT(*) AS total FROM tb_pelanggan' . $condition, $params)['total'] ?? 0);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $this->fetchAll('SELECT * FROM tb_pelanggan' . $condition . ' ORDER BY created_at DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset, $params);
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function find(string $npa): ?array
    {
        return $this->fetch('SELECT * FROM tb_pelanggan WHERE npa = :npa LIMIT 1', ['npa' => $npa]);
    }

    public function create(array $data): bool
    {
        return $this->execute('INSERT INTO tb_pelanggan (npa, nama_pelanggan, alamat, no_telepon)
            VALUES (:npa, :nama_pelanggan, :alamat, :no_telepon)', $data);
    }

    public function update(string $npa, array $data): bool
    {
        $data['npa'] = $npa;
        return $this->execute('UPDATE tb_pelanggan SET nama_pelanggan = :nama_pelanggan, alamat = :alamat, no_telepon = :no_telepon WHERE npa = :npa', $data);
    }

    public function delete(string $npa): bool
    {
        return $this->execute('DELETE FROM tb_pelanggan WHERE npa = :npa', ['npa' => $npa]);
    }

    public function recent(int $limit = 6): array
    {
        return $this->fetchAll('SELECT * FROM tb_pelanggan ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }

    public function stats(): array
    {
        $limit = (int) config('nonaktif_limit_hari');
        return $this->fetch("SELECT
            COUNT(*) AS total,
            SUM(status = 'Aktif') AS aktif,
            SUM(status <> 'Aktif' AND (tgl_nonaktif IS NULL OR DATEDIFF(CURDATE(), tgl_nonaktif) < :limit_baru) ) AS nonaktif_baru,
            SUM(status <> 'Aktif' AND tgl_nonaktif IS NOT NULL AND DATEDIFF(CURDATE(), tgl_nonaktif) >= :limit_lama) AS nonaktif_lama
            FROM tb_pelanggan", ['limit_baru' => $limit, 'limit_lama' => $limit]) ?? [];
    }

    public function customerSearch(string $term, int $limit = 8): array
    {
        $term = trim($term);
        return $this->fetchAll('SELECT npa, nama_pelanggan, alamat, status, tgl_nonaktif FROM tb_pelanggan
            WHERE npa LIKE :term1 OR nama_pelanggan LIKE :term2 ORDER BY npa LIMIT ' . (int) $limit, ['term1' => '%' . $term . '%', 'term2' => '%' . $term . '%']);
    }

    public function report(array $filters): array
    {
        $where = [];
        $params = [];
        if (in_array($filters['status'] ?? '', ['Aktif', 'Nonaktif', 'Putus'], true)) {
            $where[] = 'p.status = :status'; $params['status'] = $filters['status'];
        }
        if (in_array($filters['jenis'] ?? '', ['Angkat Meter', 'Potong Pipa Dinas', 'Tutup Lubang Bor'], true)) {
            $where[] = 'latest_p.jenis_tindakan = :jenis'; $params['jenis'] = $filters['jenis'];
        }
        if (in_array($filters['verifikasi'] ?? '', ['Pending', 'Disetujui', 'Ditolak'], true)) {
            $where[] = 'latest_d.status_verifikasi = :verifikasi'; $params['verifikasi'] = $filters['verifikasi'];
        }
        if (($filters['periode'] ?? '') !== '') {
            $where[] = 'DATE(p.created_at) >= :periode'; $params['periode'] = $filters['periode'];
        }
        $condition = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return $this->fetchAll("SELECT p.*, latest_p.jenis_tindakan, latest_p.biaya_tindakan, latest_d.biaya_daftar_ulang,
            latest_d.status_verifikasi, latest_d.tgl_permohonan
            FROM tb_pelanggan p
            LEFT JOIN tb_pemutusan latest_p ON latest_p.id_pemutusan = (SELECT id_pemutusan FROM tb_pemutusan WHERE npa = p.npa ORDER BY tgl_pemutusan DESC, id_pemutusan DESC LIMIT 1)
            LEFT JOIN tb_daftar_ulang latest_d ON latest_d.no_registrasi = (SELECT no_registrasi FROM tb_daftar_ulang WHERE npa = p.npa ORDER BY tgl_permohonan DESC LIMIT 1)
            $condition ORDER BY p.nama_pelanggan ASC", $params);
    }

    public function setActive(string $npa): bool
    {
        return $this->execute("UPDATE tb_pelanggan SET status = 'Aktif', tgl_nonaktif = NULL WHERE npa = :npa", ['npa' => $npa]);
    }
}
