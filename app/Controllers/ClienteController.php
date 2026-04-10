<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;

class ClienteController extends Controller
{
    private Cliente $cliente;

    public function __construct()
    {
        $this->cliente = new Cliente();
    }

    public function index(): void
    {
        $clientes = $this->cliente->findAll('nome');
        $this->view('clientes/index', ['clientes' => $clientes]);
    }

    public function create(): void
    {
        $this->view('clientes/form', ['cliente' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $data = $this->only(['nome', 'telefone', 'documento', 'observacoes']);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        if (empty($data['nome'])) {
            $this->flash('error', 'Nome é obrigatório.');
            $this->redirect('/clientes/criar');
            return;
        }

        $this->cliente->insert($data);
        $this->flash('success', 'Cliente cadastrado!');
        $this->redirect('/clientes');
    }

    public function edit(string $id): void
    {
        $cliente = $this->cliente->findById((int)$id);
        if (!$cliente) {
            $this->flash('error', 'Cliente não encontrado.');
            $this->redirect('/clientes');
            return;
        }
        $this->view('clientes/form', ['cliente' => $cliente]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $data = $this->only(['nome', 'telefone', 'documento', 'observacoes']);
        $data['updated_at'] = now();
        $this->cliente->update((int)$id, $data);
        $this->flash('success', 'Cliente atualizado!');
        $this->redirect('/clientes');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->cliente->delete((int)$id);
        $this->flash('success', 'Cliente removido.');
        $this->redirect('/clientes');
    }
}
