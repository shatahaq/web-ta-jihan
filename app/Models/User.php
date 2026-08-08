<?php

declare(strict_types=1);

final class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        return $this->fetch('SELECT * FROM tb_user WHERE username = :username LIMIT 1', ['username' => $username]);
    }
}
