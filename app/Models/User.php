<?php

declare(strict_types=1);

final class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        return $this->fetch('SELECT * FROM tb_user WHERE username = :username LIMIT 1', ['username' => $username]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetch('SELECT id_user, username, nama_lengkap, role, created_at, updated_at FROM tb_user WHERE id_user = :id LIMIT 1', ['id' => $id]);
    }

    public function allStaff(int $excludeId = 0): array
    {
        return $this->fetchAll(
            'SELECT id_user, username, nama_lengkap, role, created_at FROM tb_user WHERE role = :role AND id_user != :exclude ORDER BY created_at DESC',
            ['role' => 'Admin', 'exclude' => $excludeId]
        );
    }

    public function createStaff(array $data): bool
    {
        return $this->execute(
            'INSERT INTO tb_user (username, password, nama_lengkap, role) VALUES (:username, :password, :nama_lengkap, :role)',
            [
                'username' => $data['username'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'nama_lengkap' => $data['nama_lengkap'],
                'role' => 'Admin',
            ]
        );
    }

    public function deleteStaff(int $id): bool
    {
        return $this->execute('DELETE FROM tb_user WHERE id_user = :id AND role = :role', ['id' => $id, 'role' => 'Admin']);
    }

    public function usernameExists(string $username): bool
    {
        return $this->fetch('SELECT 1 FROM tb_user WHERE username = :username LIMIT 1', ['username' => $username]) !== null;
    }
}
