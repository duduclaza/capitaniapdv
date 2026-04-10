<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    private Categoria $categoria;

    public function __construct()
    {
        $this->categoria = new Categoria();
    }

    public function index(): void
    {
        $categorias = $this->categoria->findAll('nome');
        $this->view('categorias/index', ['categorias' => $categorias]);
    }

    public function create(): void
    {
        $this->view('categorias/form', ['categoria' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $nome = trim($this->input('nome', ''));

        if (empty($nome)) {
            $this->flash('error', 'Nome é obrigatório.');
            $this->redirect('/categorias/criar');
            return;
        }

        $this->categoria->insert([
            'nome' => $nome,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ]);

        $this->flash('success', 'Categoria criada com sucesso!');
        $this->redirect('/categorias');
    }

    public function edit(string $id): void
    {
        $categoria = $this->categoria->findById((int)$id);
        if (!$categoria) {
            $this->flash('error', 'Categoria não encontrada.');
            $this->redirect('/categorias');
            return;
        }
        $this->view('categorias/form', ['categoria' => $categoria]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $this->categoria->update((int)$id, [
            'nome' => trim($this->input('nome', '')),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ]);
        $this->flash('success', 'Categoria atualizada!');
        $this->redirect('/categorias');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->categoria->delete((int)$id);
        $this->flash('success', 'Categoria removida.');
        $this->redirect('/categorias');
    }
}
