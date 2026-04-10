<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    private Fornecedor $fornecedor;

    public function __construct()
    {
        $this->fornecedor = new Fornecedor();
    }

    public function index(): void
    {
        $fornecedores = $this->fornecedor->findAll('razao_social');
        $this->view('fornecedores/index', ['fornecedores' => $fornecedores]);
    }

    public function create(): void
    {
        $this->view('fornecedores/form', ['fornecedor' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $data = $this->only(['razao_social', 'nome_fantasia', 'telefone', 'email', 'cnpj', 'observacoes']);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $this->fornecedor->insert($data);
        $this->flash('success', 'Fornecedor cadastrado!');
        $this->redirect('/fornecedores');
    }

    public function edit(string $id): void
    {
        $fornecedor = $this->fornecedor->findById((int)$id);
        if (!$fornecedor) {
            $this->flash('error', 'Fornecedor não encontrado.');
            $this->redirect('/fornecedores');
            return;
        }
        $this->view('fornecedores/form', ['fornecedor' => $fornecedor]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $data = $this->only(['razao_social', 'nome_fantasia', 'telefone', 'email', 'cnpj', 'observacoes']);
        $data['updated_at'] = now();
        $this->fornecedor->update((int)$id, $data);
        $this->flash('success', 'Fornecedor atualizado!');
        $this->redirect('/fornecedores');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->fornecedor->delete((int)$id);
        $this->flash('success', 'Fornecedor removido.');
        $this->redirect('/fornecedores');
    }
}
