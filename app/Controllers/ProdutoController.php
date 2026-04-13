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

        // Insere o produto localmente
        $this->produto->insert($data);

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

        $produto = $this->produto->findById((int)$id);
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

        $this->produto->update((int)$id, $data);
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

        if ($this->produto->possuiVinculos($produtoId)) {
            $this->produto->inativar($produtoId);
            $this->flash('success', 'Produto possui historico e foi inativado.');
            $this->redirect('/produtos');
            return;
        }

        try {
            $this->produto->delete($produtoId);
            $this->flash('success', 'Produto removido.');
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

            $this->produto->inativar($produtoId);
            $this->flash('success', 'Produto possui historico e foi inativado.');
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
}
