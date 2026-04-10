<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Categoria;
use App\Services\StripeService;

class ProdutoController extends Controller
{
    private Produto $produto;
    private Categoria $categoria;
    private StripeService $stripe;

    public function __construct()
    {
        $this->produto   = new Produto();
        $this->categoria = new Categoria();
        $this->stripe    = new StripeService();
    }

    public function index(): void
    {
        $produtos = $this->produto->findAllWithCategory();
        $this->view('produtos/index', ['produtos' => $produtos]);
    }

    public function create(): void
    {
        $categorias = $this->categoria->findAtivas();
        $this->view('produtos/form', ['produto' => null, 'categorias' => $categorias]);
    }

    public function store(): void
    {
        $this->validateCsrf();

        $data = $this->only([
            'categoria_id', 'nome', 'sku', 'codigo_barras', 'unidade',
            'preco_custo', 'preco_venda', 'percent_lucro',
            'estoque_atual', 'estoque_minimo', 'controla_estoque', 'ativo'
        ]);

        // Calcular preço de venda a partir do % lucro se informado
        if (!empty($data['percent_lucro']) && !empty($data['preco_custo'])) {
            $data['preco_venda'] = percentToPrice((float)$data['preco_custo'], (float)$data['percent_lucro']);
        }

        $data['preco_custo']      = (float)str_replace(',', '.', $data['preco_custo'] ?? '0');
        $data['preco_venda']      = (float)str_replace(',', '.', $data['preco_venda'] ?? '0');
        $data['estoque_atual']    = (float)str_replace(',', '.', $data['estoque_atual'] ?? '0');
        $data['estoque_minimo']   = (float)str_replace(',', '.', $data['estoque_minimo'] ?? '0');
        $data['percent_lucro']    = (float)str_replace(',', '.', $data['percent_lucro'] ?? '0');
        $data['controla_estoque'] = isset($_POST['controla_estoque']) ? 1 : 0;
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

        // Insere o produto localmente primeiro para obter o ID
        $produtoId = $this->produto->insert($data);

        // -----------------------------------------------------------------------
        // Sincroniza com Stripe — cria Product + Price
        // -----------------------------------------------------------------------
        $data['id'] = $produtoId;
        $stripeData = $this->stripe->criarProduto($data);

        // Salva os IDs do Stripe no produto local
        if ($stripeData['stripe_product_id']) {
            $this->produto->update((int)$produtoId, [
                'stripe_product_id' => $stripeData['stripe_product_id'],
                'stripe_price_id'   => $stripeData['stripe_price_id'],
                'updated_at'        => now(),
            ]);
        }
        // -----------------------------------------------------------------------

        $msg = 'Produto cadastrado com sucesso!';
        if ($stripeData['stripe_product_id']) {
            $msg .= ' ✓ Sincronizado com Stripe (ID: ' . $stripeData['stripe_product_id'] . ')';
        } else {
            $msg .= ' ⚠ Não foi possível sincronizar com o Stripe agora.';
        }

        $this->flash('success', $msg);
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

        // Calcula % de lucro atual
        if ($produto['preco_custo'] > 0 && $produto['preco_venda'] > 0) {
            $produto['percent_lucro'] = round(
                (($produto['preco_venda'] - $produto['preco_custo']) / $produto['preco_venda']) * 100,
                2
            );
        } else {
            $produto['percent_lucro'] = 0;
        }

        $categorias = $this->categoria->findAtivas();
        $this->view('produtos/form', ['produto' => $produto, 'categorias' => $categorias]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();

        $produto = $this->produto->find((int)$id);
        if (!$produto) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/produtos');
            return;
        }

        $data = $this->only([
            'categoria_id', 'nome', 'sku', 'codigo_barras', 'unidade',
            'preco_custo', 'preco_venda', 'percent_lucro',
            'estoque_atual', 'estoque_minimo', 'controla_estoque', 'ativo'
        ]);

        if (!empty($data['percent_lucro']) && !empty($data['preco_custo'])) {
            $data['preco_venda'] = percentToPrice((float)$data['preco_custo'], (float)$data['percent_lucro']);
        }

        $data['preco_custo']      = (float)str_replace(',', '.', $data['preco_custo'] ?? '0');
        $data['preco_venda']      = (float)str_replace(',', '.', $data['preco_venda'] ?? '0');
        $data['estoque_atual']    = (float)str_replace(',', '.', $data['estoque_atual'] ?? '0');
        $data['estoque_minimo']   = (float)str_replace(',', '.', $data['estoque_minimo'] ?? '0');
        $data['percent_lucro']    = (float)str_replace(',', '.', $data['percent_lucro'] ?? '0');
        $data['controla_estoque'] = isset($_POST['controla_estoque']) ? 1 : 0;
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

        // -----------------------------------------------------------------------
        // Sincroniza com Stripe — atualiza Product + Price se mudou
        // -----------------------------------------------------------------------
        $data['id'] = $id;
        $stripeData = $this->stripe->atualizarProduto(
            $data,
            $produto['stripe_product_id'] ?? null,
            $produto['stripe_price_id']   ?? null
        );

        $data['stripe_product_id'] = $stripeData['stripe_product_id'];
        $data['stripe_price_id']   = $stripeData['stripe_price_id'];
        // -----------------------------------------------------------------------

        $this->produto->update((int)$id, $data);
        $this->flash('success', 'Produto atualizado com sucesso!' .
            ($stripeData['stripe_product_id'] ? ' ✓ Stripe sincronizado.' : ''));
        $this->redirect('/produtos');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();

        $produto = $this->produto->find((int)$id);

        // Arquiva no Stripe antes de excluir localmente
        if ($produto && !empty($produto['stripe_product_id'])) {
            $this->stripe->arquivarProduto($produto['stripe_product_id']);
        }

        $this->produto->delete((int)$id);
        $this->flash('success', 'Produto removido.' .
            ($produto['stripe_product_id'] ? ' ✓ Arquivado no Stripe.' : ''));
        $this->redirect('/produtos');
    }

    /**
     * Ajax: retorna ID Stripe de um produto (para debug/verificação)
     */
    public function stripeInfo(string $id): void
    {
        $produto = $this->produto->find((int)$id);
        $this->json([
            'produto_id'        => $id,
            'stripe_product_id' => $produto['stripe_product_id'] ?? null,
            'stripe_price_id'   => $produto['stripe_price_id']   ?? null,
        ]);
    }

    /**
     * Sincroniza em lote todos os produtos sem stripe_product_id
     */
    public function sincronizarTodos(): void
    {
        $this->validateCsrf();

        $produtos = $this->produto->findSemStripe();
        $count    = 0;

        foreach ($produtos as $p) {
            $stripeData = $this->stripe->criarProduto($p);
            if ($stripeData['stripe_product_id']) {
                $this->produto->update((int)$p['id'], [
                    'stripe_product_id' => $stripeData['stripe_product_id'],
                    'stripe_price_id'   => $stripeData['stripe_price_id'],
                    'updated_at'        => now(),
                ]);
                $count++;
            }
        }

        $this->flash('success', "{$count} produto(s) sincronizados com o Stripe!");
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
}
