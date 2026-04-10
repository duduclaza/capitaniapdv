<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\Cliente;

class MesaController extends Controller
{
    private Mesa $mesa;
    private Comanda $comanda;

    public function __construct()
    {
        $this->mesa = new Mesa();
        $this->comanda = new Comanda();
    }

    public function index(): void
    {
        $mesas = $this->mesa->findAllWithStatus();
        $this->view('mesas/index', ['mesas' => $mesas]);
    }

    public function create(): void
    {
        $this->view('mesas/form', ['mesa' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $this->mesa->insert([
            'numero'    => (int)$this->input('numero'),
            'descricao' => $this->input('descricao', ''),
            'status'    => 'livre',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->flash('success', 'Mesa cadastrada!');
        $this->redirect('/mesas');
    }

    public function edit(string $id): void
    {
        $mesa = $this->mesa->findById((int)$id);
        if (!$mesa) {
            $this->flash('error', 'Mesa não encontrada.');
            $this->redirect('/mesas');
            return;
        }
        $this->view('mesas/form', ['mesa' => $mesa]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $this->mesa->update((int)$id, [
            'numero'     => (int)$this->input('numero'),
            'descricao'  => $this->input('descricao', ''),
            'updated_at' => now(),
        ]);
        $this->flash('success', 'Mesa atualizada!');
        $this->redirect('/mesas');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->mesa->delete((int)$id);
        $this->flash('success', 'Mesa removida.');
        $this->redirect('/mesas');
    }
}
