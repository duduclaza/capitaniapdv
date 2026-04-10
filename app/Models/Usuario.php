<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email): ?array
    {
        return $this->findOneWhere(['email' => $email]);
    }

    public function authenticate(string $email, string $senha): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user) return null;
        if (!$user['ativo']) return null;
        if (!password_verify($senha, $user['senha_hash'])) return null;

        return $user;
    }

    public function createUser(array $data): int
    {
        $data['senha_hash'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        unset($data['senha']);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        return $this->insert($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        if (!empty($data['senha'])) {
            $data['senha_hash'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }
        unset($data['senha']);
        $data['updated_at'] = now();
        return $this->update($id, $data);
    }
}
