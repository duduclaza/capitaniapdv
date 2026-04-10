<?php

namespace App\Models;

use App\Core\Model;

class Mesa extends Model
{
    protected string $table = 'mesas';

    public function findAllWithStatus(): array
    {
        return $this->raw(
            "SELECT m.*, 
                    c.id AS comanda_id,
                    c.subtotal AS comanda_subtotal
             FROM mesas m
             LEFT JOIN comandas c ON c.mesa_id = m.id AND c.status = 'aberta'
             ORDER BY m.numero ASC"
        );
    }

    public function setStatus(int $id, string $status): void
    {
        $this->rawExec("UPDATE mesas SET status = ? WHERE id = ?", [$status, $id]);
    }
}
