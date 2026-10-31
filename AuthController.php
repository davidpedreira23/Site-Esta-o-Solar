<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\HttpException;
use App\Core\Response;

/** Login e logout do painel administrativo. */
final class AuthController extends Controller
{
    public function formulario(Request $req, array $p): Response
    {
        if (Auth::check()) {
            return $this->redirecionar('/estacoes');
        }
        return $this->view('auth/login', ['titulo' => 'Entrar', 'erro' => null]);
    }

    public function entrar(Request $req, array $p): Response
    {
        $email = trim((string) $req->input('email', ''));
        if (Auth::attempt($email, (string) $req->input('senha', ''))) {
            return $this->redirecionar('/estacoes', 'Bem-vindo(a), ' . Auth::user()['nome'] . '!');
        }
        return $this->view('auth/login', ['titulo' => 'Entrar', 'erro' => 'E-mail ou senha incorretos.', 'email' => $email], 401);
    }

    public function sair(Request $req, array $p): Response
    {
        Auth::logout();
        Auth::start(); // reabre sessão limpa para poder exibir o aviso
        return $this->redirecionar('/', 'Você saiu do painel.');
    }

    /** POST /api/login (JSON {"email","senha"}) — útil para testar a API com curl/Postman. */
    public function entrarApi(Request $req, array $p): Response
    {
        if (!Auth::attempt(trim((string) $req->input('email', '')), (string) $req->input('senha', ''))) {
            throw new HttpException(401, 'E-mail ou senha incorretos.');
        }
        return Response::json(['mensagem' => 'Login realizado.', 'usuario' => Auth::user()]);
    }
}
