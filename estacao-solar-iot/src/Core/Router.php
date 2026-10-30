<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ROTEADOR (front controller)
 * Liga "MÉTODO + CAMINHO" a um método de Controller.
 *   $router->get('/estacoes/{id}', [EstacaoController::class, 'mostrar'], auth: true);
 * {id} só aceita números. Rotas com auth:true exigem login.
 */
final class Router
{
    private array $rotas = [];

    public function add(string $metodo, string $caminho, array $handler, bool $auth = false): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $caminho) . '$#';
        $this->rotas[] = compact('metodo', 'regex', 'handler', 'auth');
    }

    public function get(string $c, array $h, bool $auth = false): void    { $this->add('GET', $c, $h, $auth); }
    public function post(string $c, array $h, bool $auth = false): void   { $this->add('POST', $c, $h, $auth); }
    public function put(string $c, array $h, bool $auth = false): void    { $this->add('PUT', $c, $h, $auth); }
    public function delete(string $c, array $h, bool $auth = false): void { $this->add('DELETE', $c, $h, $auth); }

    public function dispatch(Request $req): Response
    {
        try {
            $caminhoExiste = false;
            foreach ($this->rotas as $r) {
                if (!preg_match($r['regex'], $req->path, $m)) {
                    continue;
                }
                $caminhoExiste = true;
                if ($r['metodo'] !== $req->method) {
                    continue;
                }
                $params = array_map('intval', array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY));

                // --- Segurança antes de chamar o controller ---
                if ($r['auth'] && !Auth::check()) {
                    if ($req->isApi()) {
                        throw new HttpException(401, 'Faça login para acessar este recurso.');
                    }
                    return Response::redirect('/login');
                }
                $escrita = in_array($req->method, ['POST', 'PUT', 'DELETE'], true);
                if ($escrita && !$req->isApi() && !Auth::csrfValido($req->input('_csrf'))) {
                    throw new HttpException(419, 'Sessão expirada ou token CSRF inválido. Volte e tente de novo.');
                }
                // API autenticada por sessão: só aceita escrita em JSON (formulários de outros sites não conseguem)
                if ($escrita && $req->isApi() && $r['auth']
                    && !str_contains($req->header('content-type') ?? '', 'application/json')
                    && $req->method !== 'DELETE') {
                    throw new HttpException(415, 'Envie o corpo como application/json.');
                }

                [$classe, $metodo] = $r['handler'];
                return (new $classe())->$metodo($req, $params);
            }
            if ($caminhoExiste) {
                throw new HttpException(405, 'Método não permitido para esta rota.');
            }
            throw new NotFoundException('Página ou recurso não encontrado.');
        } catch (ValidationException $e) {
            return Response::json(['erro' => $e->getMessage(), 'campos' => $e->errors], 422);
        } catch (NotFoundException $e) {
            return $this->erro($req, 404, $e->getMessage());
        } catch (HttpException $e) {
            return $this->erro($req, $e->status, $e->getMessage());
        } catch (\Throwable $e) {
            $msg = App::config('debug') ? $e->getMessage() : 'Erro interno do servidor.';
            return $this->erro($req, 500, $msg);
        }
    }

    private function erro(Request $req, int $status, string $mensagem): Response
    {
        if ($req->isApi()) {
            return Response::json(['erro' => $mensagem], $status);
        }
        return Response::html(View::render('errors/erro', ['status' => $status, 'mensagem' => $mensagem]), $status);
    }
}
