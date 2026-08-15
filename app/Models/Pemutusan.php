<?php

declare(strict_types=1);

final class Pemutusan extends Model
{
    public function paginate(string $search, int $page, int $perPage = 12): array
    {
        $params = []; $condition = '';
        if ($search !== '') { $condition = ' WHERE (m.npa LIKE :search1 OR p.nama_pelanggan LIKE :search2)'; $params['search1'] = '%' . $search . '%'; $params['search2'] = '%' . $search . '%'; }
        $base = ' FROM tb_pemutusan m JOIN tb_pelanggan p ON p.npa = m.npa';
        $total = (int) ($this->fetch('SELECT COUNT(*) AS total' . $base . $condition, $params)['total'] ?? 0);
        $offset = max(0, ($page - 1) * $perPage);
        return ['rows' => $this->fetchAll('SELECT m.*, p.nama_pelanggan' . $base . $condition . ' ORDER BY m.tgl_pemutusan DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset, $params), 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function find(int $id): ?array
    {
        return $this->fetch('SELECT m.*, p.nama_pelanggan, p.alamat FROM tb_pemutusan m JOIN tb_pelanggan p ON p.npa = m.npa WHERE m.id_pemutusan = :id', ['id' => $id]);
    }

    public function latest(string $npa): ?array
    {
        return $this->fetch('SELECT * FROM tb_pemutusan WHERE npa = :npa ORDER BY tgl_pemutusan DESC, id_pemutusan DESC LIMIT 1', ['npa' => $npa]);
    }

    public function byPelanggan(string $npa): array
    {
        return $this->fetchAll('SELECT * FROM tb_pemutusan WHERE npa = :npa ORDER BY tgl_pemutusan DESC', ['npa' => $npa]);
    }

    public function recent(int $limit = 6): array
    {
        return $this->fetchAll('SELECT m.*, p.nama_pelanggan FROM tb_pemutusan m JOIN tb_pelanggan p ON p.npa = m.npa ORDER BY m.tgl_pemutusan DESC LIMIT ' . (int) $limit);
    }

    public function create(array $data): bool
    {
        $this->execute("UPDATE tb_pelanggan SET status = 'Putus', tgl_nonaktif = :tgl_pemutusan WHERE npa = :npa", ['npa' => $data['npa'], 'tgl_pemutusan' => $data['tgl_pemutusan']]);
        return $this->execute('INSERT INTO tb_pemutusan (npa, tgl_pemutusan, status_pemutusan, jenis_tindakan, biaya_tindakan, keterangan)
            VALUES (:npa, :tgl_pemutusan, :status_pemutusan, :jenis_tindakan, :biaya_tindakan, :keterangan)', $data);
    }

    public function update(int $id, array $data): bool
    {
        $npa = $this->fetch('SELECT npa FROM tb_pemutusan WHERE id_pemutusan = :id', ['id' => $id])['npa'] ?? '';
        if ($npa) {
            $this->execute("UPDATE tb_pelanggan SET tgl_nonaktif = :tgl_pemutusan WHERE npa = :npa", ['npa' => $npa, 'tgl_pemutusan' => $data['tgl_pemutusan']]);
        }
        unset($data['npa']);
        $data['id'] = $id;
        return $this->execute('UPDATE tb_pemutusan SET tgl_pemutusan = :tgl_pemutusan, status_pemutusan = :status_pemutusan,
            jenis_tindakan = :jenis_tindakan, biaya_tindakan = :biaya_tindakan, keterangan = :keterangan WHERE id_pemutusan = :id', $data);
    }
}
