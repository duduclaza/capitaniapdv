<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Categoria;

class ProdutoController extends Controller
{
    private Produto $produto;
    private Categoria $categoria;

    public function __construct()
    {
        $this->produto   = new Produto();
        $this->categoria = new Categoria();
    }

    public function index(): void
    {
        $produtos = $this->produto->findAllWithCategory();
        $this->view('produtos/index', ['produtos' => $produtos]);
    }

    public function create(): void
    {
        $categorias = $this->categoria->findAtivas();
        $produtosComposicao = $this->produto->findAllForComposition();
        $composicao = [];
        $this->view('produtos/form', compact('categorias', 'produtosComposicao', 'composicao') + ['produto' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();

        $data = $this->only([
            'categoria_id', 'nome', 'sku', 'codigo_barras', 'unidade',
            'preco_custo', 'preco_venda', 'percent_lucro',
            'mao_obra_valor', 'custo_energia_valor', 'custo_agua_valor',
            'custo_aluguel_valor', 'custo_gas_valor',
            'taxa_maquininha_percent', 'taxa_governo_percent',
            'estoque_atual', 'estoque_minimo', 'controla_estoque', 'ativo'
        ]);

        // Calcular preço de venda a partir do % lucro se informado
        $precoCusto = $this->decimal($data['preco_custo'] ?? '0');
        $percentLucro = $this->decimal($data['percent_lucro'] ?? '0');
        $maoObraValor = $this->decimal($data['mao_obra_valor'] ?? '0');
        $custoEnergiaValor = $this->decimal($data['custo_energia_valor'] ?? '0');
        $custoAguaValor = $this->decimal($data['custo_agua_valor'] ?? '0');
        $custoAluguelValor = $this->decimal($data['custo_aluguel_valor'] ?? '0');
        $custoGasValor = $this->decimal($data['custo_gas_valor'] ?? '0');
        $custosFixosValor = $custoEnergiaValor + $custoAguaValor + $custoAluguelValor + $custoGasValor;
        $taxaMaquininhaPercent = $this->decimal($data['taxa_maquininha_percent'] ?? '0');
        $taxaGovernoPercent = $this->decimal($data['taxa_governo_percent'] ?? '0');
        $custoComposicao = $this->custoComposicaoPost($_POST['componente_produto_id'] ?? [], $_POST['quantidade_componente'] ?? []);
        $percentTotal = $percentLucro + $taxaMaquininhaPercent + $taxaGovernoPercent;

        if ($percentTotal >= 100) {
            $this->flash('error', 'A soma de lucro e taxas deve ser menor que 100%.');
            $this->redirect('/produtos/criar');
            return;
        }

        $deveCalcularPreco = ($percentTotal > 0 || $maoObraValor > 0 || $custosFixosValor > 0 || $custoComposicao > 0)
            && ($precoCusto + $custoComposicao + $maoObraValor + $custosFixosValor) > 0;

        if ($deveCalcularPreco) {
            $data['preco_venda'] = percentToPrice(
                $precoCusto + $custoComposicao,
                $percentLucro,
                $maoObraValor,
                $taxaMaquininhaPercent,
                $taxaGovernoPercent,
                $custosFixosValor
            );
        }

        $data['preco_custo']              = $precoCusto;
        $data['preco_venda']              = $this->decimal($data['preco_venda'] ?? '0');
        $data['estoque_atual']            = $this->decimal($data['estoque_atual'] ?? '0');
        $data['estoque_minimo']           = $this->decimal($data['estoque_minimo'] ?? '0');
        $data['percent_lucro']            = $percentLucro;
        $data['mao_obra_valor']           = $maoObraValor;
        $data['custo_energia_valor']      = $custoEnergiaValor;
        $data['custo_agua_valor']         = $custoAguaValor;
        $data['custo_aluguel_valor']      = $custoAluguelValor;
        $data['custo_gas_valor']          = $custoGasValor;
        $data['taxa_maquininha_percent']  = $taxaMaquininhaPercent;
        $data['taxa_governo_percent']     = $taxaGovernoPercent;
        $data['controla_estoque'] = isset($_POST['controla_estoque']) ? 1 : 0;
        $data['requer_preparo']   = isset($_POST['requer_preparo']) ? 1 : 0;
        $data['ativo']            = isset($_POST['ativo']) ? 1 : 0;
        $data['created_at']       = now();
        $data['updated_at']       = now();

        // Validar
        if (empty($data['nome'])) {
            $this->flash('error', 'Nome do produto é obrigatório.');
            $this->redirect('/produtos/criar');
            return;
        }

        if ($data['preco_venda'] <= 0) {
            $this->flash('error', 'Preço de venda deve ser maior que zero.');
            $this->redirect('/produtos/criar');
            return;
        }

        // Handle image upload
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imgData = $this->processarImagem($_FILES['imagem']);
            if ($imgData) {
                $data['imagem_blob'] = $imgData['blob'];
                $data['imagem_nome'] = $imgData['nome'];
                $data['imagem_tipo'] = $imgData['tipo'];
            }
        } else {
            $data['imagem_blob'] = null;
            $data['imagem_nome'] = null;
            $data['imagem_tipo'] = null;
        }

        // Insere o produto localmente
        $produtoId = $this->produto->insert($data);
        $this->produto->syncComposicao(
            $produtoId,
            $_POST['componente_produto_id'] ?? [],
            $_POST['quantidade_componente'] ?? []
        );

        $this->flash('success', 'Produto cadastrado com sucesso!');
        $this->redirect('/produtos');
    }

    public function edit(string $id): void
    {
        $produto = $this->produto->findByIdWithCategory((int)$id);
        if (!$produto) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/produtos');
            return;
        }

        $produto['percent_lucro'] = (float)($produto['percent_lucro'] ?? 0);

        $categorias = $this->categoria->findAtivas();
        $produtosComposicao = $this->produto->findAllForComposition((int)$id);
        $composicao = $this->produto->findComposicao((int)$id);
        $this->view('produtos/form', compact('produto', 'categorias', 'produtosComposicao', 'composicao'));
    }

