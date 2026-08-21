<?php

declare(strict_types=1);

final class ActivityLog extends Model
{
    /**
     * Record an activity log entry.
     */
    public static function log(string $aksi, string $modul, string $referensi, string $detail): void
    {
        $userId = Auth::id();
        if ($userId === null) return;

        try {
            (new self())->execute(
                'INSERT INTO tb_activity_log (id_user, aksi, modul, referensi, detail) VALUES (:id_user, :aksi, :modul, :referensi, :detail)',
                [
                    'id_user' => $userId,
                    'aksi' => $aksi,
                    'modul' => $modul,
                    'referensi' => $referensi,
                    'detail' => $detail,
                ]
            );
        } catch (Throwable $e) {
            error_log('ActivityLog::log failed: ' . $e->getMessage());
        }
    }

    /**
     * Get paginated activity logs with user info.
     */
    public function paginate(string $search = '', string $modul = '', int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if ($modul !== '') {
            $where .= ' AND a.modul = :modul';
            $params['modul'] = $modul;
        }

        if ($search !== '') {
            $where .= ' AND (u.nama_lengkap LIKE :search1 OR a.detail LIKE :search2 OR a.referensi LIKE :search3)';
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }

        $countRow = $this->fetch("SELECT COUNT(*) AS total FROM tb_activity_log a JOIN tb_user u ON a.id_user = u.id_user WHERE {$where}", $params);
        $total = (int) ($countRow['total'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchAll(
            "SELECT a.*, u.nama_lengkap, u.username FROM tb_activity_log a JOIN tb_user u ON a.id_user = u.id_user WHERE {$where} ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Get recent logs for dashboard or widgets.
     */
    public function recent(int $limit = 10): array
    {
        return $this->fetchAll(
            'SELECT a.*, u.nama_lengkap, u.username FROM tb_activity_log a JOIN tb_user u ON a.id_user = u.id_user ORDER BY a.created_at DESC LIMIT ' . $limit
        );
    }
}
