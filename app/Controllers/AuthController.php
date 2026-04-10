<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller
{
    private Usuario $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', [], null);
    }

    public function login(): void
    {
        $this->validateCsrf();

        $email = trim($this->input('email', ''));
        $senha = $this->input('senha', '');

        if (empty($email) || empty($senha)) {
            $this->flash('error', 'Preencha e-mail e senha.');
            $this->redirect('/login');
            return;
        }

        $user = $this->usuario->authenticate($email, $senha);

        if (!$user) {
            $this->flash('error', 'Credenciais inválidas ou usuário inativo.');
            $this->redirect('/login');
            return;
        }

        // Store in session (without password)
        unset($user['senha_hash']);
        $_SESSION['user'] = $user;

        $this->flash('success', 'Bem-vindo, ' . $user['nome'] . '!');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}
