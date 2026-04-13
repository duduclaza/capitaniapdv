<?php

namespace App\Models;

use App\Core\Model;

class Funcionario extends Model
{
    protected string $table = 'funcionarios';

    public function findAllOrdered(): array
    {
        return $this->raw(
            "SELECT *
             FROM funcionarios
             ORDER BY ativo DESC, nome ASC"
        );
    }

    public function findAtivos(): array
    {
        return $this->raw(
            "SELECT *
             FROM funcionarios
             WHERE ativo = 1
             ORDER BY nome ASC"
        );
    }

    public function createFuncionario(array $data): int
    {
        return $this->insert([
            'nome' => trim((string)($data['nome'] ?? '')),
            'cargo' => trim((string)($data['cargo'] ?? '')),
            'telefone' => trim((string)($data['telefone'] ?? '')),
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updateFuncionario(int $id, array $data): bool
    {
        return $this->update($id, [
            'nome' => trim((string)($data['nome'] ?? '')),
            'cargo' => trim((string)($data['cargo'] ?? '')),
            'telefone' => trim((string)($data['telefone'] ?? '')),
            'updated_at' => now(),
        ]);
    }

    public function setAtivo(int $id, bool $ativo): bool
    {
        return $this->update($id, [
            'ativo' => $ativo ? 1 : 0,
            'updated_at' => now(),
        ]);
    }
}
