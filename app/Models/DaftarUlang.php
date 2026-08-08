<?php

declare(strict_types=1);

final class DaftarUlang extends Model
{
    public function paginate(string $status, int $page, int $perPage = 12): array
    {
        $params = []; $condition = '';
        if (in_array($status, ['Pending', 'Disetujui', 'Ditolak'], true)) { $condition = ' WHERE d.status_verifikasi = :status'; $params['status'] = $status; }
        $base = ' FROM tb_daftar_ulang d JOIN tb_pelanggan p ON p.npa = d.npa LEFT JOIN tb_user u ON u.id_user = d.id_user';
        $total = (int) ($this->fetch('SELECT COUNT(*) AS total' . $base . $condition, $params)['total'] ?? 0);
        $offset = max(0, ($page - 1) * $perPage);
        return ['rows' => $this->fetchAll('SELECT d.*, p.nama_pelanggan, p.alamat, u.nama_lengkap AS verifier' . $base . $condition . ' ORDER BY d.tgl_permohonan DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset, $params), 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function find(string $registration): ?array
    {
        return $this->fetch('SELECT d.*, p.nama_pelanggan, p.alamat, p.status AS status_pelanggan, u.nama_lengkap AS verifier
            FROM tb_daftar_ulang d JOIN tb_pelanggan p ON p.npa = d.npa LEFT JOIN tb_user u ON u.id_user = d.id_user
            WHERE d.no_registrasi = :registration LIMIT 1', ['registration' => $registration]);
    }

    public function latest(string $npa): ?array
    {
        return $this->fetch('SELECT * FROM tb_daftar_ulang WHERE npa = :npa ORDER BY tgl_permohonan DESC LIMIT 1', ['npa' => $npa]);
    }

    public function byPelanggan(string $npa): array
    {
        return $this->fetchAll('SELECT * FROM tb_daftar_ulang WHERE npa = :npa ORDER BY tgl_permohonan DESC', ['npa' => $npa]);
    }

    public function recent(int $limit = 5): array
    {
        return $this->fetchAll('SELECT d.*, p.nama_pelanggan FROM tb_daftar_ulang d JOIN tb_pelanggan p ON p.npa = d.npa ORDER BY d.tgl_permohonan DESC LIMIT ' . (int) $limit);
    }

    public function pendingCount(): int
    {
        return (int) ($this->fetch("SELECT COUNT(*) AS total FROM tb_daftar_ulang WHERE status_verifikasi = 'Pending'")['total'] ?? 0);
    }

    public function create(array $data): bool
    {
        return $this->execute('INSERT INTO tb_daftar_ulang (no_registrasi, npa, biaya_daftar_ulang, bukti_lunas, keterangan)
            VALUES (:no_registrasi, :npa, :biaya_daftar_ulang, :bukti_lunas, :keterangan)', $data);
    }

    public function verify(string $registration, string $status, int $userId, bool $activateCustomer = false): bool
    {
        $this->db->beginTransaction();
        try {
            $item = $this->fetch('SELECT npa, status_verifikasi FROM tb_daftar_ulang WHERE no_registrasi = :registration FOR UPDATE', ['registration' => $registration]);
            if (!$item || $item['status_verifikasi'] !== 'Pending') { $this->db->rollBack(); return false; }
            $this->execute('UPDATE tb_daftar_ulang SET status_verifikasi = :status, tgl_verifikasi = NOW(), id_user = :user_id WHERE no_registrasi = :registration', ['status' => $status, 'user_id' => $userId, 'registration' => $registration]);
            if ($status === 'Disetujui' && $activateCustomer) {
                $this->execute("UPDATE tb_pelanggan SET status = 'Aktif', tgl_nonaktif = NULL WHERE npa = :npa", ['npa' => $item['npa']]);
            }
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
}