    public function update(string $id): void
    {
        $this->validateCsrf();

        $produto = $this->produto->findById((int)$id);
        if (!$produto) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/produtos');
            return;
        }

        $data = $this->only([
            'categoria_id', 'nome', 'sku', 'codigo_barras', 'unidade',
            'preco_custo', 'preco_venda', 'percent_lucro',
            'mao_obra_valor', 'custo_energia_valor', 'custo_agua_valor',
            'custo_aluguel_valor', 'custo_gas_valor',
            'taxa_maquininha_percent', 'taxa_governo_percent',
            'estoque_atual', 'estoque_minimo', 'controla_estoque', 'ativo'
        ]);

        $precoCusto = $this->decimal($data['preco_custo'] ?? '0');
        $percentLucro = $this->decimal($data['percent_lucro'] ?? '0');
        $maoObraValor = $this->decimal($data['mao_obra_valor'] ?? '0');
        $custoEnergiaValor = $this->decimal($data['custo_energia_valor'] ?? '0');
        $custoAguaValor = $this->decimal($data['custo_agua_valor'] ?? '0');
        $custoAluguelValor = $this->decimal($data['custo_aluguel_valor'] ?? '0');
        $custoGasValor = $this->decimal($data['custo_gas_valor'] ?? '0');
        $custosFixosValor = $custoEnergiaValor + $custoAguaValor + $custoAluguelValor + $custoGasValor;
        $taxaMaquininhaPercent = $this->decimal($data['taxa_maquininha_percent'] ?? '0');
        $taxaGovernoPercent = $this->decimal($data['taxa_governo_percent'] ?? '0');
        $custoComposicao = $this->custoComposicaoPost($_POST['componente_produto_id'] ?? [], $_POST['quantidade_componente'] ?? [], (int)$id);
        $percentTotal = $percentLucro + $taxaMaquininhaPercent + $taxaGovernoPercent;

        if ($percentTotal >= 100) {
            $this->flash('error', 'A soma de lucro e taxas deve ser menor que 100%.');
            $this->redirect("/produtos/{$id}/editar");
            return;
        }

        $deveCalcularPreco = ($percentTotal > 0 || $maoObraValor > 0 || $custosFixosValor > 0 || $custoComposicao > 0)
            && ($precoCusto + $custoComposicao + $maoObraValor + $custosFixosValor) > 0;

