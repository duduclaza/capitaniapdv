<?php

namespace App\Models;

use App\Core\Model;

class Categoria extends Model
{
    protected string $table = 'categorias';

    public function findAtivas(): array
    {
        return $this->findWhere(['ativo' => 1], 'nome');
    }
}
