<?php

namespace App\Models;

use App\Core\Model;

class Comanda extends Model
{
    protected string $table = 'comandas';

    public function findAbertas(): array
    {
        return $this->raw(
            "SELECT c.*, m.numero AS mesa_numero, u.nome AS operador_nome,
                    cli.nome AS cliente_nome
             FROM comandas c
             JOIN mesas m ON m.id = c.mesa_id
             LEFT JOIN usuarios u ON u.id = c.opened_by
             LEFT JOIN clientes cli ON cli.id = c.cliente_id
             WHERE c.status = 'aberta'
             ORDER BY c.opened_at DESC"
        );
    }

    public function findById(int $id): ?array
    {
        return $this->rawOne(
            "SELECT c.*, m.numero AS mesa_numero, u.nome AS operador_nome,
                    cli.nome AS cliente_nome
             FROM comandas c
             JOIN mesas m ON m.id = c.mesa_id
             LEFT JOIN usuarios u ON u.id = c.opened_by
             LEFT JOIN clientes cli ON cli.id = c.cliente_id
             WHERE c.id = ?",
            [$id]
        );
    }

    public function getItens(int $comandaId): array
    {
        return $this->raw(
            "SELECT ci.*, p.nome AS produto_nome, p.unidade
             FROM comanda_itens ci
             JOIN produtos p ON p.id = ci.produto_id
             WHERE ci.comanda_id = ?
             ORDER BY ci.created_at ASC",
            [$comandaId]
        );
    }

    public function recalcularTotal(int $comandaId): void
    {
        $this->rawExec(
            "UPDATE comandas c
             SET c.subtotal = (SELECT COALESCE(SUM(ci.total_item), 0) FROM comanda_itens ci WHERE ci.comanda_id = c.id),
                 c.total = c.subtotal - c.desconto
             WHERE c.id = ?",
            [$comandaId]
        );
    }

    public function addItem(int $comandaId, int $produtoId, float $quantidade, float $precoUnitario, string $observacao = ''): int
    {
        $totalItem = $quantidade * $precoUnitario;
        $id = $this->rawScalar(
            "INSERT INTO comanda_itens (comanda_id, produto_id, quantidade, preco_unitario, observacao, total_item, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$comandaId, $produtoId, $quantidade, $precoUnitario, $observacao, $totalItem]
        );
        // Actually lastInsertId
        $id = $this->db->lastInsertId();
        $this->recalcularTotal($comandaId);
        return (int) $id;
    }

    public function removeItem(int $itemId): void
    {
        $item = $this->rawOne("SELECT * FROM comanda_itens WHERE id = ?", [$itemId]);
        if ($item) {
            $this->rawExec("DELETE FROM comanda_itens WHERE id = ?", [$itemId]);
            $this->recalcularTotal($item['comanda_id']);
        }
    }

    public function fechar(int $id, int $userId): void
    {
        $this->rawExec(
            "UPDATE comandas SET status = 'paga', closed_by = ?, closed_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$userId, $id]
        );
    }

    public function cancelar(int $id, int $userId): void
    {
        $this->rawExec(
            "UPDATE comandas SET status = 'cancelada', closed_by = ?, closed_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$userId, $id]
        );
    }

    public function countAbertas(): int
    {
        return (int) $this->rawScalar("SELECT COUNT(*) FROM comandas WHERE status = 'aberta'");
    }
}
