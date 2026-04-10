<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    private Usuario $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function index(): void
    {
        $usuarios = $this->usuario->findAll('nome');
        $this->view('usuarios/index', ['usuarios' => $usuarios]);
    }

    public function create(): void
    {
        $this->view('usuarios/form', ['usuario' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $data = $this->only(['nome', 'email', 'senha', 'perfil', 'ativo']);

        if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
            $this->flash('error', 'Preencha todos os campos obrigatórios.');
            $this->redirect('/usuarios/criar');
            return;
        }

        $data['ativo'] = isset($_POST['ativo']) ? 1 : 0;
        $this->usuario->createUser($data);
        $this->flash('success', 'Usuário criado!');
        $this->redirect('/usuarios');
    }

    public function edit(string $id): void
    {
        $usuario = $this->usuario->findById((int)$id);
        if (!$usuario) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/usuarios');
            return;
        }
        $this->view('usuarios/form', ['usuario' => $usuario]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $data = $this->only(['nome', 'email', 'senha', 'perfil', 'ativo']);
        $data['ativo'] = isset($_POST['ativo']) ? 1 : 0;
        $this->usuario->updateUser((int)$id, $data);
        $this->flash('success', 'Usuário atualizado!');
        $this->redirect('/usuarios');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $user = auth();
        if ((int)$id === $user['id']) {
            $this->flash('error', 'Você não pode remover a si mesmo.');
            $this->redirect('/usuarios');
            return;
        }
        $this->usuario->delete((int)$id);
        $this->flash('success', 'Usuário removido.');
        $this->redirect('/usuarios');
    }
}