        if ($deveCalcularPreco) {
            $data['preco_venda'] = percentToPrice(
                $precoCusto + $custoComposicao,
                $percentLucro,
                $maoObraValor,
                $taxaMaquininhaPercent,
                $taxaGovernoPercent,
                $custosFixosValor
            );
        }

        $data['preco_custo']              = $precoCusto;
        $data['preco_venda']              = $this->decimal($data['preco_venda'] ?? '0');
        $data['estoque_atual']            = $this->decimal($data['estoque_atual'] ?? '0');
        $data['estoque_minimo']           = $this->decimal($data['estoque_minimo'] ?? '0');
        $data['percent_lucro']            = $percentLucro;
        $data['mao_obra_valor']           = $maoObraValor;
        $data['custo_energia_valor']      = $custoEnergiaValor;
        $data['custo_agua_valor']         = $custoAguaValor;
        $data['custo_aluguel_valor']      = $custoAluguelValor;
        $data['custo_gas_valor']          = $custoGasValor;
        $data['taxa_maquininha_percent']  = $taxaMaquininhaPercent;
        $data['taxa_governo_percent']     = $taxaGovernoPercent;
        $data['controla_estoque'] = isset($_POST['controla_estoque']) ? 1 : 0;
        $data['requer_preparo']   = isset($_POST['requer_preparo']) ? 1 : 0;
        $data['ativo']            = isset($_POST['ativo']) ? 1 : 0;
        $data['updated_at']       = now();

        // Handle image update
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imgData = $this->processarImagem($_FILES['imagem']);
            if ($imgData) {
                $data['imagem_blob'] = $imgData['blob'];
                $data['imagem_nome'] = $imgData['nome'];
                $data['imagem_tipo'] = $imgData['tipo'];
            }
        }

        $produtoId = (int)$id;
        $this->produto->update($produtoId, $data);
        $this->produto->syncComposicao(
            $produtoId,
            $_POST['componente_produto_id'] ?? [],
            $_POST['quantidade_componente'] ?? []
        );
        $this->flash('success', 'Produto atualizado com sucesso!');
        $this->redirect('/produtos');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();

        $produtoId = (int)$id;
        $produto = $this->produto->findById($produtoId);
        if (!$produto) {
            $this->flash('error', 'Produto nao encontrado.');
            $this->redirect('/produtos');
            return;
        }

        try {
            $this->produto->delete($produtoId);
            $this->flash('success', 'Produto excluido.');
        } catch (\PDOException $e) {
            // 23000 = SQLSTATE integrity constraint violation
            // 1451 = MySQL/MariaDB FK constraint fails
            $code = (string)$e->getCode();
            $msg  = $e->getMessage();
            $isFkError = $code === '23000'
                || str_contains($msg, '1451')
                || str_contains($msg, 'foreign key constraint');

            if (!$isFkError) {
                throw $e;
            }

            $this->flash('error', 'Nao foi possivel excluir. Rode a migration de exclusao real de produtos no banco.');
        }

        $this->redirect('/produtos');
    }

    /**
     * Serve product image stored in database blob
     */
    public function imagem(string $id): void
    {
        $img = $this->produto->getImagem((int)$id);
        if (!$img || empty($img['imagem_blob'])) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . $img['imagem_tipo']);
        header('Cache-Control: public, max-age=86400');
        echo $img['imagem_blob'];
        exit;
    }

    private function processarImagem(array $file): ?array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $maxSize      = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file['type'], $allowedTypes)) return null;
        if ($file['size'] > $maxSize) return null;

        return [
            'blob' => file_get_contents($file['tmp_name']),
            'nome' => $file['name'],
            'tipo' => $file['type'],
        ];
    }

    private function decimal(mixed $value): float
    {
        return (float)str_replace(',', '.', (string)($value ?? '0'));
    }

    private function custoComposicaoPost(array $componentes, array $quantidades, ?int $produtoId = null): float
    {
        $total = 0.0;

        foreach ($componentes as $index => $componenteId) {
            $componenteId = (int)$componenteId;
            $quantidade = $this->decimal($quantidades[$index] ?? '0');

            if ($componenteId <= 0 || $quantidade <= 0 || $componenteId === $produtoId) {
                continue;
            }

            $componente = $this->produto->findById($componenteId);
            if ($componente) {
                $total += (float)$componente['preco_custo'] * $quantidade;
            }
        }

        return $total;
    }
}
